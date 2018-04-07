<?php
declare(strict_types=1);
namespace App\Process\Database;


use App\Process\DatabaseInterface;
use App\Process\Mongo as MongoProcess;
use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\System\Config\Database;

class Mongo extends ProcessAbstract implements ProcessInterface, DatabaseInterface
{
    public const DB = 'mongo';
    public const COMMAND_CREATE = MongoProcess::BIN.' %s --eval "db.test.insert({name:\'db creation\'})"';
    public const COMMAND_DELETE = MongoProcess::BIN.' %s --eval "db.dropDatabase()"';
    public const COMMAND_LIST = MongoProcess::BIN.' --eval "db.adminCommand( { listDatabases: 1 } )"';
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
     * @param Database $db
     * @param bool $create
     * @param bool $delete
     */
    public function db(?Database $db,bool $create=false,bool $delete=false):void
    {
        if(!file_exists(ProcessInterface::INSTALLED_APPS_STORE.MongoProcess::VERSION_TAG))
        {
            $this->getOutput()->writeln('<error>No MongoDB Server Installed!</error>');
            return;
        }
        if($create)
        {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Create MongoDB Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::COMMAND_CREATE, $db->getName()));
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
            $this->output->writeln('<info>Delete MongoDB Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::COMMAND_DELETE, $db->getName()));
            $this->getOutput()->writeln('done');
        }
        else
        {
            $result = $this->execute(self::COMMAND_LIST);
            $result = explode("\n",$result);
            foreach($result as $res)
            {
                $this->getOutput()->writeln($res);
            }
        }
    }
}