<?php
declare(strict_types=1);

namespace App\Process;

use App\System\Config\Fpm;
use App\System\Config\Site;


/**
 * Class XhGui
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class XhGui extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Tideways / XHPROF Frontend UI';
    public const REQUIREMENTS = [
        Php72::class,
        Mongo::class,
        MongoDbPhp::class,
        Tideways::class,
    ];
    public const INSTALL_FILE = self::VAGRANT_HOME . '/scripts/xhgui.js';
    public const COMMANDS = [
        self::GIT_CLONE . ' https://github.com/perftools/xhgui.git ' . self::APP_DIR,
        self::CHMOD . ' -R ' . self::APP_DIR . '/cache',
        Mongo::BIN . ' < ' . self::INSTALL_FILE,
    ];
    public const SOFTWARE = [];
    public const VERSION_TAG = 'xhgui';
    public const APP_DIR = '/home/vagrant/xhgui';
    public const FPM_IDENTITY = 'admin.xhgui';
    public const SUBDOMAIN = 'xhgui.';

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
            $this->progBarAdv(5);
            foreach (self::COMMANDS as $com) {
                $this->execute($com);
                $this->progBarAdv(10);
            }
            $this->execute(self::COMPOSER . ' install -d ' . self::APP_DIR);
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::CHOWN . ' -R vagrant:vagrant ' . self::APP_DIR);
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
            $this->progBarInit(45);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO . ' rm -Rf ' . self::APP_DIR);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            if ($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN . $this->getConfig()->getName())) {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN . $this->getConfig()->getName());
            }
            if ($this->getConfig()->getFpm()->containsKey(self::FPM_IDENTITY)) {
                $listen = explode(':', $this->getConfig()->getFpm()->get(self::FPM_IDENTITY));
                $port = (int)$listen[1];
                $this->getConfig()->getUsedPorts()->removeElement($port);
                $this->getConfig()->getFpm()->remove(self::FPM_IDENTITY);
            }
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite(
            [
                'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
                'type' => 'Xhgui',
                'to' => self::APP_DIR . '/webroot',
                'fpm' => true,
                'zRay' => false,
                'category' => Site::CATEGORY_ADMIN,
                'description' => 'XHGUI Profiler UI'
            ],
            [
                'name' => self::FPM_IDENTITY,
                'user' => 'vagrant',
                'group' => 'vagrant',
                'listen' => '127.0.0.1:%%%PORT%%%',
                'pm' => Fpm::ONDEMAND,
                'maxChildren' => 2,
            ]
        );
    }
}