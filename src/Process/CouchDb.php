<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class CouchDb extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const VERSION_TAG = 'couchdb';
    public const SOFTWARE = [
        'couchdb'
    ];
    public const COMMANDS = [
        self::SUDO.' add-apt-repository ppa:couchdb/stable',
        self::SUDO.' '.self::APT.' update'
    ];
    public const COUCH_DIR = [
        '/usr/bin/couchdb',
        '/etc/couchdb',
        '/usr/share/couchdb',
    ];
    public const COMMANDS2 = [
        self::SUDO.' '.self::SED.' -i "s/;bind_address =.*/bind_address = 0.0.0.0/" /etc/couchdb/local.ini',
        self::SUDO.' '.self::CHMOD.' -R 0770 '.self::COUCH_DIR[0].' '.self::COUCH_DIR[1].' '.self::COUCH_DIR[2],
        self::SUDO.' '.self::CHOWN.' -R couchdb:couchdb '.self::COUCH_DIR[0].' '.self::COUCH_DIR[1].' '.self::COUCH_DIR[2],
        self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART
    ];
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(70);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
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
            $this->progBarInit(40);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
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