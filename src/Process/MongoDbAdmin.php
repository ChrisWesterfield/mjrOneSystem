<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class MongoDbAdmin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class MongoDbAdmin extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Php72::class,
        MongoDbPhp::class,
        Mongo::class,
    ];
    public const DESCRIPTION = 'Mongo DB Admin Ui';
    public const SOFTWARE = [];
    public const APP_DIR = '/home/vagrant/phppma';
    public const COMMAND = self::GIT_CLONE.' https://github.com/jwage/php-mongodb-admin.git '.self::APP_DIR;
    public const VERSION_TAG = 'mongoadmin';
    /**
     * @return void
     */
    const FPM_IDENTITY = 'admin.mongodb';

    public const SUBDOMAIN = 'mda.';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(55);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::COMMAND);
            $this->progBarAdv(25);
            $this->createLink(self::APP_DIR.'/mongodbadmin.php', self::APP_DIR.'/index.php');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::CHOWN.' -R vagrant:vagrant '.self::APP_DIR);
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
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(45);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' rm -Rf '.self::APP_DIR);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
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
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite(
            [
                'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
                'type'=>'PhpApp',
                'to'=>self::APP_DIR,
                'fpm'=>true,
                'zRay'=>false,
                'category'=>Site::CATEGORY_ADMIN,
                'description'=>'Mongo DB Admin'
            ],
            [
                'name'=> self::FPM_IDENTITY,
                'user'=>'vagrant',
                'group'=>'vagrant',
                'listen'=>'127.0.0.1:%%%PORT%%%',
                'pm'=>Fpm::ONDEMAND,
                'maxChildren'=>2,
            ]
        );
    }
}