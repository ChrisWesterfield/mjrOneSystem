<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class DarkStat extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'darkstat'
    ];
    public const VERSION_TAG = 'darkstat';
    public const DEFAULT_PORT = 667;
    public const DEFAULT_CONTENT = '# Turn this to yes when you have configured the options below.
START_DARKSTAT=yes

# Don\'t forget to read the man page.

# You must set this option, else darkstat may not listen to
# the interface you want
INTERFACE=\"-i eth0 -i eth1\"

#DIR=\"/var/lib/darkstat\"
PORT=\"-p '.self::DEFAULT_PORT.'\"
BINDIP=\"-b 127.0.0.1\"
#LOCAL=\"-l 192.168.0.0/255.255.255.0\"

# File will be relative to \$DIR:
DAYLOG=\"--daylog darkstat.log\"

# Don\'t reverse resolve IPs to host names
#DNS=\"--no-dns\"

#FILTER=\"not (src net 192.168.0 and dst net 192.168.0)\"

# Additional command line Arguments:
# OPTIONS=\"--syslog --no-macs\"';
    public const DEFAULT_FILE = '/etc/darkstat/init.cfg';
    /**
     * @return void
     */
    public const SUBDOMAIN = 'darkstat.';

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
            $this->progBarAdv(25);
            $this->execute('echo "'.self::DEFAULT_CONTENT.'" | '.self::SUDO.' '.self::TEE.' '.self::DEFAULT_FILE);
            $this->progBarAdv(5);
            $this->execute(self::ENABLE_SERVICE.' darkstat');
            $this->progBarAdv(5);
            $this->execute(self::SERVICE_CMD.' darkstat'.self::SERVICE_START);
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
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
            $this->progBarInit(25);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            if($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN.$this->getConfig()->getName()))
            {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN.$this->getConfig()->getName());
            }
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite([
            'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
            'type'=>'Proxy',
            'listen'=>'127.0.0.1:'.self::DEFAULT_PORT,
            'category'=>Site::CATEGORY_ADMIN,
            'description'=>'Dark Stat Traffic'
        ]);
    }
}