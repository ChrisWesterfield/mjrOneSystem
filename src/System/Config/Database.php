<?php
declare(strict_types=1);
namespace App\System\Config;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
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
        $this->userList = new ArrayCollection();
        if(array_key_exists('userList',$config))
        {
            if(!empty($config['userList']))
            {
                foreach($config['userList'] as $user)
                {
                    $this->userList->add(new DbUser($user));
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
     * @return ArrayCollection
     */
    public function getUserList(): ArrayCollection
    {
        return $this->userList;
    }

    /**
     * @param ArrayCollection $userList
     * @return Database
     */
    public function setUserList(ArrayCollection $userList): Database
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