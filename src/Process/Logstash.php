<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Logstash
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Logstash extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const DESCRIPTION = 'Log Aggregation Tool';
    public const SOFTWARE = [];
    public const DEB_FILE = '/home/vagrant/logstash.deb';
    public const COMMANDS = [
        self::CURL.' https://artifacts.elastic.co/downloads/logstash/logstash-6.1.1.deb | '.self::SUDO.' '.self::TEE.' '.self::DEB_FILE,
        self::SUDO.' '.self::DPKG.' -i '.self::DEB_FILE,
        self::SUDO.' '.self::RM.' -f '.self::DEB_FILE,
        self::SUDO.' /usr/sbin/usermod -a -G adm logstash',
        self::ENABLE_SERVICE.' '.self::VERSION_TAG,
    ];
    public const VERSION_TAG = 'logstash';
    public const DEFAULT_PORT = 5000;
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
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(15);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::APT.' purge -y '.self::VERSION_TAG);
            $this->progBarAdv(20);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
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
        $this->createLink('/home/vagrant/base/etc/logstash/pipeline/logstash.conf','/etc/logstash/conf.d/logstash.conf');
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_START);
    }
}