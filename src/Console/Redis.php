<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Redis as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Redis
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Redis extends BaseAbstract
{
    public const NAME = 'Redis';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:redis')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}