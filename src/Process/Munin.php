<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

/**
 * Class Munin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Munin extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'munin',
        'munin-plugins-extra',
    ];
    public const MUNIN_CONFIG_FILE = '/etc/munin/munin.conf';
    public const SUBDOMAIN = 'munin.';
    public const HOME = self::VAGRANT_USER_DIR.'/munin';
    public const MUNIN_CONFIG_CONTENT = 'dbdir  /var/lib/munin
htmldir '.self::HOME.'
logdir /var/log/munin
includedir /etc/munin/munin-conf.d
graph_strategy cron
[dev.test]
    address 127.0.0.1
    use_node_name yes
';
    public const VERSION_TAG = 'munin';
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
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            $this->execute('echo "'.self::MUNIN_CONFIG_CONTENT.'" | '.self::SUDO.' '.self::TEE.' '.self::MUNIN_CONFIG_FILE);
            $this->progBarAdv(5);
            $this->execute('mkdir /home/vagrant/munin');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::CHOWN.' munin:munin /home/vagrant/munin');
            $this->progBarAdv(5);
            $this->execute(self::SERVICE_CMD.' munin-node '.self::SERVICE_RESTART);
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
            $this->execute(self::SUDO.' rm -Rf /home/vagrant/munin');
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
        $site = new Site(
            [
                'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
                'type'=>'Html',
                'to'=>self::HOME,
                'category'=>Site::CATEGORY_STATISTICS,
            ]
        );
        $this->getConfig()->getSites()->set($site->getMap(),$site);
    }
}