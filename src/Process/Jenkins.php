<?php
declare(strict_types=1);
namespace App\Process;
use App\Process\QaTools\PhpCpd;
use App\Process\QaTools\PhpCs;
use App\Process\QaTools\PhpDox;
use App\Process\QaTools\PhpLOC;
use App\Process\QaTools\PhpUnit;

/**
 * Class Jenkins
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Jenkins extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE = [
        'jenkins'
    ];
    public const COMMANDS = [
        self::WGET.' -q -O - https://jenkins-ci.org/debian/jenkins-ci.org.key | '.self::SUDO.' apt-key add -',
        'echo "deb http://pkg.jenkins-ci.org/debian binary/" | '.self::SUDO.' '.self::TEE.' /etc/apt/sources.list.d/jenkins.list',
        self::SUDO.' '.self::APT.' update',
    ];
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const VERSION_TAG = 'jenkins';

    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(65);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $com)
            {
                $this->execute($com);
                $this->progBarAdv(5);
            }
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(20);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function uninstall():void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(10);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $this->execute(self::SUDO.' '.self::RM.' -f /etc/apt/sources.list.d/jenkins.list');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function configure():void
    {

    }
}