<?php
declare(strict_types=1);
namespace App\Process;

use Symfony\Component\Process\Process;

/**
 * Class Ant
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Angular extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE = [];
    public const REQUIREMENTS = [
        Yarn::class,
    ];
    public const VERSION_TAG = 'ant';
    public const DESCRIPTION = 'Ant Build System';

    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(10);
            $this->execute('yarn global add @angular/cli');
            $this->progBarAdv(10);
            $this->execute('yarn global add forever');
            $this->progBarAdv(20);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function uninstall():void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(10);
            $this->execute('yarn global remove @angular/cli');
            $this->progBarAdv(10);
            $this->execute('yarn global remove forever');
            $this->progBarAdv(20);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function configure():void
    {

    }
}