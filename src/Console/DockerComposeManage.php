<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DockerCompose;
use App\Process\ProcessHelper;
use App\System\Docker\Compose;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DockerComposeManage extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:dockerCompose')
            ->setHelp('Docker Compose Command Wrapper')
            ->setDescription('Docker Compose Command Wrapper')
            ->addArgument('name', InputArgument::REQUIRED, 'File Identifier (from the config.yaml file)')
            ->addOption('build', 'B', InputOption::VALUE_NONE, 'Build Docker Environment')
            ->addOption('bundle', 'E', InputOption::VALUE_NONE, 'Build Bundle from Docker Compose File')
            ->addOption('down', 'D', InputOption::VALUE_NONE, 'Stop Docker Environment and Remove Containers')
            ->addOption('kill', 'K', InputOption::VALUE_NONE, 'Kill Docker Containers')
            //@todo ->addOption('port', 'P', InputOption::VALUE_NONE, 'Print Ports')
            ->addOption('pull', 'L', InputOption::VALUE_NONE, 'Pull all Service Images')
            ->addOption('restart', 'R', InputOption::VALUE_NONE, 'Restart all Services')
            ->addOption('rm', 'M', InputOption::VALUE_NONE, 'Remove all Services')
            ->addOption('stop', 'O', InputOption::VALUE_NONE, 'Stop all Services')
            ->addOption('start', 'S', InputOption::VALUE_NONE, 'Start all Services')
            ->addOption('ps', 'Z', InputOption::VALUE_NONE, 'Display Process List')
            ->addOption('up', 'U', InputOption::VALUE_NONE, 'Bring Containers Up and Start them')
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
        $compId = $input->getArgument('name');
        if(!SystemConfig::get()->getDockerCompose()->containsKey($compId))
        {
            $output->writeln('<error>Docker Compose File not Found in Config</error>');
            return 0;
        }
        /** @var Compose $dc */
        $dc = SystemConfig::get()->getDockerCompose()->get($compId);
        $file = $dc->getFilePath().'/'.$dc->getFilename();
        if(!file_exists($file))
        {
            $output->writeln('<error>Docker Compose File was not Created in Filesystem! Use system mjrone:generate:docker to generate all files!</error>');
            return 0;
        }
        $inst = new ProcessHelper();
        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setIo(new SymfonyStyle($input, $output));
        $inst->setContainer($this->getContainer());
        $cmdPattern = DockerCompose::SUDO.' '.DockerCompose::DOCKER_COMPOSE.' -f '.$file.' %s ';
        define('OVERRIDE_OUTPUT', true);
        if($input->hasOption('build') && $input->getOption('build')===true)
        {
            $output->writeln('<info>Build</info>');
            $inst->execute(sprintf($cmdPattern, 'build'));
        }
        if($input->hasOption('stop') && $input->getOption('stop')===true)
        {
            $output->writeln('<info>Stop</info>');
            $inst->execute(sprintf($cmdPattern, 'stop'));
        }
        if($input->hasOption('kill') && $input->getOption('kill')===true)
        {
            $output->writeln('<info>Kill</info>');
            $inst->execute(sprintf($cmdPattern, 'kill'));
        }
        if($input->hasOption('rm') && $input->getOption('rm')===true)
        {
            $output->writeln('<info>Removing all Containers and Attached Volumes</info>');
            $inst->execute(sprintf($cmdPattern, 'rm --force -v'));
        }
        if($input->hasOption('down') && $input->getOption('down')===true)
        {
            $output->writeln('<info>Down</info>');
            $inst->execute(sprintf($cmdPattern, 'down'));
        }
        if($input->hasOption('pull') && $input->getOption('pull')===true)
        {
            $output->writeln('<info>Pull</info>');
            $inst->execute(sprintf($cmdPattern, 'pull'));
        }
        if($input->hasOption('up') && $input->getOption('up')===true)
        {
            $output->writeln('<info>Bringing Containers Up</info>');
            $inst->execute(sprintf($cmdPattern, 'up -d'));
            $inst->execute(sprintf($cmdPattern, 'ps'));
        }
        if($input->hasOption('start') && $input->getOption('start')===true)
        {
            $output->writeln('<info>Start</info>');
            $inst->execute(sprintf($cmdPattern, 'start'));
        }
        if($input->hasOption('restart') && $input->getOption('restart')===true)
        {
            $inst->execute(sprintf($cmdPattern, 'restart'));
        }
        if($input->hasOption('ps') && $input->getOption('ps')===true)
        {
            $inst->execute(sprintf($cmdPattern, 'ps'));
        }
    }
}