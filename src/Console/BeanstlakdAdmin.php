<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\BeanstalkedAdmin as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class BeanstlakdAdmin
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class BeanstlakdAdmin extends BaseAbstract
{
    public const NAME = 'BeanstalkdAdmin';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:beanstalkdAdmin')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }

}