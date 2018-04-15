<?php
declare(strict_types=1);

namespace App\Console;

use App\Process\Docker;
use App\Process\DockerPortainer;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RestartDocker
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartDocker extends ContainerAwareCommand
{
    use LockableTrait;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:service:docker')
            ->setHelp('restart docker services')
            ->addOption('ui', 'u', InputOption::VALUE_NONE, 'Docker UI (Portainer) only!')
            ->setDescription('restart web services');
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
        $output->writeln('<info>Restarting docker Servers</info>');
        $style = new SymfonyStyle($input, $output);
        if (SystemConfig::get()->getFeatures()->contains(Docker::class)) {
            if (SystemConfig::get()->getFeatures()->contains(DockerPortainer::class) && $input->hasOption('ui') && $input->getOption('ui') === true) {
                $instance = new DockerPortainer();
                $instance->setIo($style);
                $instance->setContainer($this->getContainer());
                $instance->setConfig(SystemConfig::get());
                $instance->setOutput($output);
                $instance->stop();
                $instance->start();
            } else {
                $instance = new Docker();
                $instance->setIo($style);
                $instance->setContainer($this->getContainer());
                $instance->setConfig(SystemConfig::get());
                $instance->setOutput($output);
                $instance->restartService();
                if (SystemConfig::get()->getFeatures()->contains(DockerPortainer::class)) {
                    $instance = new DockerPortainer();
                    $instance->setIo($style);
                    $instance->setContainer($this->getContainer());
                    $instance->setConfig(SystemConfig::get());
                    $instance->setOutput($output);
                    $instance->stop();
                    $instance->start();
                }
            }
        }
        $output->writeln('done');
    }

}