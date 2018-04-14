<?php
declare(strict_types=1);
namespace App\System\Docker;
use App\System\Config\ConfigAbstract;
use App\System\Config\ConfigInterface;

/**
 * Class Ports
 * @package App\System\Config\Docker
 * @author chris westerfield <chris@mjr.one>
 */
class Ports extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $local;
    /**
     * @var string
     */
    protected $remote;

    /**
     * @var string|null
     */
    protected $protocol;

    /**
     * Site constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!empty($config)) {
            foreach ($config as $id => $item) {
                $this->{$id} = $item;
            }
        }
    }

    /**
     * @return string
     */
    public function getLocal(): string
    {
        return $this->local;
    }

    /**
     * @param string $local
     * @return Ports
     */
    public function setLocal(string $local): Ports
    {
        $this->local = $local;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }

    /**
     * @param string $remote
     * @return Ports
     */
    public function setRemote(string $remote): Ports
    {
        $this->remote = $remote;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    /**
     * @param null|string $protocol
     * @return Ports
     */
    public function setProtocol(?string $protocol): Ports
    {
        $this->protocol = $protocol;
        return $this;
    }
}