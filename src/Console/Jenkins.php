<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Jenkins as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Jenkins
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Jenkins extends BaseAbstract
{
    public const NAME = 'Jenkins';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:jenkins')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }

}