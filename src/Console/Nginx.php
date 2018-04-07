<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Nginx as Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Nginx
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Nginx extends BaseAbstract
{
    public const NAME = 'nginx';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:nginx')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }

}