<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Zray as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class ZRay
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class ZRay extends BaseAbstract
{
    public const NAME = 'Zray';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:zray')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}