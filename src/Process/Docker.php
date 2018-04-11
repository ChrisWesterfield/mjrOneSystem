<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Docker
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Docker extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const DESCRIPTION = 'Docker Daemon and Tools';
    public const SOFTWARE = [
        'docker-ce'
    ];
    public const APT_FILE = '/etc/apt/sources.list.d/docker.list';
    public const DOCKER_COMPOSE = self::LOCAL_BIN.'docker-compose';
    public const DOCKER_CTOP = self::LOCAL_BIN.'ctop';
    public const DOCKER_CHTOP = self::LOCAL_BIN.'chtop';
    public const COMMANDS = [
        self::CURL.' -fsSL https://download.docker.com/linux/ubuntu/gpg | '.self::SUDO.' apt-key add -',
        self::SUDO.' apt-key fingerprint 0EBFCD88',
        'echo "deb [arch=amd64] https://download.docker.com/linux/ubuntu zesty stable" | '.self::SUDO.' '.self::TEE.' '.self::APT_FILE,
        self::SUDO.' '.self::APT.' update ',
        self::CURL.' -L --fail "https://github.com/docker/compose/releases/download/1.11.2/docker-compose-$(uname -s)-$(uname -m)" -o '.self::DOCKER_COMPOSE,
        self::SUDO.' '.self::CHMOD.' +x '.self::DOCKER_COMPOSE,
        self::CURL.' https://github.com/bcicen/ctop/releases/download/v0.5/ctop-0.5-linux-amd64 | '.self::SUDO.' '.self::TEE.' '.self::DOCKER_CHTOP,
        self::SUDO.' '.self::CHMOD.' +x '.self::DOCKER_CTOP,
        self::CURL.' https://raw.githubusercontent.com/yadutaf/ctop/master/cgroup_top.py | '.self::SUDO.' '.self::TEE.' '.self::DOCKER_CHTOP,
        self::SUDO.' '.self::CHMOD.' +x '.self::DOCKER_CHTOP,
    ];
    public const VERSION_TAG = 'docker';

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }
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
            $this->progBarInit(75);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::APT_FILE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::APT.' update ');
            $this->progBarAdv(10);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::DOCKER_COMPOSE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::DOCKER_CHTOP);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::DOCKER_CTOP);
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