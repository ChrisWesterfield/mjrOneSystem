<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Yarn as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Yarn
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Yarn extends BaseAbstract
{
    public const NAME = 'Yarn';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:yarn')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}