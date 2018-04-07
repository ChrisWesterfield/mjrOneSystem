<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\PostgreSQL as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class PostgreSQL
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class PostgreSQL extends BaseAbstract
{
    public const NAME = 'PostgreSQL';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:pgsql')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}