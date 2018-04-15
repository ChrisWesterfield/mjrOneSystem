<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Errbit;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartErrbit
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartErrbit extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:errbit')
            ->setHelp('restart errbit services')
            ->setDescription('restart errbit services')
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
        $output->writeln('<info>Restarting errbit Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if(SystemConfig::get()->getFeatures()->contains(Errbit::class))
        {
            $instance = new Errbit();
            $instance->setIo($style);
            $instance->setContainer($this->getContainer());
            $instance->setConfig(SystemConfig::get());
            $instance->setOutput($output);
            $instance->restartService();
        }
        $output->writeln('done');
        
    }

}