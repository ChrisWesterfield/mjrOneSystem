<?php
declare(strict_types=1);

namespace App\Process;

use App\Process\QaTools\PhpCpd;
use App\Process\QaTools\PhpCs;
use App\Process\QaTools\PhpDox;
use App\Process\QaTools\PhpLOC;
use App\Process\QaTools\PhpUnit;
use App\System\Config\Site;

/**
 * Class Jenkins
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Jenkins extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Jenkins Build Server';
    public const SOFTWARE = [
        'jenkins'
    ];
    public const COMMANDS = [
        self::WGET . ' -q -O - https://jenkins-ci.org/debian/jenkins-ci.org.key | ' . self::SUDO . ' apt-key add -',
        'echo "deb http://pkg.jenkins-ci.org/debian binary/" | ' . self::SUDO . ' ' . self::TEE . ' /etc/apt/sources.list.d/jenkins.list',
        self::SUDO . ' ' . self::APT . ' update',
    ];
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const VERSION_TAG = 'jenkins';
    public const SUBDOMAIN = 'jenkins.';
    public const DEFAULT_PORT = 8080;

    /**
     *
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(65);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            foreach (self::COMMANDS as $com) {
                $this->execute($com);
                $this->progBarAdv(5);
            }
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(20);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }

    public function uninstall(): void
    {
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(10);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $this->execute(self::SUDO . ' ' . self::RM . ' -f /etc/apt/sources.list.d/jenkins.list');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->remove(self::DEFAULT_PORT);
            if ($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN . $this->getConfig()->getName())) {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN . $this->getConfig()->getName());
            }
            $this->progBarFin();
        }
    }

    public function configure(): void
    {
        $this->addSite([
            'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
            'type' => 'Proxy',
            'listen' => '127.0.0.1:' . self::DEFAULT_PORT,
            'category' => Site::CATEGORY_OTHER,
            'description' => 'Jenkins Build Server'
        ]);
    }
}