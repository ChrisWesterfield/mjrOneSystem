<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Netdata as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Netdata
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Netdata extends BaseAbstract
{
    public const NAME = 'Netdata';
    public const SERVICE_CLASS = Process::class;
    public const ADD_SITE = true;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:netdata')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}