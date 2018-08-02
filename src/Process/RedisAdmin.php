<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class RedisAdmin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class RedisAdmin extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE     = [
    ];
    public const REQUIREMENTS = [
        Redis::class,
        Php72::class,
    ];
    public const VERSION_TAG  = 'rda';
    public const DESCRIPTION  = 'Redis Admin';
    public const APP_DIR      = self::VAGRANT_USER_DIR.'/redisAdmin';
    public const SUBDOMAIN    = 'rda.';
    public const FPM_IDENTITY = 'rda';

    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(20);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::GIT_CLONE.' https://github.com/ErikDubbelboer/phpRedisAdmin.git '.self::APP_DIR);
            $this->progBarAdv(20);
            $this->execute(self::GIT_CLONE.' https://github.com/nrk/predis.git '.self::APP_DIR.'/vendor');
            $this->progBarAdv(20);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    public function uninstall():void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(70);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(20);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::RM.' -Rf '.self::APP_DIR);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarAdv(5);
            $this->removeWeb(self::SUBDOMAIN);
            $this->progBarAdv(5);
            $this->removeFpm(self::FPM_IDENTITY);
            $this->progBarFin();
        }
    }

    public function configure():void
    {
        $this->addSite(
        [
            'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
            'type' => 'PhpApp',
            'to' => self::APP_DIR,
            'fpm' => true,
            'zRay' => false,
            'category' => Site::CATEGORY_ADMIN,
            'description' => 'Php Redis Admin'
        ],
        [
            'name' => self::FPM_IDENTITY,
            'user' => 'vagrant',
            'group' => 'vagrant',
            'listen' => '127.0.0.1:%%%PORT%%%',
            'pm' => Fpm::ONDEMAND,
            'maxChildren' => 2,
            'xdebug'=>false,
        ]
    );
    }
}