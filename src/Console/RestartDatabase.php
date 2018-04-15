<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Maria;
use App\Process\MySQL56;
use App\Process\MySQL57;
use App\Process\MySQL8;
use App\Process\PostgreSQL;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartWeb
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartDatabase extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:database')
            ->setHelp('restart database services')
            ->setDescription('restart database services')
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
        $output->writeln('<info>Restarting Web Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if(SystemConfig::get()->getFeatures()->contains(Maria::class))
        {
            $instance = new Maria();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(MySQL56::class))
        {
            $instance = new MySQL56();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(MySQL57::class))
        {
            $instance = new MySQL57();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(MySQL8::class))
        {
            $instance = new MySQL8();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(PostgreSQL::class))
        {
            $instance = new PostgreSQL();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
    }

}