<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\MasterSlaveSetup;
use App\Process\MySQL as Process;
use App\Process\Maria;
use App\Process\MySQL56;
use App\Process\MySQL57;
use App\Process\MySQL8;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MySQL
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class MySQL extends ContainerAwareCommand
{
    use LockableTrait;
    public const NAME = 'MySQL';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:mysql')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('mysql','m', InputOption::VALUE_REQUIRED,'set Version to Install (6 for 5.6, 7 for 5.7 or 8 for 8.x (experimental!))',7)
            ->addOption('slaves', 'z', InputOption::VALUE_REQUIRED, 'Number of slave servers', 0)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
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
        if ($input->hasOption('remove') && $input->getOption('remove')===true)
        {
            if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL56::VERSION_TAG))
            {
                $inst = new MySQL56();
                $version = '5.6';
            }
            else
                if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL57::VERSION_TAG))
                {
                    $inst = new MySQL57();
                    $version = '5.7';
                }
            else
                if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL8::VERSION_TAG))
                {
                    $inst = new MySQL8();
                    $version = '8.0';
                }
            else
            {
                $output->writeln('<error>No MySQL Server Installation found!</error>');
                return 0;
            }
            $output->writeln('<info>Uninstalling MySQL '.$version.'</info>');
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setContainer($this->getContainer());
            $inst->setIo(new SymfonyStyle($input, $output));
            $inst->uninstall();
            $output->writeln('<info>Uninstallation completed</info>');
            $inst->getConfig()->writeConfigs();
            return 0;
        }

        if( file_exists(ProcessInterface::INSTALLED_APPS_STORE.Maria::VERSION_TAG) )
        {
            $output->writeln('<error>MariaDB Installation detected!</error>');
            return 0;
        }

        if( file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL56::VERSION_TAG) )
        {
            $output->writeln('<error>MySQL 5.6 Installation detected!</error>');
            return 0;
        }

        if( file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL57::VERSION_TAG) )
        {
            $output->writeln('<error>MySQL 5.7 Installation detected!</error>');
            return 0;
        }

        if( file_exists(ProcessInterface::INSTALLED_APPS_STORE.MySQL8::VERSION_TAG) )
        {
            $output->writeln('<error>MySQL 8.0 Installation detected!</error>');
            return 0;
        }
        if($input->hasOption('mysql') && (int)$input->getOption('mysql')=== 6 )
        {
            $inst = new MySQL56();
            $version = '5.6';
        }
        else
            if(($input->hasOption('mysql') && (int)$input->getOption('mysql')=== 7) || !$input->hasOption('mysql') )
        {
            $inst = new MySQL57();
            $version = '5.7';
        }
        else
            if(($input->hasOption('mysql') && (int)$input->getOption('mysql')=== 8) )
        {
            $inst = new MySQL8();
            $version = '8.0';
        }
        else
        {
            $output->writeln('<error>Installer currently only Supports MySQL Server 5.6 (6), 5.7 (7) or 8.0 (8 === developer Version!)!</error>');
            return 0;
        }

        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setContainer($this->getContainer());
        $inst->setIo(new SymfonyStyle($input, $output));
        $output->writeln('<info>Installing MySQL '.$version.'</info>');
        $inst->install();
        $inst->configure();
        $inst->getConfig()->writeConfigs();
        if($input->hasOption('slaves') && $input->getOption('slaves') && SystemConfig::get()->getMasterCount()===1)
        {
            SystemConfig::get()->setSlaveCount((int)$input->getOption('slaves'));
            $output->writeln('<info>Initializing Master Slave Setup</info>');
            $inst = new MasterSlaveSetup();
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setContainer($this->getContainer());
            $inst->setIo(new SymfonyStyle($input, $output));
            $inst->install();
            $inst->configure();
        }
        $output->writeln('<info>Installation completed</info>');
    }
}