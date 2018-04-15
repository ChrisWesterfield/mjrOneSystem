<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\CockroachDb;
use App\Process\CouchDb;
use App\Process\Memcached;
use App\Process\Mongo;
use App\Process\Redis;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartCaches
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartCaches extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:cache')
            ->setHelp('restart cache services')
            ->setDescription('restart cache services')
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
        $output->writeln('<info>Restarting cache Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if(SystemConfig::get()->getFeatures()->contains(Memcached::class))
        {
            $instance = new Memcached();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Redis::class))
        {
            $instance = new Redis();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
    }

}