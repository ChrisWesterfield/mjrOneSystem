<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 01.04.18
 * Time: 23:00
 */

namespace App\Process\Database;


use App\Process\CouchDb as CouchDbBase;
use App\Process\DatabaseInterface;
use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\System\Config\Database;

/**
 * Class CouchDb
 * @package App\Process\Database
 * @author Chris Westerfield <chris@mjr.one>
 */
class CouchDb extends ProcessAbstract implements ProcessInterface, DatabaseInterface
{
    public const DB = 'couchdb';
    public const COMMAND = self::CURL.' -sX %s http://127.0.0.1:5984/%s';
    public const COMMAND_LIST = self::CURL.' -sX GET http://127.0.0.1:5984/_all_dbs';
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
        if(!file_exists(ProcessInterface::INSTALLED_APPS_STORE. CouchDbBase::VERSION_TAG))
        {
            $this->getOutput()->writeln('<error>No CouchDB Server Installed!</error>');
            return;
        }
        if($create)
        {
            if($db===false)
            {
                $this->getOutput()->writeln('<error>No DB Object given!</error>');
                return;
            }
            $this->getOutput()->writeln('<info>Create CouchDB Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::COMMAND, 'PUT', $db->getName()));
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
            $this->output->writeln('<info>Delete CouchDB Database '.$db->getName().'</info>');
            $this->execute(sprintf(self::COMMAND, 'DELETE', $db->getName()));
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