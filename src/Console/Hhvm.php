<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Hhvm as Process;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Hhvm
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Hhvm extends BaseAbstract
{
    public const NAME = 'Hhvm';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:hhvm')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}