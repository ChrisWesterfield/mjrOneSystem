<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DarkStat as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Darkstat
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Darkstat extends BaseAbstract
{
    public const NAME = 'Darkstat';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:darkstat')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}