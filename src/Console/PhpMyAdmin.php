<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\PhpMyAdmin as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class PhpMyAdmin
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class PhpMyAdmin extends BaseAbstract
{
    public const NAME = 'PhpMyAdmin';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:phppma')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}