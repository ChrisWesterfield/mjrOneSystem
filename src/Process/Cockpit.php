<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Cockpit
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Cockpit extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'cockpit',
        'cockpit-bridge',
        'cockpit-ws',
        'cockpit-system',
    ];
    public const COMMANDS = [
        self::SUDO.' add-apt-repository ppa:cockpit-project/cockpit -y',
        self::SUDO .' '.self::APT.' update',
    ];
    public const VERSION_TAG = 'cockpit';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(75);
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
            if(file_exists(self::INSTALLED_APPS_STORE.Docker::VERSION_TAG))
            {
                $this->execute(self::SUDO. ' '.self::APT.' install -y cockpit-docker');
                $this->progBarAdv(25);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->execute(self::SERVICE_CMD.' cockpit '.self::SERVICE_START);
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
            $this->progBarInit(65);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            if(file_exists(self::INSTALLED_APPS_STORE.Docker::VERSION_TAG))
            {
                $this->execute(self::SUDO. ' '.self::APT.' purge -y cockpit-docker');
                $this->progBarAdv(20);
            }
            $this->execute(self::SUDO.' '.self::RM.' -f /etc/apt/sources.list.d/cockpit-project-ubuntu-cockpit-xenial.list');
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