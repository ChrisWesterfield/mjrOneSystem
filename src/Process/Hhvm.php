<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Hhvm
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Hhvm extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'HipHop Virtual Machine (Facebook PHP Engine)';
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'hhvm',
        'hhvm-dev',
    ];
    public const COMMANDS = [
        self::SUDO.' apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xB4112585D386EB94',
        'echo "deb https://dl.hhvm.com/ubuntu xenial main
# deb-src https://dl.hhvm.com/ubuntu xenial main" | '.self::SUDO.' '.self::TEE.' /etc/apt/sources.list.d/hhvm.list',
        self::SUDO.' '.self::APT.' update',
    ];
    public const VERSION_TAG = 'hhvm';
    public const CONFIG_FILE = '/etc/default/hhvm';
    public const DEFAULT_PORT = 9900;
    public const CONFIG_CONTENT = '; php options

pid = /var/run/hhvm/pid

; hhvm specific

hhvm.server.port = '.self::DEFAULT_PORT.'
hhvm.server.type = fastcgi
hhvm.server.default_document = index.php
hhvm.log.use_log_file = true
hhvm.log.file = /vagrant/log/hhvm.error.log
hhvm.repo.central.path = /var/cache/hhvm/hhvm.hhbc
" > /etc/hhvm/server.ini

echo "## This is a configuration file for /etc/init.d/hhvm.
## Overwrite start up configuration of the hhvm service.
##
## This file is sourced by /bin/sh from /etc/init.d/hhvm.

## Configuration file location.
## Default: \"/etc/hhvm/server.ini\"
## Examples:
##   \"/etc/hhvm/conf.d/fastcgi.ini\" Load configuration file from Debian/Ubuntu conf.d style location
#CONFIG_FILE=\"/etc/hhvm/server.ini\"

## User to run the service as.
## Default: \"www-data\"
## Examples:
##   \"hhvm\"   Custom \'hhvm\' user
##   \"nobody\" RHEL/CentOS \'www-data\' equivalent
RUN_AS_USER=\"vagrant\"
RUN_AS_GROUP=\"vagrant\"

## Add additional arguments to the hhvm service start up that you can\'t put in CONFIG_FILE for some reason.
## Default: \"\"
## Examples:
##   \"-vLog.Level=Debug\"                Enable debug log level
##   \"-vServer.DefaultDocument=app.php\" Change the default document
#ADDITIONAL_ARGS=\"\"

## PID file location.
## Default: \"/var/run/hhvm/pid\"
#PIDFILE=\"/var/run/hhvm/pid\"';
    public const SERVICE_NAME = 'hhvm';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(60);
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
            $this->progBarAdv(20);
            $this->execute(self::SERVICE_CMD.' '.self::SERVICE_NAME.' '.self::SERVICE_RESTART);
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
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' '.self::RM.' -Rf /etc/apt/sources.list.d/hhvm.list');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
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