<?php
declare(strict_types=1);

namespace App\Console;
use App\Process\Sites\Proxy;
use App\System\Config\Site;
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
class AddSite extends ContainerAwareCommand
{
    /**
     *
     */
    public const DEFAULT_FPM = 'default';

    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:add:web')
            ->setHelp('add or remove Site')
            ->setDescription('add or remove Site')
            ->addArgument('map', InputArgument::REQUIRED, 'Url of the Website')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of Site (Example: Symfony4)', 'Symfony4')
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Description')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'To Path (Path for Site (full Path))')
            ->addOption('fpm', null, InputOption::VALUE_REQUIRED, 'existing fpm server (default or name of an fpm worker', self::DEFAULT_FPM)
            ->addOption('https', null, InputOption::VALUE_REQUIRED, 'HTTPs Port', 443)
            ->addOption('http', null, InputOption::VALUE_REQUIRED, 'HTTP Port')
            ->addOption('charSet', null, InputOption::VALUE_REQUIRED, 'Char Set of Nginx', 'utf-8')
            ->addOption('fcgiParams', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Fcgi Params')
            ->addOption('zRay', null, InputOption::VALUE_NONE, 'Enable ZRay')
            ->addOption('clientMaxBodySize', null, InputOption::VALUE_REQUIRED, 'Client Max Body Size in Mbyte (M)', 16)
            ->addOption('proxyApp', null, InputOption::VALUE_REQUIRED, 'Port for Proxy App')
            ->addOption('fcgiBufferSize', null, InputOption::VALUE_REQUIRED, 'FCGI Buffer Size', '16k')
            ->addOption('fcgiConnectionTimeOut', null, InputOption::VALUE_REQUIRED, 'Fcgi Connection Timeout', 300)
            ->addOption('fcgiBuffer', null, InputOption::VALUE_REQUIRED, 'Fcgi Buffer Size', '4 16k')
            ->addOption('fcgiSendTimeOut', null, InputOption::VALUE_REQUIRED, 'Fcgi Send Timeout', 300)
            ->addOption('fcgiReadTimeOut', null, InputOption::VALUE_REQUIRED, 'Fcgi Read Timeout', 300)
            ->addOption('fcgiBusyBufferSize', null, InputOption::VALUE_REQUIRED, 'Fcgi Read Timeout', '64k')
            ->addOption('category', null, InputOption::VALUE_REQUIRED,'Category for Site', Site::CATEGORY_APP)
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove Size');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('remove') && $input->getOption('remove') && SystemConfig::get()->getSites()->containsKey($input->getArgument('map'))) {
            /** @var Site $site */
            $site = SystemConfig::get()->getSites()->get($input->getArgument('map'));
            SystemConfig::get()->getSites()->remove($input->getArgument('map'));
            $output->writeln('<info>Site '.$site->getMap().' was removed!</info>');
            SystemConfig::get()->writeConfigs();
            return;
        }
        if(!in_array($input->getOption('category'),Site::CATEGORIES))
        {
            $output->writeln('Category unknown. Only the Categories '.implode(',',Site::CATEGORIES).' are currently supported!');
            return;
        }
        $options = $input->getOptions();
        unset(
            $options['no-ansi'], $options['ansi'],
            $options['version'], $options['verbose'],
            $options['quiet'], $options['help'],
            $options['remove'], $options['no-interaction'],
            $options['env'], $options['no-debug']);
        $options['map'] = $input->getArgument('map');
        $site = new Site($options);
        if(SystemConfig::get()->getSites()->containsKey($site->getMap()))
        {
            if(!SystemConfig::get()->getFpm()->containsKey($site->getFpm()))
            {
                $output->writeln('<error>Site already exists!</error>');
                return;
            }
        }
        if($site->getFpm()!== self::DEFAULT_FPM)
        {
            if(!SystemConfig::get()->getFpm()->containsKey($site->getFpm()))
            {
                $output->writeln('<error>Fpm Process not found</error>');
                return;
            }
        }
        $type = 'App\\Process\\Sites\\'.$site->getType();
        if(!class_exists($type))
        {
            $output->writeln('<error>Template '.$site->getType().' does not exist!!</error>');
            return;
        }
        if($type !== Proxy::class && $input->hasOption('to') && empty($input->getOption('to')))
        {
            $output->writeln('<error>A to Path '.$site->getType().' needs to be set!</error>');
            return;
        }
        SystemConfig::get()->getSites()->set($site->getMap(), $site);
        SystemConfig::get()->writeConfigs();
    }
}