<?php
declare(strict_types=1);
namespace App\Console;


use App\Process\Apache2;
use App\Process\DockerCompose;
use App\Process\Nginx;
use App\Process\PhpFpmSites;
use App\Process\ProcessHostsFile;
use App\Process\ProcessInterface;
use App\Process\WebSitesApache;
use App\Process\WebSitesNginx;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Install extends ContainerAwareCommand
{
    use LockableTrait;
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:install')
            ->setHelp('Install Packages according to config.yaml.')
            ->setDescription('Install Packages according to config.yaml.');
    }



    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>Command is locked!</error>');
            return 0;
        }
        $style = new SymfonyStyle($input, $output);
        $config = SystemConfig::get();
        /** @var string[] $features */
        $features = $config->getFeatures();
        if($features->count() > 0)
        {
            foreach($features as $feature)
            {
                /** @var ProcessInterface $instance */
                $instance = $this->getInstance($feature, $config, $style, $output);
                $instance->setIo($style);
                $instance->setContainer($this->getContainer());
                $instance->setConfig($config);
                $instance->setOutput($output);
                $instance->install();
                $instance->configure();
            }
        }
        $config->writeConfigs();
        if(SystemConfig::get()->getFeatures()->contains(Nginx::class))
        {
            $instance = $this->getInstance(WebSitesNginx::class, $config, $style, $output);
            $instance->configure();
        }
        if(SystemConfig::get()->getFeatures()->contains(Apache2::class))
        {
            $instance = $this->getInstance(WebSitesApache::class, $config, $style, $output);
            $instance->configure();
        }
        $instance = $this->getInstance(PhpFpmSites::class, $config, $style, $output);
        $instance->configure();
        $instance = $this->getInstance(ProcessHostsFile::class, SystemConfig::get(), $style, $output);
        $instance->configure();
        if(SystemConfig::get()->getFeatures()->contains(DockerCompose::class))
        {
            if(SystemConfig::get()->getDockerCompose()->count() > 0)
            {
                /** @var DockerCompose $instance */
                $instance = $this->getInstance(DockerCompose::class, SystemConfig::get(), $style, $output);
                $instance->configureComposerFiles();
            }
        }
    }

    protected function getInstance(string $class, SystemConfig $config, SymfonyStyle $style, OutputInterface $output):ProcessInterface
    {
        /** @var ProcessInterface $instance */
        $instance = new $class();
        $operation = ($instance instanceof WebSitesNginx || $instance instanceof WebSitesApache || $instance instanceof PhpFpmSites)?'Running ':'Installing';
        $output->writeln($operation.$class::VERSION_TAG);
        $instance->setIo($style);
        $instance->setContainer($this->getContainer());
        $instance->setConfig($config);
        $instance->setOutput($output);
        return $instance;
    }

}