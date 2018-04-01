<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Docker as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Docker
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Docker extends BaseAbstract
{
    public const NAME = 'Docker';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:docker')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}