<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Ruby
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Ruby extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Ruby Development Environment';
    public const SOFTWARE = [
        'ruby',
        'ruby-dev'
    ];
    public const REQUIREMENTS = [];

    public const VERSION_TAG = 'ruby';
    public const PACKAGES = [
        'bundle',
        'rake',
    ];
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(60);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            foreach(self::PACKAGES as $package)
            {
                $this->execute(self::SUDO.' '.self::GEM.' install '.$package);
                $this->progBarAdv(5);
            }
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(60);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarAdv(5);
            foreach(self::PACKAGES as $package)
            {
                $this->execute(self::SUDO.' '.self::GEM.' uninstall '.$package);
                $this->progBarAdv(5);
            }
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
    }
}