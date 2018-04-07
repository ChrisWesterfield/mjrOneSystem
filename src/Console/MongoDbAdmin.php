<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\MongoDbAdmin as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class MongoDbAdmin
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class MongoDbAdmin extends BaseAbstract
{
    public const NAME = 'MongoDbAdmin';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:mongodbadmin')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}