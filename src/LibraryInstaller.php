<?php declare(strict_types = 1);

namespace Librette\ComposerVendor;

use Composer\Installer\LibraryInstaller as BaseLibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;


class LibraryInstaller extends BaseLibraryInstaller
{

	private $mapping = [];


	public function addMapping(array $mapping)
	{
		$this->mapping = array_merge($this->mapping, $mapping);
	}


	public function getInstallPath(PackageInterface $package)
	{
		$path = $this->getOverwrittenPath($package->getName());
		if ($path !== NULL) {
			return $path;
		}
		return parent::getInstallPath($package);
	}


	protected function installCode(PackageInterface $package)
	{
		if ($this->getOverwrittenPath($package->getName()) !== NULL) {
			return;
		}
		parent::installCode($package);
	}


	protected function updateCode(PackageInterface $initial, PackageInterface $package)
	{
		if ($this->getOverwrittenPath($package->getName()) !== NULL) {
			return;
		}
		return parent::updateCode($initial, $package);
	}


	protected function removeCode(PackageInterface $package)
	{
		if ($this->getOverwrittenPath($package->getName()) !== NULL) {
			return;
		}
		parent::removeCode($package);
	}


	private function getOverwrittenPath($packageName)
	{
		if (isset($this->mapping[$packageName])) {
			$path = $this->mapping[$packageName];
			if (is_dir($path)) {
				return $path;
			}
			$this->io->write("$path not exist for package $packageName, skipping", TRUE, IOInterface::DEBUG);
		}

		list($vendor, $name) = explode('/', $packageName);
		if (isset($this->mapping[$vendor])) {
			$path = $this->mapping[$vendor] . '/' . $name;
			if (is_dir($path)) {
				return $path;
			}
			$this->io->write("$path not exist for package $packageName, skipping", TRUE, IOInterface::DEBUG);
		}
		if (isset($this->mapping['*']) && is_dir($this->mapping['*'] . '/' . $packageName)) {
			$path = $this->mapping['*'] . '/' . $packageName;
			if (is_dir($path)) {
				return $path;
			}
			$this->io->write("$path not exist for package $packageName, skipping", TRUE, IOInterface::DEBUG);
		}
		return NULL;
	}

}
