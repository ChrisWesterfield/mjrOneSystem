<?php
declare(strict_types=1);
namespace App\System;
use App\System\Config\Blackfire;
use App\System\Config\ConfigInterface;
use App\System\Config\Database;
use App\System\Config\Folder;
use App\System\Config\Fpm;
use App\System\Config\Site;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SystemConfig
 * @package App\System
 * @author chris westerfield <chris@mjr.one>
 */
class SystemConfig
{
    protected const CONFIG_FILE=__DIR__.'/../../../etc/config.yaml';

    /**
     * @var SystemConfig
     */
    protected static $instance;

    /**
     * @return SystemConfig
     */
    public static function get():SystemConfig
    {
        if(!self::$instance instanceof SystemConfig)
        {
            self::$instance = new SystemConfig();
        }
        return self::$instance;
    }

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $memory;

    /**
     * @var string
     */
    protected $cpus;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var string
     */
    protected $authorize;

    /**
     * @var array
     */
    protected $folders;

    /**
     * @var array
     */
    protected $databases;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $fpm;

    /**
     * @var array
     */
    protected $sites;

    /**
     * @var array
     */
    protected $features;

    /**
     * @var string Loging Directory
     */
    protected $logDir;

    /**
     * @var string
     */
    protected $systemDir;

    /**
     * @var string
     */
    protected $vagrantDir;

    /**
     * @var ArrayCollection
     */
    protected $requirements;

    /**
     * @var Blackfire
     */
    protected $blackFire;

