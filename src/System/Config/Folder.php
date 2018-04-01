<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Class Folder
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class Folder extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $map;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $to;

    protected $systemType;

    /**
     * Folder constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->map = $config['map'];
        $this->to = $config['to'];
        if(array_key_exists('type',$config))
        {
            $this->type = $config['type'];
        }
        $this->systemType = $config['systemType'];
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->map;
    }

    /**
     * @param string $map
     * @return Folder
     */
    public function setMap(string $map): Folder
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Folder
     */
    public function setType(string $type): Folder
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return Folder
     */
    public function setTo(string $to): Folder
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * @param mixed $systemType
     * @return Folder
     */
    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
        return $this;
    }
}