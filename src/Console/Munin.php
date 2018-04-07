<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Munin as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Munin
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Munin extends BaseAbstract
{
    public const NAME = 'Munin';
    public const SERVICE_CLASS = Process::class;
    public const ADD_SITE = true;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:munin')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}