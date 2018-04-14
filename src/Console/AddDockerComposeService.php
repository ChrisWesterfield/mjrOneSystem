<?php
declare(strict_types=1);
namespace App\Console;
use App\System\Docker\Ports;
use App\System\Docker\Service;
use App\System\Docker\Volume;
use App\System\Docker\Compose;
use App\System\SystemConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddDockerComposeService
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class AddDockerComposeService extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:add:docker:service')
            ->setHelp('add or remove Composer Service')
            ->setDescription('add or remove Composer Service')
            ->addArgument('composeId', InputArgument::REQUIRED, 'Identifier of Docker Compose File')
            ->addArgument('name', InputArgument::REQUIRED, 'Unique Name of Service within file')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley')
            ->addOption('command', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Command for Launching')
            ->addOption('environment', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Environmental Variables Example: "ENV_VARIABLE:VALUE"')
            ->addOption('buildContext', null, InputOption::VALUE_REQUIRED, 'Build Context')
            ->addOption('buildDockerFile', null, InputOption::VALUE_REQUIRED, 'Docker Build File')
            ->addOption('image', null, InputOption::VALUE_REQUIRED, 'Docker Image (overwrites build Informations during Docker Compose Writing)')
            ->addOption('networks', null, InputOption::VALUE_REQUIRED, 'Network Device (if black the one defined in docker compose will be used (if defined!))')
            ->addOption('ports', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Ports internal to external "LOKAL_PORT:REMOTE_PORT:PROTOKOL" (Protokol is Optional)')
            ->addOption('restart', null, InputOption::VALUE_REQUIRED, 'Restart Options')
            ->addOption('volumes', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Volumes to Mount "LOKALPATH:REMOTEPATH:OPTIONS" Options is Optional')
            ->addOption('depends', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Depending Services (need to already exist!)')
            ->addOption('links', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Linked Services (need to already exist!)')
            ->addOption('memoryLimit', null, InputOption::VALUE_REQUIRED, 'Memory Limit (empty default, "512MB" for Ram of Container')
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
        $options = $input->getOptions();
        unset(
            $options['no-ansi'], $options['ansi'],
            $options['version'], $options['verbose'],
            $options['quiet'], $options['help'],
            $options['remove'], $options['no-interaction'],
            $options['env'], $options['no-debug'],
            $options['volumes'], $options['ports'],
            $options['depends'], $options['links'],
            $options['environment']
        );
        $options['name'] = $input->getArgument('name');
        $composeId = $input->getArgument('composeId');
        $name = $input->getArgument('name');
        $error = false;
        if(!SystemConfig::get()->getDockerCompose()->containsKey($composeId))
        {
            $output->writeln('<error>Docker Compose File does not exist!</error>');
            $error = true;
        }
        if(!$error)
        {
            /** @var Compose $cfg */
            $cfg = SystemConfig::get()->getDockerCompose()->get($composeId);
            if($cfg->getServices()->contains($name))
            {
                $output->writeln('<error>Service already exists!</error>');
                $error = true;
            }
        }
        if($error)
        {
            return 0;
        }
        $cfgO = new Service($options);
        $output->writeln('<info>Adding new Service for Docker Compose File '.$composeId.'</info>');
        if(!empty($input->getOption('links')))
        {
            foreach($input->getOption('links') as $in)
            {
                if(!$cfg->getServices()->containsKey($in))
                {
                    $output->writeln('<error>Service '.$in.' does not exist, please define it first!</error>');
                    return 0;
                }
                $cfgO->getLinks()->add($in);
            }
        }
        if(!empty($input->getOption('depends')))
        {
            foreach($input->getOption('depends') as $in)
            {
                if(!$cfg->getServices()->containsKey($in))
                {
                    $output->writeln('<error>Service '.$in.' does not exist, please define it first!</error>');
                    return 0;
                }
                $cfgO->getDepends()->add($in);
            }
        }
        if(!empty($input->getOption('environment')))
        {
            foreach($input->getOption('environment') as $in)
            {
                $inA = explode(':', $in);
                $env = $value = null;
                if(array_key_exists(0,$inA))
                {
                    $env = trim($inA[0], ' ');
                }
                if(array_key_exists(1, $inA))
                {
                    $value = trim($inA[1], ' ');
                }
                if($env===null || $value===null)
                {
                    $output->writeln('<error>An Environmental Variable needs to contain an key and value!</error>');
                    return 0;
                }
                if($cfgO->getEnvironment()->containsKey($env))
                {
                    $output->writeln('<error>Environment Variable '.$env.' Already exists!!</error>');
                    return 0;
                }
                $cfgO->getEnvironment()->set($env, $value);
            }
        }
        if(!empty($input->getOption('volumes')))
        {
            foreach($input->getOption('volumes') as $in)
            {
                $inA = explode(':',$in);
                $inO = new Volume(
                    [
                        'local'=>$inA[0],
                        'remote'=>$inA[1],
                    ]
                );
                if(isset($inA[2]))
                {
                    $inO->setMode($inA[2]);
                }
                if(!$cfgO->getVolumes()->containsKey($inO->getRemote()))
                {
                    $cfgO->getVolumes()->set($inO->getRemote(), $inO);
                }
            }
        }
        if(!empty($input->getOption('ports')))
        {
            foreach($input->getOption('ports') as $in)
            {
                $inA = explode(':',$in);
                $inO = new Ports(
                    [
                        'local'=>$inA[0],
                        'remote'=>$inA[1],
                    ]
                );
                if(!SystemConfig::get()->getUsedPorts()->contains((int)$inO->getLocal()))
                {
                    SystemConfig::get()->getUsedPorts()->add((int)$inO->getLocal());
                }
                if(isset($inA[2]))
                {
                    $inO->setProtocol($inA[2]);
                }
                if(!$cfgO->getPorts()->containsKey($inO->getRemote()))
                {
                    $cfgO->getPorts()->set($inO->getRemote(), $inO);
                }
            }
        }
        SystemConfig::get()->getDockerCompose()->get($composeId)->getServices()->set($cfgO->getName(), $cfgO);
        SystemConfig::get()->writeConfigs();
        $output->writeln('done');
    }
}