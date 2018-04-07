<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Sqlite as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Sqlite
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Sqlite extends BaseAbstract
{
    public const NAME = 'Sqlite';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:sqlite')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}