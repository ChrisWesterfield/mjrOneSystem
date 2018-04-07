<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Composer as Process;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Composer
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Composer extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:composer')
            ->setHelp('install or uninstall Composer')
            ->setDescription('install or uninstall Composer')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'update Composer Package');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->lock())
        {
            $output->writeln('<error>Command is locked!</error>');
            return 0;
        }
        $inst = new Process();
        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setContainer($this->getContainer());
        $inst->setIo(new SymfonyStyle($input, $output));
        if($input->hasOption('update') && $input->getOption('update')===true)
        {
            $inst->uninstall();
            $inst->install();
            $inst->configure();
            $inst->getConfig()->writeConfigs();
        }
        else
        {
            if ($input->hasOption('remove') && $input->getOption('remove')===true)
            {
                $output->writeln('<info>Uninstalling Composer</info>');
                $inst->uninstall();
                $output->writeln('<info>Uninstallation completed</info>');
            }
            else
            {
                $output->writeln('<info>Installing Composer</info>');
                $inst->install();
                $inst->configure();
                $output->writeln('<info>Installation completed</info>');
            }
            $inst->getConfig()->writeConfigs();
        }
    }
}