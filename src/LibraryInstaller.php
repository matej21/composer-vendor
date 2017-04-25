<?php declare(strict_types = 1);

namespace Librette\ComposerVendor;

use Composer\Installer\LibraryInstaller as BaseLibraryInstaller;
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
		if (isset($this->mapping[$package->getName()])) {
			return $this->mapping[$package->getName()];
		}
		return parent::getInstallPath($package);
	}


	protected function installCode(PackageInterface $package)
	{
		if (isset($this->mapping[$package->getName()])) {
			return;
		}
		parent::installCode($package);
	}


	protected function updateCode(PackageInterface $initial, PackageInterface $target)
	{
		if (isset($this->mapping[$target->getName()])) {
			return;
		}
		return parent::updateCode($initial, $target);
	}


	protected function removeCode(PackageInterface $package)
	{
		if (isset($this->mapping[$package->getName()])) {
			return;
		}
		parent::removeCode($package);
	}
}
