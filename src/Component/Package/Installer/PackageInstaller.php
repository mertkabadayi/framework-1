<?php

namespace Pagekit\Component\Package\Installer;

use Pagekit\Component\Filesystem\File;
use Pagekit\Component\Package\Exception\LogicException;
use Pagekit\Component\Package\Loader\JsonLoader;
use Pagekit\Component\Package\Loader\LoaderInterface;
use Pagekit\Component\Package\PackageInterface;
use Pagekit\Component\Package\Repository\InstalledRepositoryInterface;

/**
 * Package installation manager.
 */
class PackageInstaller implements InstallerInterface
{
    /**
     * @var InstalledRepositoryInterface
     */
    protected $repository;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var File
     */
    protected $file;

    /**
     * Initializes the installer.
     *
     * @param InstalledRepositoryInterface $repository
     * @param LoaderInterface              $loader
     * @param File                         $file
     */
    public function __construct(InstalledRepositoryInterface $repository, LoaderInterface $loader = null, File $file = null)
    {
        $this->repository = $repository;
        $this->loader     = $loader ?: new JsonLoader;
        $this->file       = $file ?: new File;
    }

    /**
     * {@inheritdoc}
     */
    public function install($packageFile)
    {
        $package = $this->loader->load($packageFile);

        if ($this->repository->hasPackage($package)) {
            throw new LogicException('Package is already installed: ' . $package);
        }

        $this->file->copyDir(dirname($packageFile), $this->repository->getInstallPath($package));
        $this->repository->addPackage(clone $package);
    }

    /**
     * {@inheritdoc}
     */
    public function update($packageFile)
    {
        $update = $this->loader->load($packageFile);

        if (!$initial = $this->repository->findPackage($update->getName())) {
            throw new LogicException('Package is not installed: ' . $initial);
        }

        $installPath = $this->repository->getInstallPath($initial);

        $this->file->delete($installPath);
        $this->repository->removePackage($initial);
        $this->file->copyDir(dirname($packageFile), $installPath);

        if (!$this->repository->hasPackage($update)) {
            $this->repository->addPackage(clone $update);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(PackageInterface $package)
    {
        if (!$this->repository->hasPackage($package)) {
            throw new LogicException('Package is not installed: ' . $package);
        }

        $this->file->delete($this->repository->getInstallPath($package));
        $this->repository->removePackage($package);
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled(PackageInterface $package)
    {
        return is_dir($this->repository->getInstallPath($package));
    }
}
