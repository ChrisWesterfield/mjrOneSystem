<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Tideways as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Tideways
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Tideways extends BaseAbstract
{
    public const NAME = 'Tideways';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:tideways')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}