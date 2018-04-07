<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Xdebug extends ProcessAbstract implements ProcessInterface
{
    public const XDEBUG_PORT = 9000;
    public const REQUIREMENTS = [
        Php72::class,
    ];
    public const SOFTWARE = [
        'php-xdebug'
    ];
    public const VERSION_TAG = 'xdebug';
    public const DEBUG_FILE = '/etc/php/%s/mods-available/xdebug.ini';
    public const XDEBUG_FILE = 'zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_connect_back=1
xdebug.default_enable = 1
xdebug.remote_autostart = 1
xdebug.remote_connect_back = 1
xdebug.remote_host = %s
xdebug.remote_port = %s
';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(65);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $ipVm = $this->getConfig()->getIp();
            $ipArray = explode('.',$ipVm);
            $ipHost = $ipArray[0].'.'.$ipArray[1].'.'.$ipArray[2].'.1';
            $this->progBarAdv(5);
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $file =sprintf(self::DEBUG_FILE,Php72::VERSION);
                $this->execute('echo "'.sprintf(self::XDEBUG_FILE, $ipHost, self::XDEBUG_PORT).'" | '.self::SUDO.' '.self::TEE.' '.$file);
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            echo 2;
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute('echo "'.sprintf(self::XDEBUG_FILE,$ipHost, self::XDEBUG_PORT).'" | '.self::SUDO.' '.self::TEE.' '.sprintf(self::DEBUG_FILE,Php71::VERSION));
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            echo 3;
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute('echo "'.sprintf(self::XDEBUG_FILE,$ipHost, self::XDEBUG_PORT).'" | '.self::SUDO.' '.self::TEE.' '.sprintf(self::DEBUG_FILE,Php70::VERSION));
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            echo 4;
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute('echo "'.sprintf(self::XDEBUG_FILE,$ipHost, self::XDEBUG_PORT).'" | '.self::SUDO.' '.self::TEE.' '.sprintf(self::DEBUG_FILE,Php56::VERSION));
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
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
            $this->progBarInit(45);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(25);
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