<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Ruby as Process;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Ruby
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Ruby extends BaseAbstract
{
    public const NAME = 'Ruby';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:ruby')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}