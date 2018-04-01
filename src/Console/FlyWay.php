<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\FlyWay as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class FlyWay
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class FlyWay extends BaseAbstract
{
    public const NAME = 'FlyWay';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:flyway')
            ->setHelp('install or uninstall FlyWay')
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}