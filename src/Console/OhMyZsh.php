<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\OhMyZsh as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class OhMyZsh
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class OhMyZsh extends BaseAbstract
{
    public const NAME = 'OhMyZsh';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:ohmyzsh')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}