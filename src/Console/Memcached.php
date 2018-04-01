<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Memcached as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Memcached
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Memcached extends BaseAbstract
{
    public const NAME = 'Memcached';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:memcached')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}