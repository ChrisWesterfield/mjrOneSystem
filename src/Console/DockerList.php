<?php
declare(strict_types=1);
namespace App\Console;
use App\System\Docker\Compose;
use App\System\SystemConfig;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class DockerList
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class DockerList extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:docker:list')
            ->setHelp('list one or complete docker compose config')
            ->setDescription('list one or complete docker compose config')
            ->addArgument('compose', InputArgument::OPTIONAL, 'Display only Config of docker compose file')
            ->addArgument('service', InputArgument::OPTIONAL, 'Displays only Configuration of Service from certain docker compose file (compose is required!)')
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
        $compose = $input->getArgument('compose');
        $service = $input->getArgument('service');
        if(empty($compose) && empty($service))
        {
            $output->writeln('<info>Printing Complete Argument List!</info>');
            $o = var_export(SystemConfig::get()->getDockerCompose(),true);
            $output->writeln($o);
        }
        else if(empty($service) && !empty($compose))
        {
            $output->writeln('<info>Printing Complete Argument List for Docker Compose File'.$compose.'!</info>');
            if(!SystemConfig::get()->getDockerCompose()->containsKey($compose))
            {
                $output->writeln('<error>No Composer file with this name!</error>');
                return 0;
            }
            $o = var_export(SystemConfig::get()->getDockerCompose()->get($compose),true);
            $output->writeln($o);
        }
        else if(!empty($service) && !empty($compose))
        {
            $output->writeln('<info>Printing Complete Argument List for Docker Compose File'.$compose.'!</info>');
            if(!SystemConfig::get()->getDockerCompose()->containsKey($compose))
            {
                $output->writeln('<error>No Composer file with this name!</error>');
                return 0;
            }
            /** @var Compose $comp */
            $comp = SystemConfig::get()->getDockerCompose()->get($compose);
            if(!$comp->getServices()->containsKey($service))
            {
                $output->writeln('<error>Docker Composer file '.$compose.' does not contain an service called'.$service.'!</error>');
                return 0;
            }
            $o = var_export($comp->getServices()->get($service),true);
            $output->writeln($o);
        }
        else
        {
            $output->writeln('<error>Can\'t display required data. You need to either add the compose or compose and service argument!</error>');
        }
    }
}