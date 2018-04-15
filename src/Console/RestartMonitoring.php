<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DarkStat;
use App\Process\Errbit;
use App\Process\MailHog;
use App\Process\Munin;
use App\Process\Netdata;
use App\Process\Statsd;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartMonitoring
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartMonitoring extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:monitoring')
            ->setHelp('restart monitoring services')
            ->setDescription('restart monitoring services')
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
        $output->writeln('<info>Restarting monitoring Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if(SystemConfig::get()->getFeatures()->contains(Munin::class))
        {
            $instance = new Munin();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Netdata::class))
        {
            $instance = new Netdata();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(DarkStat::class))
        {
            $instance = new DarkStat();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Statsd::class))
        {
            $instance = new Statsd();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
        
    }

}