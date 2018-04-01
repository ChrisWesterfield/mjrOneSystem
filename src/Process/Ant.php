<?php
declare(strict_types=1);
namespace App\Process;
use App\Process\QaTools\PhpCpd;
use App\Process\QaTools\PhpCs;
use App\Process\QaTools\PhpDox;
use App\Process\QaTools\PhpLOC;
use App\Process\QaTools\PhpUnit;

/**
 * Class Ant
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Ant extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE = [
        'ant'
    ];
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const VERSION_TAG = 'ant';

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
            $this->progBarAdv(20);
            $this->installPackages(self::SOFTWARE);
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
            $this->progBarAdv(20);
            $this->uninstallPackages(self::SOFTWARE);
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