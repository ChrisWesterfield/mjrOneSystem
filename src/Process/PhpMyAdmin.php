<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class PhpMyAdmin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class PhpMyAdmin extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Php72::class,
        Composer::class,
    ];
    public const SOFTWARE = [];
    public const APP_DIR = '/home/vagrant/PhpMyAdmin';
    public const COMMANDS = [
        self::GIT_CLONE.' https://github.com/phpmyadmin/phpmyadmin.git '.self::APP_DIR,
        self::COMPOSER.' install -vvv --profile -d '.self::APP_DIR,
        'cd '.self::APP_DIR.'/temes && '.self::WGET .' https://files.phpmyadmin.net/themes/fallen/0.5/fallen-0.5.zip | unzip -'
    ];
    public const VERSION_TAG = 'phpmyadmin';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $com)
            {
                $this->execute($com);
                $this->progBarAdv(10);
            }
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::CHOWN.' -R vagrant:vagrant '.self::APP_DIR);
            $this->progBarAdv(5);
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
            $this->progBarInit(45);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' rm -Rf '.self::APP_DIR);
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