<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Xdebug as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Xdebug
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Xdebug extends BaseAbstract
{
    public const NAME = 'Xdebug';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:xdebug')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}