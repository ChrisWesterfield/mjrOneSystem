<?php
declare(strict_types=1);
namespace App\System\Docker;
use App\System\Config\ConfigAbstract;
use App\System\Config\ConfigInterface;

/**
 * Class Volume
 * @package App\System\Config\Docker
 * @author chris westerfield <chris@mjr.one>
 */
class Volume extends ConfigAbstract implements ConfigInterface
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
     * @var string
     */
    protected $mode='';

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
     * @return Volume
     */
    public function setLocal(string $local): Volume
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
     * @return Volume
     */
    public function setRemote(string $remote): Volume
    {
        $this->remote = $remote;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return Volume
     */
    public function setMode(string $mode): Volume
    {
        $this->mode = $mode;
        return $this;
    }
}