    /**
     * SystemConfig constructor.
     */
    protected function __construct()
    {
        $config = Yaml::parseFile(self::CONFIG_FILE);
        $this->folders = new ArrayCollection();
        if(array_key_exists('folders',$config))
        {
            if(!empty($config['folders']))
            {
                foreach($config['folders'] as $folder)
                {
                    $this->folders[] = new Folder($folder);
                }
            }
        }
        $this->ip = $config['ip'];
        $this->cpus = $config['cpus'];
        $this->memory = $config['memory'];
        $this->provider = $config['provider'];
        $this->keys = $config['keys'];
        $this->databases = new ArrayCollection();
        $this->name = $config['name'];
        $this->fpm = new ArrayCollection();
        $this->sites = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->authorize = $config['authorize'];
        if(array_key_exists('blackfire', $config))
        {
            $this->blackFire = new Blackfire($config['blackfire']);
        }
        if(array_key_exists('databases',$config))
        {
            if(!empty($config['databases']))
            {
                foreach($config['databases'] as $database)
                {
                    $db = new Database($database);
                    $this->databases->set($db->getName(), $db);
                }
            }
        }
        if(array_key_exists('features', $config))
        {
            if(!empty($config['features']))
            {
                foreach($config['features'] as $feature)
                $this->features->add($feature);
            }
        }
        if(array_key_exists('fpm', $config))
        {
            if(!empty($config['fpm']))
            {
                foreach($config['fpm'] as $fpm)
                {
                    $proc = new Fpm($fpm);
                    $this->fpm->set($proc->getName(),$proc);
                }
            }
        }
        if(array_key_exists('sites', $config))
        {
            if(!empty($config['sites']))
            {
                foreach($config['sites'] as $site)
                {
                    $s = new Site($site);
                    $this->sites->set($s->getMap(),$s);
                }
            }
        }
        foreach($this->folders as $folder)
        {
            /** @var Folder $folder */
            if($folder->getSystemType()==='vagrant')
            {
                $this->vagrantDir = $folder->getTo();
                $this->logDir = $this->vagrantDir.'/logs';
                $this->systemDir = $this->vagrantDir.'/system';
            }
        }
        $this->requirements = new ArrayCollection();
        if(array_key_exists('requirements', $config))
        {
            if(!empty($config['requirements']))
            {
                foreach($config['requirements'] as $name=>$packages)
                {
                    if($packages!==null)
                    {
                        $req = new ArrayCollection($packages);
                        $this->requirements->set($name,$req);
                    }
                }
            }
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getRequirements(): ArrayCollection
    {
        return $this->requirements;
    }

    /**
     * @param $name
     * @param array $requirements
     * @return SystemConfig
     */
    public function addRequirement($name, string $requirement):SystemConfig
    {
        if(!$this->requirements->containsKey($requirement))
        {
            $this->requirements->set($requirement, new ArrayCollection());
        }
        if(!$this->requirements->get($requirement)->contains($name))
        {
            $this->requirements->get($requirement)->add($name);
        }
        return $this;
    }

    /**
     * @param $values
     * @return array
     */
    protected function getValues($values)
    {
        if($values instanceof ArrayCollection)
        {
            $results = [];
            foreach($values as $key=>$value)
            {
                if($value instanceof ConfigInterface || $value instanceof ArrayCollection)
                {
                    $results[] = $value->toArray();
                }
                else
                {
                    $results[] = $value;
                }
            }
            return $results;
        }
        else
            if($values instanceof ConfigInterface)
        {
            return $values->toArray();
        }
        return $values;
    }

    /**
     * @return void
     */
    public function writeConfigs():void
    {
        $config = [];
        $vars = get_object_vars($this);
        foreach($vars as $key=>$value)
        {
            if($key==='requirements')
            {
                $results = [];
                if($value instanceof ArrayCollection && $value->count() > 0)
                {
                    foreach ($value as $k=>$v)
                    {
                        $results[$k] = $this->getValues($v);
                    }
                }
                $config[$key] = $results;
                continue;
            }
            $config[$key] = $this->getValues($value);
        }
        $yaml = Yaml::dump($config,100);
        file_put_contents(self::CONFIG_FILE, $yaml);
    }

    /**
     * @param string $name
     * @return SystemConfig
     */
    public function addFeature(string $name):SystemConfig
    {
        if(!$this->hasFeature($name))
        {
            $this->features->add($name);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFeature(string $name):bool
    {
        return $this->features->contains($name);
    }

    /**
     * @return Blackfire
     */
    public function getBlackFire():?Blackfire
    {
        return $this->blackFire;
    }

    /**
     * @param Blackfire $blackFire
     * @return SystemConfig
     */
    public function setBlackFire(Blackfire $blackFire): SystemConfig
    {
        $this->blackFire = $blackFire;
        return $this;
    }

    /**
     * @param string $name
     * @return SystemConfig
     */
    public function removeFeature(string $name):SystemConfig
    {
        if($this->hasFeature($name))
        {
            $this->features->removeElement($name);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->logDir;
    }

    /**
     * @return string
     */
    public function getSystemDir(): string
    {
        return $this->systemDir;
    }

    /**
     * @return string
     */
    public function getVagrantDir(): string
    {
        return $this->vagrantDir;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return SystemConfig
     */
    public function setIp(string $ip): SystemConfig
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemory(): string
    {
        return $this->memory;
    }

    /**
     * @param string $memory
     * @return SystemConfig
     */
    public function setMemory(string $memory): SystemConfig
    {
        $this->memory = $memory;
        return $this;
    }

    /**
     * @return string
     */
    public function getCpus(): string
    {
        return $this->cpus;
    }

    /**
     * @param string $cpus
     * @return SystemConfig
     */
    public function setCpus(string $cpus): SystemConfig
    {
        $this->cpus = $cpus;
        return $this;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     * @return SystemConfig
     */
    public function setProvider(string $provider): SystemConfig
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorize(): string
    {
        return $this->authorize;
    }

    /**
     * @param string $authorize
     * @return SystemConfig
     */
    public function setAuthorize(string $authorize): SystemConfig
    {
        $this->authorize = $authorize;
        return $this;
    }

    /**
     * @return array
     */
    public function getFolders(): ArrayCollection
    {
        return $this->folders;
    }

    /**
     * @param array $folders
     * @return SystemConfig
     */
    public function setFolders(ArrayCollection $folders): SystemConfig
    {
        $this->folders = $folders;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDatabases(): ArrayCollection
    {
        return $this->databases;
    }

    /**
     * @param ArrayCollection $databases
     * @return SystemConfig
     */
    public function setDatabases(ArrayCollection $databases): SystemConfig
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SystemConfig
     */
    public function setName(string $name): SystemConfig
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFpm(): ArrayCollection
    {
        return $this->fpm;
    }

    /**
     * @param ArrayCollection $fpm
     * @return SystemConfig
     */
    public function setFpm(ArrayCollection $fpm): SystemConfig
    {
        $this->fpm = $fpm;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSites(): ArrayCollection
    {
        return $this->sites;
    }

    /**
     * @param ArrayCollection $sites
     * @return SystemConfig
     */
    public function setSites(ArrayCollection $sites): SystemConfig
    {
        $this->sites = $sites;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFeatures(): ArrayCollection
    {
        return $this->features;
    }

    /**
     * @param array $features
     * @return SystemConfig
     */
    public function setFeatures(ArrayCollection $features): SystemConfig
    {
        $this->features = $features;
        return $this;
    }
}