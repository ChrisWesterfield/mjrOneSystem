<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Blackfire as Process;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ZRay
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Blackfire extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:blackfire')
            ->setHelp('install or uninstall Blackfire')
            ->setDescription('install or uninstall Blackfire')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley')
            ->addOption('reconfigure', 'g', InputOption::VALUE_NONE, 'reconfigure');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
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
        if($input->hasOption('reconfigure') && $input->getOption('reconfigure'))
        {
            $inst->configure();
        }
        else
        {
            if ($input->hasOption('remove') && $input->getOption('remove')===true)
            {
                $output->writeln('<info>Uninstalling Blackfire</info>');
                $inst->uninstall();
                $output->writeln('<info>Uninstallation completed</info>');
            }
            else
            {
                $output->writeln('<info>Installing Blackfire</info>');
                $inst->install();
                $inst->configure();
                $output->writeln('<info>Installation completed</info>');
            }
        }
        $inst->getConfig()->writeConfigs();
    }
}