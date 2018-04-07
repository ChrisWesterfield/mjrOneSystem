<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 02.04.18
 * Time: 00:20
 */

namespace App\Console;


use App\Process\Database\CouchDb;
use App\Process\Database\Mongo;
use App\Process\Database\MySQL;
use App\Process\Database\PostgreSQL;
use App\System\Config\Database;
use App\System\Config\DbUser;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseManager extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:database')
            ->setHelp('manage database')
            ->setDescription('manage database')
            ->addOption('operation','o',InputOption::VALUE_REQUIRED,'Operation to be executed (c=create, d=drop, l=list === default)','l')
            ->addArgument('type', InputArgument::REQUIRED,'Database type, supported: mysql, pgsql, couchdb, mongodb (name === input)')
            ->addArgument('database', InputArgument::OPTIONAL, 'Database to be added')
            ->addArgument('username', InputArgument::IS_ARRAY,'Add User to Database (only for mysql and pgsql schema: "<username>,<password>,<type>" type is only supported by mysql (type === read or write) always include < and > for each of the fields! suround the complete username with ")')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>Command is locked!</error>');
            return 0;
        }
        $database = $input->hasArgument('database')?$input->getArgument('database'):null;
        $type = $input->getArgument('type');
        $operation = $input->getOption('operation');
        if($database!==null)
        {
            $userNames = $input->getArgument('username');
            $object = new Database(['type'=>$type, 'name'=>$database]);
            if(is_array($userNames) && !empty($userNames))
            {
                foreach($userNames as $userName)
                {
                    $uName = explode('>,<',$userName);
                    $userArray = ['username'=>str_replace(['"<','<'],['',''],$uName[0])];
                    if(isset($uName[1]))
                    {
                        $userArray['password']=str_replace(['"<','<','>"'],['','',''],$uName[1]);
                    }
                    if(isset($uName[2]))
                    {
                        if(strtolower(str_replace(['"<','<','>"','>'],['','','',''],$uName[2]))==='read')
                        {
                            $userArray['readOnly']=true;
                        }
                    }
                    $user = new DbUser($userArray);
                    $object->getUserList()->add($user);
                }
            }
        }
        switch (strtolower($type))
        {
            case 'mysql':
            case 'maria':
            case 'mariadb':
                $instance = new MySQL();
            break;
            case 'pgsql':
            case 'postgresql':
            case 'psql':
                $instance = new PostgreSQL();
            break;
            case 'mongo':
            case 'mongodb':
            case 'mong':
                $instance = new Mongo();
            break;
            case 'couch':
            case 'couchdb':
                $instance = new CouchDb();
            break;
            default:
                $output->writeln('<error>only mysql, pgsql, mongo, couchdb are supported!<</error>');
                return 0;
            break;
        }
        $instance->setOutput($output);
        $instance->setConfig(SystemConfig::get());
        $instance->setContainer($this->getContainer());
        $instance->setIo(new SymfonyStyle($input, $output));
        switch (strtolower($operation))
        {
            case 'create':
            case 'c':
                if($this->checkDbExists($object))
                {
                    $output->writeln('<error>Database with this name already exist in config.yaml!</error>');
                    return 0;
                }
                $instance->db($object,true,false);
                SystemConfig::get()->getDatabases()->add($object);
                SystemConfig::get()->writeConfigs();
            break;
            case 'delete':
            case 'remove':
            case 'd':
            case 'r':
                if(!$this->checkDbExists($object))
                {
                    $output->writeln('<error>Database with this name does not exist in config.yaml!</error>');
                    return 0;
                }
                $instance->db($object,false,true);
                foreach(SystemConfig::get()->getDatabases() as $id=>$item)
                {
                    /** @var Database $item */
                    if($item->getName()===$object->getName() && $item->getType() === $object->getType())
                    {
                        SystemConfig::get()->getDatabases()->remove($id);
                        break(1);
                    }
                }
                SystemConfig::get()->writeConfigs();
            break;
            default:
                $instance->db(null,false,false);
            break;
        }
    }

    /**
     * @param Database $config
     * @return bool
     */
    protected function checkDbExists(Database $config):bool
    {
        /** @var Database[] $databases */
        $databases = SystemConfig::get()->getDatabases();
        if(!empty($databases) && $databases->count() > 0)
        {
            foreach($databases as $database)
            {
                if($database->getName()===$config->getName() && $database->getType() === $config->getType())
                {
                    return true;
                }
            }
        }
        return false;
    }
}