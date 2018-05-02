<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\DockerPortainer as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Docker
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class DockerUi extends BaseAbstract
{
    public const NAME = 'DockerUi';
    public const SERVICE_CLASS = Process::class;
    public const ADD_SITE = true;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:dockerui')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}