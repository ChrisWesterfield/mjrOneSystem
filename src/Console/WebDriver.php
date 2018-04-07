<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\WebDriver as Process;
use Symfony\Component\Console\Input\InputOption;
/**
 * Class WebDriver
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class WebDriver extends BaseAbstract
{
    public const NAME = 'WebDriver';
    public const SERVICE_CLASS = Process::class;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:package:webdriver')
            ->setHelp('install or uninstall '.self::NAME)
            ->setDescription('install or uninstall '.self::NAME)
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }
}