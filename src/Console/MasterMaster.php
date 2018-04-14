<?php
declare(strict_types=1);
namespace App\Console;
use App\System\Docker\Compose;
use App\System\Docker\Service;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class MasterMaster
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class MasterMaster extends ContainerAwareCommand
{
    use LockableTrait;
    public const NAME = 'MasterSlave';

    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:configure:mastermaster')
            ->setHelp('Add MySQL Master Master Config')
            ->setDescription('Add MySQL Master Master Config')
            ->addArgument('file', InputArgument::REQUIRED, 'Docker Compose file (config file id) to add it to (you need to create it first!)')
            ->addArgument('master', InputArgument::OPTIONAL, 'Number of Master Servers')
            ->addArgument('slaves', InputArgument::OPTIONAL, 'Number of slave Servers')
            ->addOption('prefix', 'X', InputOption::VALUE_REQUIRED, 'db Server Prefix (master, slave are added automatically!', 'db')
        ;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getNextPort():int
    {
        for($i=3306; $i<20000; $i++)
        {
            if(!SystemConfig::get()->getUsedPorts()->contains($i))
            {
                SystemConfig::get()->getUsedPorts()->add($i);
                return $i;
            }
        }
        throw new \Exception('No Ports available!');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>Command is locked!</error>');
            return;
        }
        $masters = (int)$input->getArgument('master');
        $masters = ($masters > 0?$masters:1);
        $slaves = (int)$input->getArgument('slaves');
        if(($masters>2 && $slaves > 1) || ($masters > 3 && $slaves < 1))
        {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('You are about to generate an large ammount of MySQL Servers. ('.$masters.' Master Servers and each with '.$slaves.' Servers) This requires an large ammount of RAM! (Master 512MB, Slaves 384MB each) Do you really want to continue?',false);
            if(!$helper->ask($input, $output, $question))
            {
                $output->writeln('exiting');
                return;
            }
            else
            {
                $output->writeln('<comment>you have been warned!</comment>');
            }
        }
        $output->writeln('<info>Adding MySLQ MasterMasterSlave Config</info>');
        $prefix = $input->getOption('prefix');
        $composeId = $input->getArgument('file');
        if (!SystemConfig::get()->getDockerCompose()->containsKey($composeId)) {
            $output->writeln('<error>Docker Compose Files does not exist. Please create it first!</error>');
            return;
        }
        /** @var Compose $dc */
        $dc = SystemConfig::get()->getDockerCompose()->get($composeId);
        if($dc->getServices()->containsKey($prefix.'Master1'))
        {
            $output->writeln('<error>Db Master already exists! Please remove it first!</error>');
            return;
        }
        $config = [
                'name' => '',
                'command'=> [
                    'mysqld',
                    '--innodb-buffer-pool-size=20M',
                    '--log-bin=mysql-bin',
                    '--server-id=1'
                ],
                'environment' => [
                    'MYSQL_ROOT_PASSWORD' => '\${MYSQL_ROOT_PASSWORD}',
                    'MYSQL_DATABASE' => '\${MYSQL_DATABASE}',
                    'MYSQL_USER' => '\${MYSQL_USER}',
                    'MYSQL_PASSWORD' => '\${MYSQL_PASSWORD}',
                    'MYSQL_REPLICA_USER' => '\${MYSQL_REPLICATION_USER}',
                    'MYSQL_REPLICA_PASS' => '\${MYSQL_REPLICATION_PASSWORD}',
                    'MYSQL_MASTER_PORT' => '3306',
                    'MYSQL_MASTER_SERVER'=>'',
                    'MYSQL_MASTER_WAIT_TIME'=>'10s',
                ],
                'build' => false,
                'image' => 'mjrone/mysql:latest',
                'networks' =>$dc->getNetworkName(),
                'ports'=>[
                    3306=>[
                        'local'=>''.$this->getNextPort(),
                        'remote'=>'3306'
                    ],
                ],
                'restart'=>'unless-stopped',
                'volumes'=>[
                    '/home/vagrant/base/log/docker'=>[
                        'local'=>'/home/vagrant/base/log/docker',
                        'remote'=>'/var/log',
                        'mode'=>'rw',
                    ]
                ],
                'memoryLimit'=>'512m'
            ];
        $idC = 1;
        for($m=1; $m<=$masters; $m++)
        {
            $m1 = $m+1;
            if($m===$masters)
            {
                $m1 = 1;
            }
            $config['name'] = $prefix.'Master'.$m;
            $config['environment']['MYSQL_MASTER_SERVER'] = $prefix.'Master'.$m1;
            $config['command'][3] = '--server-id='.$idC;
            $config['memoryLimit'] = '512m';
            $idC++;
            $master = new Service($config);
            $dc->getServices()->set($master->getName(),$master);
            for($s=1;$s<=$slaves;$s++)
            {
                $config['command'][3] = '--server-id='.$m.'00'.$s;
                $config['name'] = $prefix.'SlaveM'.$m.'S'.$s;
                $config['environment']['MYSQL_MASTER_SERVER'] = $master->getName();
                $config['ports'][3306]['local']=''.$this->getNextPort();
                $config['memoryLimit'] = '384m';
                $slave = new Service($config);
                $dc->getServices()->set($slave->getName(), $slave);
            }
        }
        SystemConfig::get()->writeConfigs();
        $output->writeln('done');
        return;
    }
}