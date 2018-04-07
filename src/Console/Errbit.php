<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Errbit as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Errbit
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Errbit extends BaseAbstract
{
    public const NAME = 'Errbit';
    public const SERVICE_CLASS = Process::class;
    public const ADD_SITE = true;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:errbit')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}