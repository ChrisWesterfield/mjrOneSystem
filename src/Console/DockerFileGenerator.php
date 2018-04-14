<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DockerCompose;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DockerFileGenerator extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:generate:docker')
            ->setHelp('generate Docker Compose Files')
            ->setDescription('generate Docker Compose Files');
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
        if(SystemConfig::get()->getFeatures()->contains(DockerCompose::class)) {
            if (SystemConfig::get()->getDockerCompose()->count() > 0) {
                $inst = new DockerCompose();
                $inst->setConfig(SystemConfig::get());
                $inst->setOutput($output);
                $inst->setIo(new SymfonyStyle($input, $output));
                $inst->setContainer($this->getContainer());
                $inst->configureComposerFiles();
                return 0;
            }
            $output->writeln('<error>No Docker Compose Files defined!</error>');
            return 0;
        }
        $output->writeln('<error>Docker Compose not installed!</error>');
    }
}