<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class RabbitMQ extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Php72::class,
    ];
    public const PREFERENCES_CONTENT = '# /etc/apt/preferences.d/erlang
Package: erlang*
Pin: version 1:19.3-1
Pin-Priority: 1000
Package: esl-erlang
Pin: version 1:19.3.6
Pin-Priority: 1000';
    public const PREFERENCES_FILE = '/etc/apt/preferences.d/erlang';
    public const SOFTWARE = [
        'rabbitmq-server',
        'erlang-nox',
    ];
    public const COMMANDS = [
        self::SUDO.' wget http://repos.zend.com/zend-server/early-access/ZRay-Homestead/zray-standalone-php72.tar.gz -O - | sudo tar -xzf - -C /opt',
        self::SUDO .' ln -sf /home/vagrant/base/etc/php/zray.ini /etc/php/7.2/fpm/conf.d/20-zray.ini',
        self::SUDO.' ln -sf /opt/zray/lib/zray.so /usr/lib/php/20170718/zray.so',
        self::SUDO.' chown -R vagrant:vagrant /opt/zray',
    ];
    public const VERSION_TAG = 'rabbitmq';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->execute('echo "'.self::PREFERENCES_CONTENT.'" | '.self::SUDO.' '.self::TEE.' '.self::PREFERENCES_FILE);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            if(
                $this->getConfig()->getFeatures()->contains(Php56::class)
                ||
                $this->getConfig()->getFeatures()->contains(Php70::class)
                ||
                $this->getConfig()->getFeatures()->contains(Php71::class)
                || $this->getConfig()->getFeatures()->contains(Php72::class)
            ) {
                $this->execute(self::SUDO.' '.self::APT.' install php-amqp');
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            $this->execute(self::SUDO.' rabbitmq-plugins enable rabbitmq_management');
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
            $this->execute(self::SUDO.' '.self::RM.' -Rf '.self::PREFERENCES_FILE);
            $this->progBarAdv(5);
            if(
                $this->getConfig()->getFeatures()->contains(Php56::class)
                ||
                $this->getConfig()->getFeatures()->contains(Php70::class)
                ||
                $this->getConfig()->getFeatures()->contains(Php71::class)
                || $this->getConfig()->getFeatures()->contains(Php72::class)
            ) {
                $this->execute(self::SUDO.' '.self::APT.' purge php-amqp');
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
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