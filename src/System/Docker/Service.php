<?php
declare(strict_types=1);
namespace App\System\Docker;
use App\System\Config\ConfigAbstract;
use App\System\Config\ConfigInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Service
 * @package App\System\Config\Docker
 * @author chris westerfield <chris@mjr.one>
 */
class Service extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array|null
     */
    protected $command;

    /**
     * @var ArrayCollection|null
     */
    protected $environment;

    /**
     * @var bool
     */
    protected $build=false;

    /**
     * @var string|null
     */
    protected $buildContext;

    /**
     * @var string|null
     */
    protected $buildDockerFile;

    /**
     * @var string|null
     */
    protected $image;

    /**
     * @var array|null
     */
    protected $networks;

    /**
     * @var ArrayCollection|null|Ports[]
     */
    protected $ports;

    /**
     * @var string|null
     */
    protected $restart;

    /**
     * @var ArrayCollection|null|Volume[]
     */
    protected $volumes;

    /**
     * @var ArrayCollection|null|Service[]
     */
    protected $depends;

    /**
     * @var ArrayCollection|null|Service[]
     */
    protected $links;

    /**
     * @var string|null
     */
    protected $memoryLimit;

    /**
     * Site constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->ports = new ArrayCollection();
        $this->volumes = new ArrayCollection();
        $this->depends = new ArrayCollection();
        $this->links = new ArrayCollection();
        $this->environment = new ArrayCollection();
        if (!empty($config)) {
            foreach ($config as $id => $item) {
                switch ($id)
                {
                    case 'ports':
                        if(count($item) > 0)
                        {
                            foreach($item as $value)
                            {
                                $sItem = new Ports($value);
                                $this->ports->set($sItem->getRemote(), $sItem);
                            }
                        }
                    break;
                    case 'volumes':
                        if(count($item) > 0)
                        {
                            foreach($item as $value)
                            {
                                $sItem = new Volume($value);
                                $this->volumes->set($sItem->getRemote(), $sItem);
                            }
                        }
                    break;
                    default:
                        $this->{$id} = $item;
                    break;
                    case 'depends':
                        if(count($item) > 0)
                        {
                            foreach($item as $value)
                            {
                                $this->depends->add($value);
                            }
                        }
                    break;
                    case 'links':
                        if(count($item) > 0)
                        {
                            foreach($item as $value)
                            {
                                $this->links->add($value);
                            }
                        }
                    break;
                    case 'environment':
                        if(count($item) > 0)
                        {
                            foreach($item as $key=>$value)
                            {
                                $this->environment->set($key,$value);
                            }
                        }
                    break;
                }
            }
        }
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
     * @return Service
     */
    public function setName(string $name): Service
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCommand(): ?array
    {
        return $this->command;
    }

    /**
     * @param array|null $command
     * @return Service
     */
    public function setCommand(?array $command): Service
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getEnvironment(): ?ArrayCollection
    {
        return $this->environment;
    }

    /**
     * @param ArrayCollection|null $environment
     * @return Service
     */
    public function setEnvironment(?ArrayCollection $environment): Service
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBuild(): bool
    {
        return $this->build;
    }

    /**
     * @param bool $build
     * @return Service
     */
    public function setBuild(bool $build): Service
    {
        $this->build = $build;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBuildContext(): ?string
    {
        return $this->buildContext;
    }

    /**
     * @param null|string $buildContext
     * @return Service
     */
    public function setBuildContext(?string $buildContext): Service
    {
        $this->buildContext = $buildContext;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBuildDockerFile(): ?string
    {
        return $this->buildDockerFile;
    }

    /**
     * @param null|string $buildDockerFile
     * @return Service
     */
    public function setBuildDockerFile(?string $buildDockerFile): Service
    {
        $this->buildDockerFile = $buildDockerFile;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     * @return Service
     */
    public function setImage(?string $image): Service
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getNetworks(): ?array
    {
        return $this->networks;
    }

    /**
     * @param array|null $networks
     * @return Service
     */
    public function setNetworks(?array $networks): Service
    {
        $this->networks = $networks;
        return $this;
    }

    /**
     * @return ArrayCollection|null|Ports[]
     */
    public function getPorts(): ?ArrayCollection
    {
        return $this->ports;
    }

    /**
     * @return null|string
     */
    public function getRestart(): ?string
    {
        return $this->restart;
    }

    /**
     * @param null|string $restart
     * @return Service
     */
    public function setRestart(?string $restart): Service
    {
        $this->restart = $restart;
        return $this;
    }

    /**
     * @return ArrayCollection|null|Volume[]
     */
    public function getVolumes(): ?ArrayCollection
    {
        return $this->volumes;
    }

    /**
     * @return ArrayCollection|null|Service[]
     */
    public function getDepends(): ?ArrayCollection
    {
        return $this->depends;
    }

    /**
     * @return ArrayCollection|null|Service[]
     */
    public function getLinks(): ?ArrayCollection
    {
        return $this->links;
    }

    /**
     * @return null|string
     */
    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    /**
     * @param null|string $memoryLimit
     * @return Service
     */
    public function setMemoryLimit(?string $memoryLimit): Service
    {
        $this->memoryLimit = $memoryLimit;
        return $this;
    }
}