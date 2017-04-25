<?php

namespace Librette\ComposerVendor;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Seld\JsonLint\JsonParser;


class Plugin implements PluginInterface, EventSubscriberInterface
{

	/** @var Composer */
	private $composer;

	/** @var IOInterface */
	private $io;


	public static function getSubscribedEvents()
	{
		return [
			PluginEvents::INIT => ['init', 1],
		];
	}


	public function init(Event $event)
	{
		$files = [$this->composer->getConfig()->get('home') . '/composer.vendor.json', 'composer.vendor.json'];
		$mapping = [];
		foreach ($files as $mappingFile) {
			$file = new JsonFile($mappingFile, NULL, $this->io);

			if ($file->exists()) {
				$jsonParser = new JsonParser();
				$mapping = array_merge($mapping, (array) $jsonParser->parse(file_get_contents($mappingFile), JsonParser::DETECT_KEY_CONFLICTS));
			}

		}
		if (count($mapping) === 0) {
			return;
		}
		$resultMapping = [];
		$rootPackageName = $this->composer->getPackage()->getName();
		list($vendor,) = explode('/', $rootPackageName);
		foreach ($mapping as $rule => $path) {
			if (strpos($rule, ':') === FALSE) {
				$resultMapping[$rule] = $path;
				continue;
			}
			list($requiredPackage, $dependency) = explode(':', $rule);
			if ($requiredPackage === $rootPackageName || $requiredPackage === $vendor) {
				$resultMapping[$dependency] = $path;
			}
		}

		$installationManager = $this->composer->getInstallationManager();
		$installer = $installationManager->getInstaller('library');
		$installationManager->removeInstaller($installer);
		$newInstaller = new LibraryInstaller($this->io, $this->composer, NULL);
		$newInstaller->addMapping($resultMapping);
		$installationManager->addInstaller($newInstaller);
	}


	public function activate(Composer $composer, IOInterface $io)
	{
		$this->composer = $composer;
		$this->io = $io;
	}

}
