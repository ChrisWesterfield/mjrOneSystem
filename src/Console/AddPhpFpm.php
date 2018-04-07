<?php
declare(strict_types=1);
namespace App\Console;
use App\Process\Php56;
use App\Process\Php70;
use App\Process\Php71;
use App\Process\Php72;
use App\Process\PhpFpmSites as Process;
use App\System\Config\Fpm;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PhpFpmSites
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class AddPhpFpm extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:add:phpfpm')
            ->setHelp('add or remove phpFPM Process')
            ->setDescription('add or remove phpFpm Process')
            ->addArgument('name',InputArgument::REQUIRED,'name of the instance')
            ->addArgument('version',InputArgument::REQUIRED,'PhpVersion')
            ->addOption('maxChildren',null,InputOption::VALUE_REQUIRED,'max Children (default: 16)', 16)
            ->addOption('maxSpare', null, InputOption::VALUE_REQUIRED,'max Spare Processes (default: 4)', 4)
            ->addOption('minSpare', null, InputOption::VALUE_REQUIRED, 'min Spare Processes (default: 2)', 2)
            ->addOption('maxRam', null, InputOption::VALUE_REQUIRED, 'Maximum Ram (default: 512M)', '512M')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Start Process Count', 2)
            ->addOption('pm', null, InputOption::VALUE_REQUIRED, 'Process Manager (static, dynamic or ondemmand - default: dynamic)','dynamic')
            ->addOption('xdebug', null, InputOption::VALUE_NONE, 'enable XDebug')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Prot of Process', 9000)
            ->addOption('processIdleTimeout', null, InputOption::VALUE_REQUIRED, 'Process Idle Timeout', '10s')
            ->addOption('maxRequests', null, InputOption::VALUE_REQUIRED, 'Maximum Ammount of Requests', 200)
            ->addOption('disableDisplayError', null, InputOption::VALUE_REQUIRED, 'disable Display Errors')
            ->addOption('disableLogErrors', null, InputOption::VALUE_REQUIRED, 'Disable Logging of Errors')
            ->addOption('flags', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Php Flags (--flags="ID=VALUE"')
            ->addOption('values', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'PHP Values (--values="id=value"')
            ->addOption('listen', null, InputOption::VALUE_REQUIRED, 'either 127.0.0.1 or path. Don\'t use any other IP!','127.0.0.1')
            ->addOption('remove','r', InputOption::VALUE_NONE, 'remove package completley');
    }



    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->hasArgument('name') && SystemConfig::get()->getFpm()->containsKey($input->getArgument('name')) && $input->hasOption('remove') && $input->getOption('remove')===true)
        {
            $output->writeln('<info>Removing Php FPM Config '.$input->getArgument('name').'</info>');
            SystemConfig::get()->getFpm()->remove($input->getArgument('name'));
            SystemConfig::get()->writeConfigs();
            $output->writeln('done');
            return;
        }
        $options = $input->getOptions();
        if(!SystemConfig::get()->getFeatures()->contains(Php72::class) && Php72::VERSION === $options['version'])
        {
            $output->writeln('<error>Php 7.2 is not installed!</error>');
        }
        if(!SystemConfig::get()->getFeatures()->contains(Php71::class) && Php71::VERSION === $options['version'])
        {
            $output->writeln('<error>Php 7.1 is not installed!</error>');
        }
        if(!SystemConfig::get()->getFeatures()->contains(Php70::class) && Php70::VERSION === $options['version'])
        {
            $output->writeln('<error>Php 7.0 is not installed!</error>');
        }
        if(!SystemConfig::get()->getFeatures()->contains(Php56::class) && Php56::VERSION === $options['version'])
        {
            $output->writeln('<error>Php 5.6 is not installed!</error>');
        }
        $output->writeln('<info>Adding Php FPM Config '.$input->getArgument('name').'</info>');
        if(SystemConfig::get()->getFpm()->count() > 0)
        {
            foreach(SystemConfig::get()->getFpm() as $fpm)
            {
                /** @var Fpm $fpm */
                if($fpm->getName()===$input->getArgument('name'))
                {
                    $output->writeln('<error>Name already taken!</error>');
                    return;
                }
                if($fpm->getListen()==='127.0.0.1' && $fpm->getPort()===(int)$options['port'])
                {
                    $output->writeln('<error>Port already taken!</error>');
                    return;
                }
            }
        }
        unset(
            $options['no-ansi'],$options['ansi'],
            $options['version'],$options['verbose'],
            $options['quiet'],$options['help'],
            $options['remove'],$options['no-interaction'],
            $options['env'],$options['no-debug']);
        $options['displayError'] = !$options['disableDisplayError'];
        $options['logError'] = !$options['disableLogErrors'];
        unset($options['disableDisplayError'], $options['disableLogErrors']);
        $options['version'] = $input->getArgument('version');
        $options['name'] = $input->getArgument('name');
        $options['port'] = (int)$options['port'];
        $fpm = new Fpm($options);
        SystemConfig::get()->getFpm()->set($fpm->getName(), $fpm);
        SystemConfig::get()->writeConfigs();
        $output->writeln('<comment>PHP Configs need to be refreshed: mjrone:sites:phpfpm</comment>');
        $output->writeln('done');
    }
}