<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 01.04.18
 * Time: 23:21
 */

namespace App\Process\Database;


use App\Process\DatabaseInterface;
use App\Process\Maria;
use App\Process\MySQL56;
use App\Process\MySQL57;
use App\Process\MySQL8;
use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\System\Config\Database;
use App\System\Config\DbUser;

class MySQL extends ProcessAbstract implements ProcessInterface, DatabaseInterface
{
    public const COMMAND = 'mysql -u %s -p%s -h%s -P%s -e "';
    public const SQL_CREATE_USER = self::COMMAND.' CREATE USER IF NOT EXISTS \'%s\'@\'%s\' IDENTIFIED BY \'%s\'"';
    public const SQL_CREATE_DATABASE = self::COMMAND.' CREATE DATABASE %s;"';
    public const SQL_PRIVILEGE_READ = self::COMMAND.' GRANT SELECT ON %s.* TO \'%s\'@\'%s\';"';
    public const SQL_PRIVILEGE_WRITE = self::COMMAND.' GRANT ALL PRIVILEGES ON %s.* to \'%s\'@\'%s\';"';
    public const SQL_PRIVILEGE_FLUSH = self::COMMAND.' FLUSH PRIVILEGES;"';
    public const SQL_DROP_DATABASE = self::COMMAND.' DROP DATABASE %s;"';
    public const SQL_LIST_DATABASES = self::COMMAND.' SHOW DATABASES;"';
    /**
     * @return void
     */
    public function install(): void{}

    /**
     *
     */
    public function uninstall(): void{}

    /**
     * @return mixed
     */
    public function configure(): void{}

    /**
     * @param string $db
     * @param bool $create
     * @param bool $delete
     */
    public function db(?Database $db, bool $create = false, bool $delete = false): void
    {
        if(
            !file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL56::VERSION_TAG)
            &&
            !file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL57::VERSION_TAG)
            &&
            !file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL8::VERSION_TAG)
            &&
            !file_exists(ProcessInterface::INSTALLED_APPS_STORE.Maria::VERSION_TAG)
        )
        {
            $this->getOutput()->writeln('<error>No MySQL Server Installed!</error>');
            return;
        }
        if($create) {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Create MySQL Database '.$db->getName().'</info>');
            foreach ($db->getUserList() as $user)
            {
                /** @var DbUser $user */
                $this->execute(sprintf(self::SQL_CREATE_USER,'root','123','127.0.0.1','3306',$user->getUsername(),'%',$user->getPassword()));
                $this->execute(sprintf(self::SQL_CREATE_DATABASE,'root','123','127.0.0.1','3306', $db->getName()));
                if($user->getReadOnly())
                {
                    $this->execute(sprintf(self::SQL_PRIVILEGE_READ,'root','123','127.0.0.1','3306',$db->getName(),$user->getUsername(),'%'));
                }
                else
                {
                    $this->execute(sprintf(self::SQL_PRIVILEGE_WRITE,'root','123','127.0.0.1','3306',$db->getName(),$user->getUsername(),'%'));
                }
                $this->execute(sprintf(self::SQL_PRIVILEGE_FLUSH,'root','123','127.0.0.1','3306'));
                $this->getOutput()->writeln('done');
            }
        }
        else
            if($delete)
        {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Delete MySQL Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::SQL_DROP_DATABASE,'root','123','127.0.0.1','3306', $db->getName()));
            $this->getOutput()->writeln('done');
        }
        else
        {
            $result = $this->execute(sprintf(self::SQL_LIST_DATABASES,'root','123','127.0.0.1','3306'));
            $result = explode("\n",$result);
            foreach($result as $res)
            {
                $this->getOutput()->writeln($res);
            }
        }
    }
}