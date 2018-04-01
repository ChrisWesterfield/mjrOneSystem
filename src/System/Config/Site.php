<?php
declare(strict_types=1);
namespace App\System\Config;
use App\Process\Sites\SiteBaseAbstract;

/**
 * Class Site
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class Site extends ConfigAbstract implements ConfigInterface
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
    protected $description;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $fpm;

    /**
     * @var int|null
     */
    protected $http=null;

    /**
     * @var int|null
     */
    protected $https=443;

    /**
     * @var string
     */
    protected $charSet='uft-8';

    /**
     * @var array
     */
    protected $fcgiParams=[];

    /**
     * @var bool
     */
    protected $zRay=false;

    /**
     * @var int|null
     */
    protected $clientMaxBodySize;

    /**
     * @var int
     */
    protected $proxyApp=81;

    /**
     * @var string|null
     */
    protected $fcgiBufferSize;

    /**
     * @var string|null
     */
    protected $fcgiBuffer;

    /**
     * @var int|null
     */
    protected $fcgiConnectionTimeOut;

    /**
     * @var int|null
     */
    protected $fcgiSendTimeOut;

    /**
     * @var int|null
     */
    protected $fcgiReadTimeOut;

    /**
     * @var string|null
     */
    protected $fcgiBusyBufferSize;

    /**
     * Site constructor.
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
    }

    /**
     * @return null|string
     */
    public function getFcgiBusyBufferSize(): ?string
    {
        return $this->fcgiBusyBufferSize;
    }

    /**
     * @param null|string $fcgiBusyBufferSize
     * @return Site
     */
    public function setFcgiBusyBufferSize(?string $fcgiBusyBufferSize): Site
    {
        $this->fcgiBusyBufferSize = $fcgiBusyBufferSize;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFcgiBufferSize(): ?string
    {
        return $this->fcgiBufferSize;
    }

    /**
     * @param null|string $fcgiBufferSize
     * @return Site
     */
    public function setFcgiBufferSize(?string $fcgiBufferSize): Site
    {
        $this->fcgiBufferSize = $fcgiBufferSize;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFcgiBuffer(): ?string
    {
        return $this->fcgiBuffer;
    }

    /**
     * @param null|string $fcgiBuffer
     * @return Site
     */
    public function setFcgiBuffer(?string $fcgiBuffer): Site
    {
        $this->fcgiBuffer = $fcgiBuffer;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFcgiConnectionTimeOut(): ?int
    {
        return $this->fcgiConnectionTimeOut;
    }

    /**
     * @param int|null $fcgiConnectionTimeOut
     * @return Site
     */
    public function setFcgiConnectionTimeOut(?int $fcgiConnectionTimeOut): Site
    {
        $this->fcgiConnectionTimeOut = $fcgiConnectionTimeOut;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFcgiSendTimeOut(): ?int
    {
        return $this->fcgiSendTimeOut;
    }

    /**
     * @param int|null $fcgiSendTimeOut
     * @return Site
     */
    public function setFcgiSendTimeOut(?int $fcgiSendTimeOut): Site
    {
        $this->fcgiSendTimeOut = $fcgiSendTimeOut;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFcgiReadTimeOut(): ?int
    {
        return $this->fcgiReadTimeOut;
    }

    /**
     * @param int|null $fcgiReadTimeOut
     * @return Site
     */
    public function setFcgiReadTimeOut(?int $fcgiReadTimeOut): Site
    {
        $this->fcgiReadTimeOut = $fcgiReadTimeOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getProxyApp(): int
    {
        return $this->proxyApp;
    }

    /**
     * @param int $proxyApp
     * @return Site
     */
    public function setProxyApp(int $proxyApp): Site
    {
        $this->proxyApp = $proxyApp;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getClientMaxBodySize(): ?int
    {
        return $this->clientMaxBodySize;
    }

    /**
     * @param int|null $clientMaxBodySize
     * @return Site
     */
    public function setClientMaxBodySize(?int $clientMaxBodySize): Site
    {
        $this->clientMaxBodySize = $clientMaxBodySize;
        return $this;
    }

    /**
     * @return bool
     */
    public function isZray():bool
    {
        return $this->zRay;
    }

    /**
     * @param bool $zray
     * @return Site
     */
    public function setZray(bool $zray):Site
    {
        $this->zRay = $zray;
        return $this;
    }


    /**
     * @return array
     */
    public function getFcgiParams(): array
    {
        return $this->fcgiParams;
    }

    /**
     * @param array $fcgiParams
     * @return Site
     */
    public function setFcgiParams(array $fcgiParams): Site
    {
        $this->fcgiParams = $fcgiParams;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharSet(): string
    {
        return $this->charSet;
    }

    /**
     * @param string $charSet
     * @return Site
     */
    public function setCharSet(string $charSet): Site
    {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHttp(): ?int
    {
        return $this->http;
    }

    /**
     * @param int|null $http
     * @return Site
     */
    public function setHttp(?int $http): Site
    {
        $this->http = $http;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHttps(): ?int
    {
        return $this->https;
    }

    /**
     * @param int|null $https
     * @return Site
     */
    public function setHttps(?int $https): Site
    {
        $this->https = $https;
        return $this;
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
     * @return Site
     */
    public function setMap(string $map): Site
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
     * @return Site
     */
    public function setType(string $type): Site
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Site
     */
    public function setDescription(string $description): Site
    {
        $this->description = $description;
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
     * @return Site
     */
    public function setTo(string $to): Site
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getFpm(): string
    {
        return $this->fpm;
    }

    /**
     * @param string $fpm
     * @return Site
     */
    public function setFpm(string $fpm): Site
    {
        $this->fpm = $fpm;
        return $this;
    }
}