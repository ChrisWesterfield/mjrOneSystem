<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\WebSitesApache;
use App\Process\WebSitesNginx;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SiteConfig extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:configure:sites')
            ->setHelp('generate Website Configs for apache and nginx')
            ->setDescription('generate Website Configs for apache and nginx')
            ->addOption('ignoreApache','a',InputOption::VALUE_NONE)
            ->addOption('ignoreNginx','n', InputOption::VALUE_NONE);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$input->hasOption('ignoreApache'))
        {
            $output->writeln('configuring apache');
            $inst = new WebSitesApache();
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setIo(new SymfonyStyle($input, $output));
            $inst->setContainer($this->getContainer());
            $inst->configure();
            $output->writeln('configuring apache done');
        }
        $inst->getConfig()->writeConfigs();
        if(!$input->hasOption('ignoreNginx'))
        {
            $output->writeln('configuring nginx');
            $inst = new WebSitesNginx();
            $inst->setIo(new SymfonyStyle($input, $output));
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setContainer($this->getContainer());
            $inst->configure();
            $output->writeln('configuring nginx done');
        }
        $inst->getConfig()->writeConfigs();
    }
}