<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\WpCli as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Composer
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class WpCli extends BaseAbstract
{
    public const NAME = 'WpCli';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:wpcli')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'update Composer Package');
    }

}