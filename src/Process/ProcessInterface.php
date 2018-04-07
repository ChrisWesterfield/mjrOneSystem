<?php
declare(strict_types=1);

namespace App\Process;


use App\System\SystemConfig;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface ProcessInterface
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
interface ProcessInterface
{
    public const INSTALLED_APPS_STORE = '/home/vagrant/.apps/';
    public const LOCAL = '/usr/local/';
    public const LOCAL_BIN = self::LOCAL . 'bin/';
    public const SUDO = '/usr/bin/sudo';
    public const MAKE = '/usr/bin/make';
    public const APT = ' DEBIAN_FRONTEND=noninteractive  /usr/bin/apt-get';
    public const APT_INSTALL = self::SUDO . ' ' . self::APT . ' install -y %s';
    public const APT_INSTALL_ALT = self::SUDO . ' ' . 'DEBIAN_FRONTEND=noninteractive ' . self::APT . ' -yq -o Dpkg::Options::="--force-confnew" install %s';
    public const APT_UNINSTALL = self::SUDO . ' ' . self::APT . ' purge -y %s';
    public const RELOAD_DAEMON = self::SUDO . ' systemctl daemon-reload';
    public const SYSTEMCTL = '/bin/systemctl';
    public const SERVICE = '/usr/sbin/service';
    public const SERVICE_CMD = self::SUDO . ' ' . self::SERVICE;
    public const ENABLE_SERVICE = self::SUDO . ' ' . self::SYSTEMCTL . ' enable';
    public const DISABLE_SERVICE = self::SUDO . ' ' . self::SYSTEMCTL . ' disable';
    public const SERVICE_RESTART = 'restart';
    public const SERVICE_STOP = 'stop';
    public const SERVICE_START = 'start';
    public const LN = '/bin/ln';
    public const LINK = self::LN.' -sfn %s %s';
    public const GIT = '/usr/bin/git';
    public const GIT_CLONE = self::GIT . ' clone ';
    public const GEM = '/usr/bin/gem';
    public const SH = '/bin/sh';
    public const BUNDLE = self::LOCAL_BIN . 'bundle';
    public const SED = '/bin/sed';
    public const TEE = '/usr/bin/tee';
    public const COMPOSER = self::LOCAL_BIN . 'composer';
    public const WGET = '/usr/bin/wget';
    public const CHMOD = '/bin/chmod';
    public const CHOWN = '/bin/chown';
    public const CURL = '/usr/bin/curl';
    public const TAR = '/bin/tar';
    public const MV = '/bin/mv';
    public const RM = '/bin/rm';
    public const DPKG = '/usr/bin/dpkg';
    public const BASH = '/bin/bash';
    public const NPM = '/usr/bin/npm';
    public const MKDIR = '/bin/mkdir';
    public const APT_KEY = '/usr/bin/apt-key';
    public const VAGRANT_HOME = '/home/vagrant/base';
    public const VAGRANT_ETC = self::VAGRANT_HOME.'/etc';
    public const VAGRANT_BIN = self::VAGRANT_HOME.'/bin';
    public const SUPERVISOR_D = self::VAGRANT_ETC.'/supervisor';
    public const UPD_ALT = '/usr/sbin/update-alternatives';

    /**
     * @param SystemConfig $config
     * @return mixed
     */
    public function setConfig(SystemConfig $config);

    /**
     * @return SystemConfig
     */
    public function getConfig(): SystemConfig;

    /**
     * @return void
     */
    public function install(): void;

    /**
     *
     */
    public function uninstall(): void;

    /**
     * @return mixed
     */
    public function configure(): void;

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface;

    /**
     * @param OutputInterface $output
     * @return ProcessInterface
     */
    public function setOutput(OutputInterface $output): ProcessInterface;

    /**
     * @param SymfonyStyle $io
     * @return ProcessAbstract
     */
    public function setIo(SymfonyStyle $io): ProcessAbstract;

    /**
     * @return SymfonyStyle
     */
    public function getIo(): SymfonyStyle;

    /**
     * @param ContainerInterface $container
     * @return ProcessInterface
     */
    public function setContainer(ContainerInterface $container): ProcessInterface;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}