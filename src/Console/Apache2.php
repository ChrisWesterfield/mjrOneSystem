<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Apache2 as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Apache2
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Apache2 extends BaseAbstract
{
    public const NAME = 'Apache2';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:apache2')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}