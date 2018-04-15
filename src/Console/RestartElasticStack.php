<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\ElasticSearch5;
use App\Process\ElasticSearch6;
use App\Process\Kibana;
use App\Process\Logstash;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartElasticStack
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartElasticStack extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:elasticStack')
            ->setHelp('restart elasticStack services')
            ->setDescription('restart elasticStack services')
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
        $output->writeln('<info>Restarting elasticStack Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if(SystemConfig::get()->getFeatures()->contains(ElasticSearch6::class))
        {
            $instance = new ElasticSearch6();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(ElasticSearch5::class))
        {
            $instance = new ElasticSearch5();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Kibana::class))
        {
            $instance = new Kibana();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Logstash::class))
        {
            $instance = new Logstash();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
    }

}