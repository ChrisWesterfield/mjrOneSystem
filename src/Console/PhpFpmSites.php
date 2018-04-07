<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\PhpFpmSites as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class PhpFpmSites
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class PhpFpmSites extends BaseAbstract
{
    public const NAME = 'PhpFpm Config to Sites';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:sites:phpfpm')
            ->setHelp(self::NAME)
            ->setDescription(self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}