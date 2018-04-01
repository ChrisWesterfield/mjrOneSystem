<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Class Database
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class Database extends ConfigAbstract implements ConfigInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $userList;
    /**
     * @var string
     */
    protected $type;

    /**
     * Database constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->name = $config['name'];
        if(array_key_exists('type', $config))
        {
            $this->type = $config['type'];
        }
        $this->userList = [];
        if(array_key_exists('users',$config))
        {
            if(empty($config['users']))
            {
                foreach($config['users'] as $user)
                {
                    $this->userList[] = new DbUser($user);
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
     * @return Database
     */
    public function setName(string $name): Database
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserList(): array
    {
        return $this->userList;
    }

    /**
     * @param array $userList
     * @return Database
     */
    public function setUserList(array $userList): Database
    {
        $this->userList = $userList;
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
     * @return Database
     */
    public function setType(string $type): Database
    {
        $this->type = $type;
        return $this;
    }

}