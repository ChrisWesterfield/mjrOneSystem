<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class FlyWay
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class FlyWay extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const SOFTWARE = [];
    public const FILE_NAME_TGZ = 'flyway.tgz';
    public const COMMANDS = [
        self::CURL.' https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/5.0.7/flyway-commandline-5.0.7-linux-x64.tar.gz | '.self::SUDO.' '.self::TEE.' '.self::LOCAL.self::FILE_NAME_TGZ,
        self::SUDO.' '.self::TAR.' -zxvf '.self::LOCAL.self::FILE_NAME_TGZ .' -C '.self::LOCAL,
        self::SUDO.' '.self::MV.' '.self::LOCAL.'flyway-5.0.7'.' '.self::LOCAL.self::VERSION_TAG,
        self::SUDO.' '.self::CHMOD.' +x '.self::LOCAL.self::VERSION_TAG.'/'.self::VERSION_TAG,
        self::SUDO.' '.self::RM.' '.self::LOCAL.self::FILE_NAME_TGZ,
    ];
    public const VERSION_TAG = 'flyway';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
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
            $this->createLink(self::LOCAL.self::VERSION_TAG.'/'.self::VERSION_TAG, self::LOCAL_BIN.self::VERSION_TAG);
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
            $this->progBarInit(25);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf '.self::LOCAL.self::VERSION_TAG);
            $this->execute(self::SUDO.' rm -Rf '.self::LOCAL_BIN.self::VERSION_TAG);
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