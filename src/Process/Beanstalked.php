<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Beanstalked
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Beanstalked extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'beanstalkd'
    ];
    public const VERSION_TAG = 'beanstalkd';

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(30);
            $this->execute(self::SUDO . ' ' . self::SED . ' -i "s/#START=yes/START=yes/" /etc/default/beanstalkd');
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
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
            $this->progBarInit(55);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(30);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
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