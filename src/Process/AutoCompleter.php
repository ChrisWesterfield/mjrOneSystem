<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Composer
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class AutoCompleter extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Composer::class,
    ];
    public const SOFTWARE = [];
    public const DESCRIPTION = 'Bash AutoComplete Generator for PHP Commands based on symfony';
    public const APP_DIR = self::VAGRANT_USER_DIR.'/autocomplete';
    public const VERSION_TAG = 'autocompleter';
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
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::MKDIR.' '.self::APP_DIR);
            $this->progBarAdv(5);
            $this->execute(Composer::COMPOSER.' require bamarni/symfony-console-autocomplete -d '.self::APP_DIR.' -vvv --profile');
            $this->progBarAdv(5);
            $this->createLink(self::APP_DIR.'/vendor/bin/symfony-autocomplete',self::LOCAL_BIN.'/symfony-autocomplete');
            $this->progBarAdv(25);
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
            $this->progBarInit(35);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf '.self::APP_DIR);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::LOCAL_BIN.'/symfony-autocomplete');
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