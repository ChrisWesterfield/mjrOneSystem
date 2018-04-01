<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Mongo as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Mongo
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Mongo extends BaseAbstract
{
    public const NAME = 'MongoDb';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install:mongo')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}