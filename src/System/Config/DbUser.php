<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Class DbUser
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class DbUser extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var bool|mixed
     */
    protected $readOnly = false;

    /**
     * DbUser constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->username = $config['username'];
        if(array_key_exists('password',$config))
        {
            $this->password = $config['password'];
        }
        if(array_key_exists('readOnly',$config))
        {
            $this->readOnly = $config['readOnly'];
        }
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return DbUser
     */
    public function setUsername(string $username): DbUser
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return DbUser
     */
    public function setPassword(string $password): DbUser
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param bool|mixed $readOnly
     * @return DbUser
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }
}