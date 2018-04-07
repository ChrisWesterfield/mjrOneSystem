<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\ElasticSearch5;
use App\Process\ElasticSearch6;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ElasticSearch
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class ElasticSearch extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:elastic')
            ->setHelp('install or uninstall Elastic Search 5 or 6')
            ->setDescription('install or uninstall Elastic Search 5 or 6')
            ->addOption('elasticVersion', 'i', InputOption::VALUE_REQUIRED, 'Which Version to Install (currently only 5 or 6!)',6)
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
            if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.ElasticSearch5::VERSION_TAG))
            {
                $inst = new ElasticSearch5();
            }
            else
                if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.ElasticSearch6::VERSION_TAG))
            {
                $inst = new ElasticSearch6();
            }
            else
            {
                $output->writeln('<error>No elastic Search Installation found!</error>');
                return 0;
            }
            $output->writeln('<info>Uninstalling ElasticSearch'.($inst instanceof ElasticSearch5?'5':'6').'</info>');
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setContainer($this->getContainer());
            $inst->setIo(new SymfonyStyle($input, $output));
            $inst->uninstall();
            $output->writeln('<info>Uninstallation completed</info>');
            $inst->getConfig()->writeConfigs();
            return 0;
        }
        if(file_exists(ProcessInterface::INSTALLED_APPS_STORE.ElasticSearch5::VERSION_TAG) or file_exists(ProcessInterface::INSTALLED_APPS_STORE.ElasticSearch6::VERSION_TAG))
        {
            $output->writeln('<error>Elastic Search already Exists!. ('.(file_exists(ProcessInterface::INSTALLED_APPS_STORE.ElasticSearch5::VERSION_TAG)?'Elastic Search 5':'Elastic Search 6').')</error>');
            return 0;
        }
        if($input->hasOption('elasticVersion') && (int)$input->getOption('elasticVersion')=== 5 )
        {
            $inst = new ElasticSearch5();
        }
        else
            if(($input->hasOption('elasticVersion') && (int)$input->getOption('elasticVersion')=== 6) || !$input->hasOption('elasticVersion') )
        {
            $inst = new ElasticSearch6();
        }
        else
        {
            $output->writeln('<error>Installer currently only Supports Elastic Search 5 or 6!</error>');
            return 0;
        }
        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setContainer($this->getContainer());
        $inst->setIo(new SymfonyStyle($input, $output));
        $output->writeln('<info>Installing ElasticSearch'.($inst instanceof ElasticSearch5?'5':'6').'</info>');
        $inst->install();
        $inst->configure();
        $output->writeln('<info>Installation completed</info>');
        $inst->getConfig()->writeConfigs();
    }
}