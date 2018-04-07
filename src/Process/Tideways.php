<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Tideways extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Php72::class,
    ];
    public const SOFTWARE = [
        'php-tideways'
    ];
    public const COMMANDS = [
        self::SUDO.' '.self::RM.' -f /etc/php/%s/cli/conf.d/20-tideways.ini',
        self::SUDO.' '.self::RM.' -f /etc/php/%s/fpm/conf.d/20-tideways.ini',
    ];
    public const COMMANDS2 = [
        self::SUDO.' '.self::LN.' '.self::VAGRANT_ETC.'/php/tideways.%s.ini /etc/php/%s/cli/conf./20-tideways.ini',
        self::SUDO.' '.self::LN.' '.self::VAGRANT_ETC.'/php/tideways.%s.ini /etc/php/%s/fpm/conf./20-tideways.ini',
    ];
    public const VERSION_TAG = 'tideways';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(130);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $obj = $this;
            $phpV = function($version) use ($obj)
            {
                foreach(Tideways::COMMANDS as $com)
                {
                    $obj->execute(sprintf($com, $version));
                    $obj->progBarAdv(5);
                }
                foreach(Tideways::COMMANDS2 as $com)
                {
                    $obj->execute(sprintf($com, $version, $version));
                    $obj->progBarAdv(5);
                }
            };
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $phpV(Php72::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $phpV(Php71::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $phpV(Php70::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $phpV(Php56::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
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
            $this->progBarInit(90);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $obj = $this;
            $phpV = function($version) use ($obj) {
                foreach (Tideways::COMMANDS as $com) {
                    $obj->execute(sprintf($com, $version));
                    $obj->progBarAdv(5);
                }
            };
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $phpV(Php72::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $phpV(Php71::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $phpV(Php70::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $phpV(Php56::VERSION);
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
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