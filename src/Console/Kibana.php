<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Kibana as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Kibana
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Kibana extends BaseAbstract
{
    public const NAME = 'Kibana';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:kibana')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}