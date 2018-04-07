<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\XhGui as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class XhGui
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class XhGui extends BaseAbstract
{
    public const NAME = 'XhGui';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:xhgui')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}