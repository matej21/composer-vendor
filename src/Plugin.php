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
		$vendorMappingFile = $this->composer->getConfig()->get('home') . '/composer.vendor.json';
		$file = new JsonFile($vendorMappingFile, NULL, $this->io);

		$mapping = [];
		if ($file->exists()) {
			$jsonParser = new JsonParser();
			$mapping = $jsonParser->parse(file_get_contents($vendorMappingFile), JsonParser::DETECT_KEY_CONFLICTS);
		}
		if (!$mapping) {
			return;
		}

		$installationManager = $this->composer->getInstallationManager();
		$installer = $installationManager->getInstaller('library');
		$installationManager->removeInstaller($installer);
		$newInstaller = new LibraryInstaller($this->io, $this->composer, NULL);
		$newInstaller->addMapping($mapping);
		$installationManager->addInstaller($newInstaller);
	}


	public function activate(Composer $composer, IOInterface $io)
	{
		$this->composer = $composer;
		$this->io = $io;
	}

}
