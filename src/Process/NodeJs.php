<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class NodeJs
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class NodeJs extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Software development System';
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'nodejs',
    ];
    public const COMMANDS = [
        self::SUDO.' '.self::APT.' purge nodejs npm -y',
        self::CURL.' -sL https://deb.nodesource.com/setup_6.x | '.self::SUDO.' '.self::BASH.' -',
        self::SUDO.' '.self::APT.' install -y nodejs',
        self::SUDO.' '.self::NPM.' install -g npm',
        self::SUDO.' '.self::NPM.' install -g gulp-cli',
        self::SUDO.' '.self::NPM.' install -g bower',
        self::SUDO.' '.self::NPM.' install -g yarn',
        self::SUDO.' '.self::NPM.' install -g grunt-cli',
    ];
    public const VERSION_TAG = 'zray';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(55);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
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