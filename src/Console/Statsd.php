<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Statsd as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Statsd
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Statsd extends BaseAbstract
{
    public const NAME = 'Statsd';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:statsd')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}