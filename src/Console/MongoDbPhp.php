<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\MongoDbPhp as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class MongoDbPhp
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class MongoDbPhp extends BaseAbstract
{
    public const NAME = 'MongoDbPhp';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:mongodbphp')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}