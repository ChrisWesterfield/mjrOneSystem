<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 01.04.18
 * Time: 23:21
 */

namespace App\Process\Database;


use App\Process\DatabaseInterface;
use App\Process\PostgreSQL as PostgreSQLBase;
use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\System\Config\Database;
use App\System\Config\DbUser;

class PostgreSQL extends ProcessAbstract implements ProcessInterface, DatabaseInterface
{
    public const DB_COMMAND = self::SUDO.' -u postgres ';
    public const COMMAND_CREATE_USER = self::DB_COMMAND.' createuser %s';
    public const COMMAND_CREATE_DATABASE = self::DB_COMMAND.' createdb %s';
    public const PGSQL_ALTER_PASSWORD = self::DB_COMMAND.' '.PostgreSQLBase::BIN_PSQL.' -c "ALTER USER %s WITH ENCRYPTED PASSWORD \'%s\'"';
    public const PGSQL_GRANT_ALL = self::DB_COMMAND.' '.PostgreSQLBase::BIN_PSQL.' -c "GRANT ALL PRIVILEGES ON DATABASE %s TO %s;"';
    public const PGSQL_DROP_DATABASE = self::DB_COMMAND.' '.PostgreSQLBase::BIN_PSQL.' -c "DROP DATABASE %s"';
    public const PGSQL_LIST_DATABASES = self::DB_COMMAND.' '.PostgreSQLBase::BIN_PSQL.' -l';

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
            !file_exists(ProcessInterface::INSTALLED_APPS_STORE.PostgreSQLBase::VERSION_TAG)
        )
        {
            $this->getOutput()->writeln('<error>No PostgreSQL Server Installed!</error>');
            return;
        }
        if($create)
        {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Create PostgreSQL Database '.$db->getName().'</info>');
            $first = null;
            foreach($db->getUserList() as $user)
            {
                /** @var DbUser $user */
                $username = $user->getUsername();
                $password = null;
                if($user->getPassword()!==null)
                {
                    $password = $user->getPassword();
                }
                if($first!==null)
                {
                    $first = $username;
                }
                $this->execute(sprintf(self::COMMAND_CREATE_USER,$username));
                if($password!==null)
                {
                    $this->execute(sprintf(self::PGSQL_ALTER_PASSWORD,$username, $password));
                }
            }
            $this->execute(sprintf(self::COMMAND_CREATE_DATABASE,$db->getName()));
            $this->execute(sprintf(self::PGSQL_GRANT_ALL,$db->getName(), $first));
            $this->getOutput()->writeln('done');
        }
        else
            if($delete)
        {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Delete PostgreSQL Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::PGSQL_DROP_DATABASE,$db->getName()));
            $this->getOutput()->writeln('done');
        }
        else
        {
            $result = $this->execute(self::PGSQL_LIST_DATABASES);
            $result = explode("\n",$result);
            foreach($result as $res)
            {
                $this->getOutput()->writeln($res);
            }
        }
    }
}