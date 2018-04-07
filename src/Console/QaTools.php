<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\QaTools\CodeCeption;
use App\Process\QaTools\PhpCbf;
use App\Process\QaTools\PhpCpd;
use App\Process\QaTools\PhpCs;
use App\Process\QaTools\PhpDox;
use App\Process\QaTools\PhpLOC;
use App\Process\QaTools\PhpMetrics;
use App\Process\QaTools\PhpUnit;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class QaTools
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class QaTools extends ContainerAwareCommand
{
    use LockableTrait;
    public const SUPPORTED = [
        'phpunit',
        'phploc',
        'phpcs',
        'phpcbf',
        'phpcpd',
        'codecept',
        'phpmetrics',
        'phpdox',
    ];
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:qatools')
            ->setHelp('install or uninstall QaTools')
            ->setDescription('install or uninstall QaTools')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley')
            ->addOption('include',   'i',InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'list of QaTools (One with each option, else All are installed)['.implode(', ',self::SUPPORTED));
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
        $packages = ($input->hasOption('include') && !empty($input->getOption('include')))?$input->getOption('include'):self::SUPPORTED;
        foreach($packages as $package)
        {
            switch (strtolower($package))
            {
                case 'phpunit':
                    $inst = new PhpUnit();
                break;
                case 'phploc':
                    $inst = new PhpLOC();
                break;
                case 'phpcs':
                    $inst = new PhpCs();
                break;
                case 'phpcbf':
                    $inst = new PhpCbf();
                break;
                case 'phpcpd':
                    $inst = new PhpCpd();
                break;
                case 'codecept':
                    $inst = new CodeCeption();
                break;
                case 'phpmetrics':
                    $inst = new PhpMetrics();
                break;
                case 'phpdox':
                    $inst = new PhpDox();
                break;
            }
            $inst->setConfig(SystemConfig::get());
            $inst->setOutput($output);
            $inst->setContainer($this->getContainer());
            $inst->setIo(new SymfonyStyle($input, $output));
            if ($input->hasOption('remove') && $input->getOption('remove')===true)
            {
                $output->writeln('<info>Uninstalling '.$package.'</info>');
                $inst->uninstall();
                $output->writeln('<info>Uninstallation completed</info>');
            }
            else
            {
                $output->writeln('<info>Installing '.$package.'</info>');
                $inst->install();
                $inst->configure();
                $output->writeln('<info>Installation completed</info>');
            }
        }
        $inst->getConfig()->writeConfigs();
    }
}