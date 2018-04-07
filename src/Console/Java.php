<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Java as Process;
use Symfony\Component\Console\Input\InputOption;
class Java extends BaseAbstract
{
    public const NAME = 'Java';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:java')
            ->setHelp('install or uninstall Java')
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'remove package completley');
    }
}