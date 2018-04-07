<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Ngrok as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class Ngrok
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class Ngrok extends BaseAbstract
{
    public const NAME = 'Ngrok';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:ngrok')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}