<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Kibana
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Kibana extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const SOFTWARE = [];
    public const DEB_FILE = '/home/vagrant/kibana.deb';
    public const COMMANDS = [
        self::CURL.' https://artifacts.elastic.co/downloads/kibana/kibana-6.1.1-amd64.deb | '.self::SUDO.' '.self::TEE.' '.self::DEB_FILE,
        self::SUDO.' '.self::DPKG.' -i '.self::DEB_FILE,
        self::SUDO.' '.self::RM.' -f '.self::DEB_FILE,
        self::ENABLE_SERVICE.' '.self::VERSION_TAG,
    ];
    public const VERSION_TAG = 'kibana';
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
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_START);
    }
}