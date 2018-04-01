<?php
declare(strict_types=1);
namespace App\Process;
use App\Process\QaTools\PhpCpd;
use App\Process\QaTools\PhpCs;
use App\Process\QaTools\PhpDox;
use App\Process\QaTools\PhpLOC;
use App\Process\QaTools\PhpUnit;

/**
 * Class Ant
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class MailHog extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE = [
        'daemon',
        'fakeroot'
    ];
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const VERSION_TAG = 'mailhog';
    public const TMP_TARGET='/home/vagrant/mailhog';

    public const COMMANDS = [
        self::GIT_CLONE.' https://github.com/deogracia/MailHog-debian-package '.self::TMP_TARGET,
        'cd '.self::TMP_TARGET.' && mkdir -p package/usr/sbin ',
        'cd '.self::TMP_TARGET.' && '.self::SUDO.' '.self::CHMOD.' 0755 package/usr/sbin',
        'cd '.self::TMP_TARGET.'/package/usr/sbin && '.self::SUDO.' '.self::WGET.' https://github.com/mailhog/MailHog/releases/download/v0.1.6/MailHog_linux_amd64',
        'cd '.self::TMP_TARGET.'/package/usr/sbin && '.self::SUDO .' '.self::MV.' MailHog_linux_amd64 mailhog',
        'cd '.self::TMP_TARGET.'/package/usr/sbin && '.self::SUDO .' '.self::CHMOD.' 0755 mailhog',
        'cd '.self::TMP_TARGET.' && '.self::SUDO .' '.' /bin/bash ./restore-permission.bash',
        self::SUDO.' '.self::CHMOD.' -R 0755 '.self::TMP_TARGET,
        'cd '.self::TMP_TARGET.' && '.self::SUDO .' /usr/bin/dpkg-deb --build package',
        'cd '.self::TMP_TARGET.' && '.self::SUDO .' '.self::MV.' package.deb mailhog-VERSION-amd64.deb',
        'cd '.self::TMP_TARGET.' && '.self::SUDO .' dpkg -i mailhog-VERSION-amd64.deb ',
        self::SUDO.' '.self::SED.' -i \'s/0.0.0.0:8025/127.0.0.1:8025/g\' /etc/default/mailhog',
        self::SUDO.' '.self::SED.' -i \'s/0.0.0.0:1025/127.0.0.1:1025/g\' /etc/default/mailhog',
        self::SUDO.' '.self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART,
    ];

    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(155);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(10);
            }
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
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::APT.' purge -y '.self::VERSION_TAG);
            $this->progBarAdv(30);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function configure():void
    {

    }
}