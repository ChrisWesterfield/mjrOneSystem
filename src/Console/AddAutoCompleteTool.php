<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\AutoCompleter as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AddAutoCompleteTool
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class AddAutoCompleteTool extends BaseAbstract
{
    public const NAME = 'AutoCompleter';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:autocompleter')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}