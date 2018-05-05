<?php
declare(strict_types=1);

namespace App\Process;


use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class Php72
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Php72 extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'PHP Environment';
    public const VERSION = '7.2';
    public const SERVICE_NAME = 'php' . self::VERSION . '-fpm';
    public const DIR_MODS_AVAILABLE = '/etc/php/' . self::VERSION . '/mods-available/';
    public const DIR_CONF_CLI = '/etc/php/' . self::VERSION . '/cli/conf.d/';
    public const DIR_CONF_FPM = '/etc/php/' . self::VERSION . '/fpm/conf.d/';
    public const DIR_POOL = '/etc/php/' . self::VERSION . '/fpm/pool.d/';
    public const OPCACHE_FILE = 'opcache.ini';
    public const MJRONE_FILE = 'mjrone.ini';
    public const SOFTWARE = [
        'php' . self::VERSION . '-common',
        'php' . self::VERSION . '-bcmath',
        'php' . self::VERSION . '-cli',
        'php' . self::VERSION . '-dev',
        'php' . self::VERSION . '-enchant',
        'php' . self::VERSION . '-curl',
        'php' . self::VERSION . '-imap',
        'php' . self::VERSION . '-gd',
        'php' . self::VERSION . '-intl',
        'php' . self::VERSION . '-json',
        'php' . self::VERSION . '-mbstring',
        'php' . self::VERSION . '-opcache',
        'php' . self::VERSION . '-pspell',
        'php' . self::VERSION . '-readline',
        'php' . self::VERSION . '-recode',
        'php' . self::VERSION . '-soap',
        'php' . self::VERSION . '-sqlite3',
        'php' . self::VERSION . '-tidy',
        'php' . self::VERSION . '-xml',
        'php' . self::VERSION . '-xmlrpc',
        'php' . self::VERSION . '-zip',
        'php' . self::VERSION . '-xsl',
        'php' . self::VERSION . '-fpm',
    ];
    public const REQUIREMENTS = [
        Nginx::class,
    ];
    public const VERSION_TAG = 'php72';
    public const FPM_IDENTITY = 'admin.info72';
    public const SUBDOMAIN = 'info72.';
    public const SYSTEM_FPM_IDENTITY = 'admin.system';
    public const SYSTEM_SUBDOMAIN = '';

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::SERVICE_NAME.' '.self::SERVICE_RESTART);
    }

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(35);
            $mjrone = file_get_contents(__DIR__ . '/../../templates/configuration/php/config/mjrone.conf');
            $this->progBarAdv(5);
            $this->execute('echo "' . $mjrone . '" | ' . self::SUDO . ' ' . self::TEE . ' ' . self::DIR_MODS_AVAILABLE . self::MJRONE_FILE);
            $this->progBarAdv(5);
            $opcache = file_get_contents(__DIR__ . '/../../templates/configuration/php/config/opcache.conf');
            $this->progBarAdv(5);
            $this->execute('echo "' . $opcache . '" | ' . self::SUDO . ' ' . self::TEE . ' ' . self::DIR_MODS_AVAILABLE . self::OPCACHE_FILE);
            $this->progBarAdv(5);
            $this->createLink(self::DIR_MODS_AVAILABLE . self::MJRONE_FILE, self::DIR_CONF_CLI . '20-' . self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->createLink(self::DIR_MODS_AVAILABLE . self::MJRONE_FILE, self::DIR_CONF_FPM . '20-' . self::MJRONE_FILE);
            $this->progBarAdv(5);
            if (
                $this->getConfig()->getFeatures()->contains(Maria::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL56::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL57::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL8::class)
            ) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install php' . self::VERSION . '-mysql');
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(PostgreSQL::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install php' . self::VERSION . '-pgsql');
                $this->progBarAdv(5);
            }
            $this->execute(self::SERVICE_CMD . ' ' . self::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
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
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(60);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            if (
                $this->getConfig()->getFeatures()->contains(Maria::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL56::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL57::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL8::class)
            ) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge php' . self::VERSION . '-mysql');
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(PostgreSQL::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge php' . self::VERSION . '-pgsql');
                $this->progBarAdv(5);
            }
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(15);
            $this->execute(self::SUDO.' '.self::RM.' ' . self::INSTALLED_APPS_STORE. self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' ' . self::DIR_CONF_FPM.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' ' . self::DIR_CONF_CLI.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' ' . self::DIR_MODS_AVAILABLE.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            if($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN.$this->getConfig()->getName()))
            {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN.$this->getConfig()->getName());
            }
            if($this->getConfig()->getFpm()->containsKey(self::FPM_IDENTITY))
            {
                $listen = explode(':',$this->getConfig()->getFpm()->get(self::FPM_IDENTITY));
                $port = (int)$listen[1];
                $this->getConfig()->getUsedPorts()->removeElement($port);
                $this->getConfig()->getFpm()->remove(self::FPM_IDENTITY);
            }
            $this->progBarFin();
        }
    }

    /**
     * @param $file
     * @param $template
     * @param $variables
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render($file, $template, $variables): void
    {
        $rendered = $this->getContainer()->get('twig')->render($template, $variables);
        @file_put_contents(self::DIR_POOL . $file, $rendered);
    }

    /**
     *
     */
    public function configure(): void
    {
        $this->addSite(
            [
                'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
                'type' => 'PhpApp',
                'to' => self::VAGRANT_SYSTEM . '/public/' . self::VERSION,
                'fpm' => true,
                'zRay' => false,
                'category' => Site::CATEGORY_INFO,
                'description'=>'Php 7.2 Info Page',
            ],
            [
                'name' => self::FPM_IDENTITY,
                'user' => 'vagrant',
                'group' => 'vagrant',
                'listen' => '127.0.0.1:%%%PORT%%%',
                'pm' => Fpm::ONDEMAND,
                'maxChildren' => 2,
                'version' => self::VERSION,
                'xdebug'=>false,
            ]

        );
        $this->addSite(
            [
                'map' => self::SYSTEM_SUBDOMAIN . $this->getConfig()->getName(),
                'type' => 'Symfony4',
                'to' => self::VAGRANT_SYSTEM . '/public/',
                'fpm' => true,
                'zRay' => false,
                'category' => Site::CATEGORY_INFO,
                'description'=>'Startpage',
            ],
            [
                'name' => self::SYSTEM_FPM_IDENTITY,
                'user' => 'vagrant',
                'group' => 'vagrant',
                'listen' => '127.0.0.1:%%%PORT%%%',
                'pm' => Fpm::ONDEMAND,
                'maxChildren' => 2,
                'version' => self::VERSION,
                'xdebug'=>false,
            ]

        );
    }
}