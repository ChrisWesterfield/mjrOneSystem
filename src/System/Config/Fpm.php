<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Class Fpm
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class Fpm extends ConfigAbstract implements ConfigInterface
{
    public const PM = [
        self::DYNAMIC,
        self::STATIC,
        self::ONDEMAND,
    ];
    public const DYNAMIC = 'dynamic';
    public const STATIC = 'static';
    public const ONDEMAND = 'ondemand';
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version='7.2';

    /**
     * @var string
     */
    protected $pm='dynamic';

    /**
     * @var int
     */
    protected $maxChildren=16;

    /**
     * @var int
     */
    protected $maxSpare=4;

    /**
     * @var int
     */
    protected $minSpare=2;

    /**
     * @var string
     */
    protected $maxRam='512M';

    /**
     * @var int
     */
    protected $start=4;

    /**
     * @var bool
     */
    protected $xdebug=true;

    /**
     * @var bool
     */
    protected $zRay=false;

    /**
     * @var string
     */
    protected $listen = '127.0.0.1';

    /**
     * @var int
     */
    protected $port=9000;

    /**
     * @var string
     */
    protected $user='vagrant';

    /**
     * @var string
     */
    protected $group='vagrant';

    /**
     * @var string
     */
    protected $processIdleTimeOut = '10s';

    /**
     * @var string
     */
    protected $maxRequests = 200;

    /**
     * @var array
     */
    protected $flags=[];

    /**
     * @var array
     */
    protected $values=[];

    /**
     * @var bool
     */
    protected $displayError=true;

    /**
     * @var bool
     */
    protected $logError=false;

    /**
     * Fpm constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        if(!empty($config))
        {
            foreach($config as $id=>$item)
            {
                $this->{$id} = $item;
            }
        }
        if(!in_array($this->pm,self::PM))
        {
            $this->pm = 'dynamic';
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
     * @return Fpm
     */
    public function setName(string $name): Fpm
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Fpm
     */
    public function setVersion(string $version): Fpm
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getPm(): string
    {
        return $this->pm;
    }

    /**
     * @param string $pm
     * @return Fpm
     */
    public function setPm(string $pm): Fpm
    {
        $this->pm = $pm;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxChildren(): int
    {
        return $this->maxChildren;
    }

    /**
     * @param int $maxChildren
     * @return Fpm
     */
    public function setMaxChildren(int $maxChildren): Fpm
    {
        $this->maxChildren = $maxChildren;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSpare(): int
    {
        return $this->maxSpare;
    }

    /**
     * @param int $maxSpare
     * @return Fpm
     */
    public function setMaxSpare(int $maxSpare): Fpm
    {
        $this->maxSpare = $maxSpare;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinSpare(): int
    {
        return $this->minSpare;
    }

    /**
     * @param int $minSpare
     * @return Fpm
     */
    public function setMinSpare(int $minSpare): Fpm
    {
        $this->minSpare = $minSpare;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxRam(): string
    {
        return $this->maxRam;
    }

    /**
     * @param string $maxRam
     * @return Fpm
     */
    public function setMaxRam(string $maxRam): Fpm
    {
        $this->maxRam = $maxRam;
        return $this;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start
     * @return Fpm
     */
    public function setStart(int $start): Fpm
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return bool
     */
    public function isXdebug(): bool
    {
        return $this->xdebug;
    }

    /**
     * @param bool $xdebug
     * @return Fpm
     */
    public function setXdebug(bool $xdebug): Fpm
    {
        $this->xdebug = $xdebug;
        return $this;
    }

    /**
     * @return bool
     */
    public function isZRay(): bool
    {
        return $this->zRay;
    }

    /**
     * @param bool $zRay
     * @return Fpm
     */
    public function setZRay(bool $zRay): Fpm
    {
        $this->zRay = $zRay;
        return $this;
    }

    /**
     * @return string
     */
    public function getListen(): string
    {
        return $this->listen;
    }

    /**
     * @param string $listen
     * @return Fpm
     */
    public function setListen(string $listen): Fpm
    {
        $this->listen = $listen;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return Fpm
     */
    public function setPort(int $port): Fpm
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return Fpm
     */
    public function setUser(string $user): Fpm
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return Fpm
     */
    public function setGroup(string $group): Fpm
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessIdleTimeOut(): string
    {
        return $this->processIdleTimeOut;
    }

    /**
     * @param string $processIdleTimeOut
     * @return Fpm
     */
    public function setProcessIdleTimeOut(string $processIdleTimeOut): Fpm
    {
        $this->processIdleTimeOut = $processIdleTimeOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRequests(): int
    {
        return $this->maxRequests;
    }

    /**
     * @param int $maxRequests
     * @return Fpm
     */
    public function setMaxRequests(int $maxRequests): Fpm
    {
        $this->maxRequests = $maxRequests;
        return $this;
    }

    /**
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param array $flags
     * @return Fpm
     */
    public function setFlags(array $flags): Fpm
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     * @return Fpm
     */
    public function setValues(array $values): Fpm
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayError(): bool
    {
        return $this->displayError;
    }

    /**
     * @param bool $displayError
     * @return Fpm
     */
    public function setDisplayError(bool $displayError): Fpm
    {
        $this->displayError = $displayError;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLogError(): bool
    {
        return $this->logError;
    }

    /**
     * @param bool $logError
     * @return Fpm
     */
    public function setLogError(bool $logError): Fpm
    {
        $this->logError = $logError;
        return $this;
    }
}