<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\MySQL57 as Process;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MasterSlave extends ContainerAwareCommand
{
    use LockableTrait;
    public const NAME = 'MasterSlave';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:configure:masterslave')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addArgument('slaves',InputArgument::OPTIONAL, 'Number of slave servers')
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
            return;
        }
        $output->writeln('<error>Currently not working!</error>');
        return;
    }
}