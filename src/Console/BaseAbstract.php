<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BaseAbstract
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
abstract class BaseAbstract extends ContainerAwareCommand
{
    use LockableTrait;
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
        $parent = get_class($this);
        $class = $parent::SERVICE_CLASS;
        $name = $parent::NAME;
        /** @var ProcessInterface $inst */
        $inst = new $class();
        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setContainer($this->getContainer());
        $inst->setIo(new SymfonyStyle($input, $output));
        if ($input->hasOption('remove') && $input->getOption('remove')===true)
        {
            $output->writeln('<info>Uninstalling '.$name.'</info>');
            $inst->uninstall();
            $output->writeln('<info>Uninstallation completed</info>');
        }
        else
        {
            $output->writeln('<info>Installing '.$name.'</info>');
            $inst->install();
            $inst->configure();
            $output->writeln('<info>Installation completed</info>');
        }
        $inst->getConfig()->writeConfigs();

        $check = $parent.'::ADD_PHP';
        if(defined($check) && $parent::ADD_PHP === true)
        {
            $output->writeln('<comment>PHP Configs need to be refreshed: mjrone:sites:phpfpm</comment>');
        }
        $check = $parent.'::ADD_SITE';
        if(defined($check) && $parent::ADD_SITE === true)
        {
            $output->writeln('<comment>Vagrant needs to be reloaded! vagrant reload (outside of this VM)</comment>');
            $output->writeln('<comment>Webserver Configs need to be refreshed: mjrone:sites:web</comment>');
        }
    }
}