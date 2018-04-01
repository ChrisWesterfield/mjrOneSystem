<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Netdata
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Netdata extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [];
    public const COMMANDS = [
        self::SUDO.' /bin/bash /home/vagrant/base/system/bin/netdata.install.bash',
        seLf::SERVICE_CMD.' netdata '.self::SERVICE_RESTART,
    ];
    public const VERSION_TAG = 'netdata';
    public const COMMANDS_UNINSTALL = [
        self::DISABLE_SERVICE.' netdata ',
        self::SERVICE_CMD.' netdata '.self::SERVICE_STOP,
        self::SUDO.' '.self::RM.' -Rf /usr/src/netdata.git',
        self::SUDO.' /usr/sbin/groupdel netdata',
        self::SUDO.' /usr/sbin/userdel netdata',
        self::SUDO.' '.self::RM.' -f /etc/logrotate.d/netdata',
        self::SUDO.' '.self::RM.' -f /etc/systemd/system/netdata.service',
        self::SUDO.' '.self::RM.' -f /etc/init.d/netdata',
        self::SUDO.' '.self::SYSTEMCTL.' daemon-reload',
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
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(25);
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
            $this->progBarInit(80);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach(self::COMMANDS_UNINSTALL as $cmd)
            {
                $this->execute($cmd);
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