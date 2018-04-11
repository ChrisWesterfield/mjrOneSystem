<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Yarn
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Yarn extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Javascript Packaging System';
    public const REQUIREMENTS = [
        NodeJs::class,
    ];
    public const SOFTWARE = [
        'git-core',
        'curl',
        'zlib1g-dev',
        'build-essential',
        'libssl-dev',
        'libreadline-dev',
        'libyaml-dev',
        'libsqlite3-dev',
        'sqlite3',
        'libxml2-dev',
        'libxslt1-dev',
        'libcurl4-openssl-dev',
        'python-software-properties',
        'libffi-dev',
        'yarn',
        'libxml2',
        'libxml2-dev',
        'libxslt1-dev',
        'libcurl4-openssl-dev'
    ];
    public const APT_FILE ='/etc/apt/sources.list.d/yarn.list';
    public const COMMANDS = [
        self::CURL.' -sS https://dl.yarnpkg.com/debian/pubkey.gpg | '.self::SUDO.' '.self::APT_KEY.' add -',
        'echo "deb https://dl.yarnpkg.com/debian/ stable main" | '.self::SUDO.' '.self::TEE.' '.self::APT_FILE,
        self::SUDO.' '.self::APT.' update',
    ];
    public const VERSION_TAG = 'yarn';
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
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->installPackages(self::SOFTWARE);
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
            $this->progBarInit(55);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(['yarn']);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::APT_FILE);
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