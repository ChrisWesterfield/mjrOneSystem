<?php
declare(strict_types=1);
namespace App\System\Config;


/**
 * Class Blackfire
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class Blackfire extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $serverId;

    /**
     * @var string
     */
    protected $serverToken;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientToken;

    public function __construct(array $config)
    {
        if(!empty($config))
        {
            foreach($config as $key=>$value)
            {
                if(property_exists(self::class, $key))
                {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getServerId(): string
    {
        return $this->serverId;
    }

    /**
     * @param string $serverId
     * @return Blackfire
     */
    public function setServerId(string $serverId): Blackfire
    {
        $this->serverId = $serverId;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerToken(): string
    {
        return $this->serverToken;
    }

    /**
     * @param string $serverToken
     * @return Blackfire
     */
    public function setServerToken(string $serverToken): Blackfire
    {
        $this->serverToken = $serverToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return Blackfire
     */
    public function setClientId(string $clientId): Blackfire
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    /**
     * @param string $clientToken
     * @return Blackfire
     */
    public function setClientToken(string $clientToken): Blackfire
    {
        $this->clientToken = $clientToken;
        return $this;
    }
}