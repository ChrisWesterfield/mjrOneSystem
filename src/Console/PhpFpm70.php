<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Php70 as Process;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class PhpFpm70
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class PhpFpm70 extends BaseAbstract
{
    public const NAME = 'PHP Fpm 7.0';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:php70')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}