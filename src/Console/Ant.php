<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Ant as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Ant
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Ant extends BaseAbstract
{
    public const NAME = 'Ant';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:ant')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
             ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}