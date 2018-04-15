<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Apache2;
use App\Process\Nginx;
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
class RestartWeb extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:web')
            ->setHelp('restart web services')
            ->setDescription('restart web services')
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
        $style = new SymfonyStyle($input, $output);
        $output->writeln('<info>Restarting Web Servers</info>');
        if(SystemConfig::get()->getFeatures()->contains(Nginx::class))
        {
            $instance = new Nginx();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        if(SystemConfig::get()->getFeatures()->contains(Apache2::class))
        {
            $instance = new Apache2();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
    }

}