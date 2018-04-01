<?php
declare(strict_types=1);
namespace App\Process\QaTools;
use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;

/**
 * Class PhpUnit
 * @package App\Process\QaTools
 * @author chris westerfield <chris@mjr.one>
 */
class PhpDox extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
    ];
    public const SOFTWARE = [];
    public const COMMANDS = [
        self::CURL.' http://phpdox.de/releases/phpdox.phar | '.self::SUDO.' '.self::TEE.' '.self::LOCAL_BIN.'phpdox',
        self::SUDO.' '.self::CHMOD.' +x '.self::LOCAL_BIN.'phpdox',
    ];
    public const VERSION_TAG = 'phpdox';
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
            $this->execute(self::SUDO.' rm -Rf '.self::LOCAL_BIN.'phpdox');
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