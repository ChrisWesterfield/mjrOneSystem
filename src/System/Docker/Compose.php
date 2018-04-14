<?php
declare(strict_types=1);

namespace App\System\Docker;

use App\System\Config\ConfigAbstract;
use App\System\Config\ConfigInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Compose
 * @package App\System\Docker
 * @author chris westerfield <chris@mjr.one>
 */
class Compose extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $filePath;
    /**
     * @var string
     */
    protected $version = '2';
    /**
     * @var ArrayCollection|null|Service[]
     */
    protected $services;
    /**
     * @var int|null|string
     */
    protected $networkName;
    /**
     * @var string
     */
    protected $networkDriver;

    /**
     * Compose constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->services = new ArrayCollection();

        if (!empty($config)) {
            foreach ($config as $id => $item) {
                if ($id === 'services') {
                    if (!empty($item)) {
                        foreach ($item as $k => $v) {
                            $item[$k] = new Service($v);
                        }
                    }
                    $item = new ArrayCollection($item);
                }
                $this->{$id} = $item;
            }
        }
        if (array_key_exists('network', $config)) {
            reset($config['network']);
            $firstKey = key($config['network']);
            $this->networkName = $firstKey;
            $this->networkDriver = $config['network'][$firstKey]['driver'];
        }
    }

    /**
     * @return int|null|string
     */
    public function getNetworkName()
    {
        return $this->networkName;
    }

    /**
     * @param int|null|string $networkName
     * @return Compose
     */
    public function setNetworkName($networkName)
    {
        $this->networkName = $networkName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNetworkDriver(): string
    {
        return $this->networkDriver;
    }

    /**
     * @param string $networkDriver
     * @return Compose
     */
    public function setNetworkDriver(string $networkDriver): Compose
    {
        $this->networkDriver = $networkDriver;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return (string)$this->version;
    }

    /**
     * @param string $version
     * @return Compose
     */
    public function setVersion(string $version): Compose
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return ArrayCollection|null|Service[]
     */
    public function getServices(): ?ArrayCollection
    {
        return $this->services;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Compose
     */
    public function setId(string $id): Compose
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return Compose
     */
    public function setFilename(string $filename): Compose
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     * @return Compose
     */
    public function setFilePath(string $filePath): Compose
    {
        $this->filePath = $filePath;
        return $this;
    }
}