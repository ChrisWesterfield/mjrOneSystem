<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Composer
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Composer extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
    ];
    public const SOFTWARE = [];
    public const DESCRIPTION = 'Php Composer to manage PHP Project Requirements';
    public const COMMANDS = [
        self::CURL.' https://getcomposer.org/composer.phar | '.self::SUDO.' '.self::TEE.' '.self::COMPOSER,
        self::SUDO.' '.self::CHMOD.' +x '.self::COMPOSER,
    ];
    public const VERSION_TAG = 'composer';
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
            $this->execute(self::SUDO.' rm -Rf '.self::COMPOSER);
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