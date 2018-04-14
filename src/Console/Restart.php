<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Php56;
use App\Process\Php70;
use App\Process\Php71;
use App\Process\Php72;
use App\Process\ProcessAbstract;
use App\Process\ProcessHelper;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Ant
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Restart extends ContainerAwareCommand
{
    use LockableTrait;
    public const SERVICES = [
        'php56'=>Php56::SERVICE_NAME,
        'php70'=>Php70::SERVICE_NAME,
        'php71'=>Php71::SERVICE_NAME,
        'php72'=>Php72::SERVICE_NAME,
        'hhvm'=>'hhvm',
        'nginx'=>'nginx',
        'apache2'=>'apache2',
        'rabbitmq'=>'rabbitmq',
        'beanstalkd'=>'beanstalkd',
        'mailhog'=>'mailhog',
        'elastic'=>'elasticsearch',
        'kibana'=>'kibana',
        'logstash'=>'logstash',
        'errbit'=>'errbit',
        'docker'=>'docker',
        'darkstat'=>'darkstat',
        'netdata'=>'netdata',
        'memcache'=>'memcached',
        'redis'=>'redis-server',
        'postgres'=>'postgresql',
        'supervisor'=>'supervisor',
        'mongo'=>'mongod'
    ];
    public const IGNORE = [
        'version',
        'quiet',
        'help',
        'ansi',
        'no-ansi',
        'no-interaction',
        'env',
        'no-debug',
        'verbose'
    ];
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:restart')
            ->setHelp('Restart Services')
            ->setDescription('Restart Services')
            ->addOption('php56','A',InputOption::VALUE_NONE, 'Php FPM 5.6 (if available)')
            ->addOption('php70','B',InputOption::VALUE_NONE, 'Php FPM 5.6 (if available)')
            ->addOption('php71','C',InputOption::VALUE_NONE, 'Php FPM 5.6 (if available)')
            ->addOption('php72','D',InputOption::VALUE_NONE, 'Php FPM 5.6 (if available)')
            ->addOption('nginx', 'E', InputOption::VALUE_NONE, 'Restart Nginx (if available)')
            ->addOption('rabbitmq', 'F', InputOption::VALUE_NONE, 'Restart RabbitMQ(if available)')
            ->addOption('beanstalkd', 'G', InputOption::VALUE_NONE, 'Restart Beanstalkd (if available)')
            ->addOption('mailhog', 'H', InputOption::VALUE_NONE, 'Restart mailhog Components(if available)')
            ->addOption('elastic', 'I', InputOption::VALUE_NONE, 'Restart Elastic Search(if available)')
            ->addOption('kibana', 'J', InputOption::VALUE_NONE, 'Restart Kibana (if available)')
            ->addOption('logstash', 'K', InputOption::VALUE_NONE, 'Restart Logstash(if available)')
            ->addOption('errbit', 'L', InputOption::VALUE_NONE, 'Restart Errbit(if available)')
            ->addOption('hhvm', 'M', InputOption::VALUE_NONE, 'Restart Facebook HHVM (if available)')
            ->addOption('docker', 'N', InputOption::VALUE_NONE, 'Restart Docker(if available)')
            ->addOption('mongo', 'O', InputOption::VALUE_NONE, 'Restart MongoDB(if available)')
            ->addOption('darkstat', 'P', InputOption::VALUE_NONE, 'Restart Darkstat(if available)')
            ->addOption('netdata', 'Q', InputOption::VALUE_NONE, 'Restart netdata(if available)')
            ->addOption('memcache', 'R', InputOption::VALUE_NONE, 'Restart Memcache Server(if available)')
            ->addOption('redis', 'S', InputOption::VALUE_NONE, 'Restart Redis Server(if available)')
            ->addOption('postgres', 'T', InputOption::VALUE_NONE, 'Restart Postgresql(if available)')
            ->addOption('supervisor', 'U', InputOption::VALUE_NONE, 'Restart SupervisorD(if available)');
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
        $process = new ProcessHelper();
        $process->setIo(new SymfonyStyle($input, $output));
        $process->setContainer($this->getContainer());
        $process->setConfig(SystemConfig::get());
        $process->setOutput($output);
        $options = $input->getOptions();
        $all = true;
        foreach($options as $val=>$option)
        {
            if(!in_array($val,self::IGNORE) && $option===true)
            {
                $all=false;
            }
        }
        if($all)
        {
            foreach(self::SERVICES as $id=>$service)
            {
                if(is_array($service))
                {
                    foreach($service as $i=>$v)
                    {
                        $output->writeln('Restarting '.$i);
                        $process->execute(ProcessInterface::SERVICE_CMD.' '.$v.' '.ProcessInterface::SERVICE_RESTART);
                    }
                }
                else
                {
                    $output->writeln('Restarting '.$id);
                    $process->execute(ProcessInterface::SERVICE_CMD.' '.$service.' '.ProcessInterface::SERVICE_RESTART);
                }
            }
            return 0;
        }
        if($input->hasOption('darkstat') && $input->getOption('darkstat')===true)
        {
            $output->writeln('Restarting Darkstat');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['darkstat'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('php56') && $input->getOption('php56')===true)
        {
            $output->writeln('Restarting Php-FPM 5.6');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['php56'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('php70') && $input->getOption('php70')===true)
        {
            $output->writeln('Restarting Php-FPM 7.0');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['php70'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('php71') && $input->getOption('php71')===true)
        {
            $output->writeln('Restarting Php-FPM 7.1');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['php71'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('php72') && $input->getOption('php72')===true)
        {
            $output->writeln('Restarting Php-FPM 7.2');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['php72'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('nginx') && $input->getOption('nginx')===true)
        {
            $output->writeln('Restarting Nginx');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['nginx'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('beanstalkd') && $input->getOption('beanstalkd')===true)
        {
            $output->writeln('Restarting BeanstalkD');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['beanstalkd'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('rabbitmq') && $input->getOption('rabbitmq')===true)
        {$output->writeln('Restarting RabbitMQ');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['rabbitmq'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('mailhog') && $input->getOption('mailhog')===true)
        {
            $output->writeln('Restarting Mail MailHog');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['mailhog'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('elastic') && $input->getOption('elastic')===true)
        {
            $output->writeln('Restarting kibana');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['elastic'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('kibana') && $input->getOption('kibana')===true)
        {
            $output->writeln('Restarting kibana');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['kibana'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('logstash') && $input->getOption('logstash')===true)
        {
            $output->writeln('Restarting logstash');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['logstash'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('errbit') && $input->getOption('errbit')===true)
        {
            $output->writeln('Restarting errbit');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['errbit'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('hhvm') && $input->getOption('hhvm')===true)
        {
            $output->writeln('Restarting hhvm');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['hhvm'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('docker') && $input->getOption('docker')===true)
        {
            $output->writeln('Restarting docker');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['docker'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('mongo') && $input->getOption('mongo')===true)
        {
            $output->writeln('Restarting mongo');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['mongo'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('netdata') && $input->getOption('netdata')===true)
        {
            $output->writeln('Restarting netdata');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['netdata'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('memcache') && $input->getOption('memcache')===true)
        {
            $output->writeln('Restarting memcached');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['memcache'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('redis') && $input->getOption('redis')===true)
        {
            $output->writeln('Restarting redis');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['redis'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('postgres') && $input->getOption('postgres')===true)
        {
            $output->writeln('Restarting postgres');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['postgres'].' '.ProcessInterface::SERVICE_RESTART);
        }
        if($input->hasOption('supervisor') && $input->getOption('supervisor')===true)
        {
            $output->writeln('Restarting supervisor');
            $process->execute(ProcessInterface::SERVICE_CMD.' '.self::SERVICES['supervisor'].' '.ProcessInterface::SERVICE_RESTART);
        }
    }
}