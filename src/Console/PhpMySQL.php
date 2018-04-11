<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\PhpMysql as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class PhpMySQL
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class PhpMySQL extends BaseAbstract
{
    public const NAME = 'PHP MySQL Extension';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:phpmysql')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}