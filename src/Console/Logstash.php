<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Logstash as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Logstash
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Logstash extends BaseAbstract
{
    public const NAME = 'Logstash';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:logstash')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}