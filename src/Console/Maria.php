<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Maria as Process;
use App\Process\MySQL8;
use App\Process\MySQL57;
use App\Process\MySQL56;
use App\Process\ProcessInterface;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Maria
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Maria extends ContainerAwareCommand
{
    use LockableTrait;
    public const NAME = 'Maria';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:maria')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
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
        if(
            file_exists(MySQL8::INSTALLED_APPS_STORE.MySQL8::VERSION_TAG)
            ||
            file_exists(MySQL57::INSTALLED_APPS_STORE.MySQL57::VERSION_TAG)
            ||
            file_exists(MySQL56::INSTALLED_APPS_STORE.MySQL56::VERSION_TAG)
        )
        {
            $output->writeln('<error>MySQL is already installed. Please uninstall it to Install the MariaDB Server!</error>');
            return 0;
        }
        $parent = get_class($this);
        $class = $parent::SERVICE_CLASS;
        $name = $parent::NAME;
        /** @var ProcessInterface $inst */
        $inst = new $class();
        $inst->setConfig(SystemConfig::get());
        $inst->setOutput($output);
        $inst->setContainer($this->getContainer());
        $inst->setIo(new SymfonyStyle($input, $output));
        if ($input->hasOption('remove') && $input->getOption('remove')===true)
        {
            $output->writeln('<info>Uninstalling '.$name.'</info>');
            $inst->uninstall();
            $output->writeln('<info>Uninstallation completed</info>');
        }
        else
        {
            $output->writeln('<info>Installing '.$name.'</info>');
            $inst->install();
            $inst->configure();
            $output->writeln('<info>Installation completed</info>');
        }
        $inst->getConfig()->writeConfigs();
    }
}