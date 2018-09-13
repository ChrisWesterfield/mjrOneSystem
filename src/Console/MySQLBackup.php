<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 31.07.18
 * Time: 14:27
 */

namespace App\Console;


use App\Kernel;
use App\Process\ProcessInterface;
use App\System\Config\Database;
use App\System\Config\DbUser;
use App\System\SystemConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MySQLBackup extends BaseAbstract
{
    public const CMD = '/usr/bin/mysqldump -u%s -p%s -h 127.0.0.1 -P %s %s --skip-lock-tables 2>/dev/null  | gzip > "%s"';

    public function configure()
    {
        $this->setName('mjrone:backup:mysql')
            ->setHelp('create Backup from Database Server')
            ->setDescription('create Backup from MySQL Server and Store it in the Vagrant Backup Directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workDir = ProcessInterface::VAGRANT_HOME.'/backup/';
        if(!is_dir($workDir))
        {
            mkdir($workDir,0775, true);
        }
        if (SystemConfig::get()->getDatabases()->count() > 0)
        {
            $databases = SystemConfig::get()->getDatabases();
            foreach($databases as $id=>$database)
            {
                /** @var Database $database */
                if($database->getType()==='mysql')
                {
                    try{
                        $file = $workDir.$id.'.sql.gz';
                        $dbuser = 'root';
                        $password  = '123';
                        $port = 3306;
                        $database = $database->getName();
                        $cmd = sprintf(self::CMD, $dbuser, $password, $port, $database, $file);
                        $outputLvl = 3;
                        if(defined('OVERRIDE_OUTPUT') && constant('OVERRIDE_OUTPUT')===true)
                        {
                            $outputLvl = 0;
                        }
                        $print = function (string $outputString, int $level) use ($output)
                        {
                            if($level===0 || ($level===1 && $output->isVerbose()) || ($level===2 && $output->isVeryVerbose()) || ($level===3 && $output->isDebug())) {
                                $output->writeln($outputString);
                            }
                        };
                        $process = new Process($cmd);
                        $process->setTimeout(1800);
                        $print($cmd, 2);
                        $process->run(
                            function ($type, $buffer) use ($outputLvl, $print)
                            {
                                $message = rtrim($buffer, "\n");
                                $print($message, $outputLvl);
                            }
                        );
                        $response = $process->getOutput();
                        if(empty($response) && $process->getExitCode()>0)
                        {
                            $response = $process->getErrorOutput();
                            $aResponse = explode("\n",$response);
                            foreach($aResponse as $resp)
                            {
                                $print('<error>'.$resp.'</error>', $outputLvl);
                            }
                        }
                    }catch (\Exception $ex){
                        $output->writeln('<error>'.$ex->getMessage().'</error>');
                    }
                }
            }
        }
        $output->writeln('<info>done</info>');
    }
}