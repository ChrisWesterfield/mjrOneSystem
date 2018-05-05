<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\MasterSlaveSetup as Process;
use App\System\SystemConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MasterSlaveServer
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class MasterSlaveServer extends BaseAbstract
{
    public const NAME = 'Master Slave Setup';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:masterSlave')
             ->setHelp('install or uninstall '.self::NAME)
             ->setDescription('install or uninstall '.self::NAME)
             ->addOption('slaves', 'z', InputOption::VALUE_REQUIRED, 'Number of Slave Servers', 2)
             ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(SystemConfig::get()->getMasterCount()===1)
        {
            SystemConfig::get()->setSlaveCount((int)$input->getOption('slaves'));
            parent::execute($input, $output);
        }
    }
}