<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Composer
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class WpCli extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Wordpress CLI Tools';
    public const REQUIREMENTS = [
    ];
    public const SOFTWARE = [];
    public const COMMANDS = [
        self::CURL.' https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar | '.self::SUDO.' '.self::TEE.' '.self::LOCAL_BIN.'wp',
        self::SUDO.' '.self::CHMOD.' +x '.self::LOCAL_BIN.'wp',
    ];
    public const VERSION_TAG = 'wpcli';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(30);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
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
            $this->progBarInit(25);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf '.self::LOCAL_BIN.'wp');
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