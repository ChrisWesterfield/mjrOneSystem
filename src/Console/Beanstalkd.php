<?php
declare(strict_types=1);

namespace App\Console;

use App\Process\Beanstalked as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Beanstalkd
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Beanstalkd extends BaseAbstract
{
    public const NAME = 'BeanstalkD';
    public const SERVICE_CLASS = Process::class;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:beanstalkd')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'remove package completley');
    }
}