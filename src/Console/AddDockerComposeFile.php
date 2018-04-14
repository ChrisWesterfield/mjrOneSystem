<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DockerCompose;
use App\Process\ProcessHelper;
use App\Process\ProcessInterface;
use App\System\Docker\Compose;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DockerComposeFile
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class AddDockerComposeFile extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:add:docker:file')
            ->setHelp('add or remove Composer File')
            ->setDescription('add or remove Composer File')
            ->addArgument('id', InputArgument::REQUIRED, 'Unique Identifier for compose file')
            ->addArgument('file', InputArgument::OPTIONAL, 'File name of file')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to file')
            ->addOption('net_natme', null, InputOption::VALUE_REQUIRED, 'name of network device')
            ->addOption('net_mode', null, InputOption::VALUE_REQUIRED, 'network device mode', 'bridge')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
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
        $process = new ProcessHelper();
        $process->setIo(new SymfonyStyle($input, $output));
        $process->setContainer($this->getContainer());
        $process->setConfig(SystemConfig::get());
        $process->setOutput($output);
        $options = $input->getOptions();
        if($input->hasOption('remove') && $input->getOption('remove'))
        {
            if(SystemConfig::get()->getDockerCompose()->containsKey($input->getArgument('id')))
            {
                /** @var Compose $dc */
                $dc = SystemConfig::get()->getDockerCompose()->get($input->getArgument('id'));
                $file = $dc->getFilePath().$dc->getFilename();
                $path = $dc->getFilePath();
                if(is_file($file))
                {
                    $process->getOutput()->writeln('<info>stoping docker composel file execution and removing it</info>');
                    $process->execute('cd '.$path.' && '.DockerCompose::DOCKER_COMPOSE.' stop ');
                    $process->execute('cd '.$path.' && '.DockerCompose::DOCKER_COMPOSE.' kill ');
                    $process->execute(ProcessInterface::RM.' -f '.$file);
                    SystemConfig::get()->writeConfigs();
                    $process->getOutput()->writeln('done');
                    return 0;
                }
                $process->getOutput()->writeln('<error>file not found</error>');
                return 0;
            }

            $process->getOutput()->writeln('<error>File not found in config</error>');
            return 0;
        }
        $id = $input->getArgument('id');
        $path = $input->getArgument('path');
        $file = $input->getArgument('file');
        $error = false;
        if(empty($path))
        {
            $process->getOutput()->writeln('<error>Path needed</error>');
            $error = true;
        }
        if(!$error && !is_dir($path)){
            $process->getOutput()->writeln('<error>directory doesn\'t exist!</error>');
            $error = true;
        }
        if(!$error && !is_writable($path)){
            $process->getOutput()->writeln('<error>directory is not writale!</error>');
            $error = true;
        }
        if(empty($file))
        {
            $file = 'docker-compose.yml';
        }
        if(SystemConfig::get()->getDockerCompose()->containsKey($id))
        {
            $process->getOutput()->writeln('<error>Docker Compose already exists!</error>');
            $error = true;
        }
        if(!$error && is_writable($path.'/'.$file))
        {
            $process->getOutput()->writeln('<error>Cant write to docker file</error>');
            $error = true;
        }
        if($error)
        {
            return 0;
        }
        $output->writeln('<info>Adding Docker Compose File '.$path.$file.'</info>');
        $cfg = new Compose(
            [
                'id'=>$id,
                'filename'=>$file,
                'filePath'=>$path,
                'version'=>2,
            ]
        );
        SystemConfig::get()->getDockerCompose()->set($id, $cfg);
        SystemConfig::get()->writeConfigs();
        $output->writeln('done');
    }
}