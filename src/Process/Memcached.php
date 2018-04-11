<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Memcached extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const DESCRIPTION = 'Memcached Server';
    public const SOFTWARE = [
        'memcached',
        'php-memcached',
    ];
    public const PHP_DIRS = [
        '7.0'=>'/usr/lib/php/20151012/',
        '7.1'=>'/usr/lib/php/20160303/',
        '7.2'=>'/usr/lib/php/20170718/',
    ];
    public const PHP_LIB_DIR = '/home/vagrant/php-memcached';
    public const COMMANDS = [
        self::GIT_CLONE.' https://github.com/websupport-sk/pecl-memcache.git '.self::PHP_LIB_DIR,
        'cd '.self::PHP_LIB_DIR.' && '.self::GIT.' checkout php7',
    ];
    public const VERSION_TAG = 'memcached';
    public const DEFAULT_PORT = 11211;
    public const VERSION_COMMANDS =
    [
        self::SUDO.' '.self::APT.' install php%s-memcache -y',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' /usr/bin/make clean',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' '.self::GIT.' reset --hard HEAD',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' '.self::RM.' -Rf .deps config.h.in~ configure.ac -y ',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' /usr/bin/phpize%s ',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' ./configure ',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' /usr/bin/make ',
        'cd '.self::PHP_LIB_DIR.' && ' . self::SUDO.' /usr/bin/make install ',
    ];

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }

    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(170);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            if(
                file_exists(self::INSTALLED_APPS_STORE.Php70::VERSION_TAG)
                ||
                file_exists(self::INSTALLED_APPS_STORE.Php71::VERSION_TAG )
                ||
                file_exists(self::INSTALLED_APPS_STORE.Php72::VERSION_TAG))
            {
                foreach(self::COMMANDS as $COMMAND)
                {
                    $this->execute($COMMAND);
                    $this->progBarAdv(5);
                }
                $obj = $this;
                $phpV = function($version) use ($obj)
                {
                    foreach(Memcached::VERSION_COMMANDS as $com)
                    {
                        if(strpos($com, '%s')!==false)
                        {
                            $com = sprintf($com, '7.0');
                        }
                        $obj->execute($com);
                        $obj->progBarAdv(5);
                    }
                };
                if(file_exists(self::INSTALLED_APPS_STORE.Php70::VERSION_TAG))
                {
                    $phpV(Php70::VERSION);
                }
                if(file_exists(self::INSTALLED_APPS_STORE.Php71::VERSION_TAG))
                {
                    $phpV(Php71::VERSION);
                }
                if(file_exists(self::INSTALLED_APPS_STORE.Php70::VERSION_TAG))
                {
                    $phpV(Php72::VERSION);
                    $this->execute('ln -s /usr/lib/php/20170718/memcached.so /usr/lib/php/20170718/memcached.so.so');
                    $this->progBarAdv(5);
                }
            }
            if(file_exists(self::INSTALLED_APPS_STORE.Php56::VERSION_TAG))
            {
                $this->execute(self::SUDO.' '.self::APT.' install -y php5.6-memcached php5.6-memcache');
                $this->progBarAdv(5);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
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
            $this->progBarInit(150);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            if(file_exists(self::INSTALLED_APPS_STORE.Php56::VERSION_TAG))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge -y php5.6-memcached php5.6-memcache');
                $this->progBarAdv(5);
            }
            $obj = $this;
            $phpV = function($version) use ($obj)
            {
                $files = [
                    Memcached::PHP_DIRS[$version],
                ];
                foreach($files as $file)
                {
                    $obj->execute(self::SUDO.' '.self::RM.' -f '.$file);
                    $obj->progBarAdv(5);
                }
            };
            if(file_exists(self::INSTALLED_APPS_STORE.Php70::VERSION_TAG))
            {
                $phpV(Php70::VERSION);
            }
            if(file_exists(self::INSTALLED_APPS_STORE.Php71::VERSION_TAG))
            {
                $phpV(Php71::VERSION);
            }
            if(file_exists(self::INSTALLED_APPS_STORE.Php72::VERSION_TAG))
            {
                $phpV(Php72::VERSION);
                $this->execute(self::SUDO.' '.self::RM.' -f /usr/lib/php/20170718/memcached.so.so');
            }
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf '.self::PHP_LIB_DIR);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
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