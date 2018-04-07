<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

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
    public const SUBDOMAIN = 'cockpit.';
    public const DEFAULT_PORT = 7777;
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
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            $this->execute(self::SUDO.' '.self::RM.' -f /etc/apt/sources.list.d/cockpit-project-ubuntu-cockpit-xenial.list');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            if($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN .$this->getConfig()->getName()))
            {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN .$this->getConfig()->getName());
            }
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $site = new Site(
            [
                'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
                'type'=>'Proxy',
                'listen'=>'127.0.0.1:'.self::DEFAULT_PORT,
                'category'=>Site::CATEGORY_ADMIN,
            ]
        );
        $this->getConfig()->getSites()->set($site->getMap(),$site);
    }
}