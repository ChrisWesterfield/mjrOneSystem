<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Mongo
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Mongo extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const ALTERNATIVE_MODE = true;
    public const COMMANDS = [
        self::SUDO . ' apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 2930ADAE8CAF5059EE73BB4B58712A2291FA4AD5',
        'echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.6 multiverse" | '.self::SUDO.' /usr/bin/tee /etc/apt/sources.list.d/mongodb-org-3.6.list',
        self::SUDO . ' ' . self::APT . ' update',
    ];
    public const SOFTWARE = [
        'mongodb-org',
    ];
    public const VERSION_TAG = 'mongo';
    public const BIN = '/usr/bin/mongo';
    public const BIND_IP = self::SUDO . ' ' . self::SED . ' -i "s/bindIp: .*/bindIp: 0.0.0.0/" /etc/mongod.conf';
    public const ENABLE = self::ENABLE_SERVICE . ' mongod';
    public const START = self::SERVICE_CMD . ' start mongod';
    public const CONFIGURE = [
        self::SUDO . ' mongo admin --eval "db.createUser({user:\'vagrant\',pwd:\'123\',roles:[\'root\']})"',
    ];
    public const DEFAULT_PORT = 27017;

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
            foreach (self::COMMANDS as $cmd) {
                $this->execute($cmd);
                $this->progBarAdv(5);
            }
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::BIND_IP);
            $this->progBarAdv(5);
            $this->execute(self::ENABLE);
            $this->progBarAdv(5);
            $this->execute(self::START);
            $this->progBarAdv(5);
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
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(30);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
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
        foreach(self::CONFIGURE as $config)
        {
            $this->execute($config);
        }
    }
}