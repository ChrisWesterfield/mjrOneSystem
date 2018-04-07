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
        $port = 9001;
        for($i=9001; $i<20000; $i++)
        {
            if(!$this->getConfig()->getUsedPorts()->contains($i))
            {
                $port = $i;
            }
        }
        if($i>19999)
        {
            $this->getOutput()->writeln('no available Ports left!');
        }
        $this->getConfig()->getUsedPorts()->add($i);
        $fpm = new Fpm(
            [
                'name'=> self::FPM_IDENTITY,
                'user'=>'vagrant',
                'group'=>'vagrant',
                'listen'=>'127.0.0.1:'.$i,
                'pm'=>Fpm::ONDEMAND,
                'maxChildren'=>2,
            ]
        );
        $this->getConfig()->getFpm()->set($fpm->getName(),$fpm);
        $site = new Site(
            [
                'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
                'type'=>'PhpApp',
                'to'=>self::APP_DIR,
                'fpm'=>$fpm->getName(),
                'zRay'=>false,
                'category'=>Site::CATEGORY_ADMIN,
            ]
        );
        $this->getConfig()->getSites()->set($site->getMap(),$site);
    }
}