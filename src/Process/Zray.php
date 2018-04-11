<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Zray extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Zend ZRAY Extension (only for PHP7.2)';
    public const REQUIREMENTS = [
        Php72::class,
    ];
    public const SOFTWARE = [];
    public const COMMANDS = [
        self::SUDO.' wget http://repos.zend.com/zend-server/early-access/ZRay-Homestead/zray-standalone-php72.tar.gz -O - | sudo tar -xzf - -C /opt',
        self::SUDO .' ln -sf /home/vagrant/base/etc/php/zray.ini /etc/php/7.2/fpm/conf.d/20-zray.ini',
        self::SUDO.' ln -sf /opt/zray/lib/zray.so /usr/lib/php/20170718/zray.so',
        self::SUDO.' chown -R vagrant:vagrant /opt/zray',
    ];
    public const VERSION_TAG = 'zray';
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
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
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
            $this->progBarInit(55);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' rm -Rf /opt/zray');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf /etc/php/7.2/fpm/conf.d/20-zray.ini');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf /usr/lib/php/20170718/zray.so');
            $this->progBarAdv(5);
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