<?php
declare(strict_types=1);
namespace App\Process;


use App\System\Config\Fpm;
use App\System\Config\Site;
use App\System\SystemConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Class ProcessAbstract
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
abstract class ProcessAbstract
{
    /**
     * @var SystemConfig
     */
    protected $config;
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var ProgressBar
     */
    protected $pb;

    /**
     * ProcessAbstract constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param SystemConfig $config
     */
    public function setConfig(SystemConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return SystemConfig
     */
    public function getConfig(): SystemConfig
    {
        return $this->config;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return ProcessInterface
     */
    public function setContainer(ContainerInterface $container): ProcessInterface
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo():SymfonyStyle
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle $io
     * @return ProcessAbstract
     */
    public function setIo(SymfonyStyle $io): ProcessAbstract
    {
        $this->io = $io;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return ProcessInterface
     */
    public function setOutput(OutputInterface $output):ProcessInterface
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param $commands
     * @return string
     */
    public function execute($commands)
    {
        $process = new Process($commands);
        $process->setTimeout(600);
        $process->run();
        $this->printOutput($commands,2);
        $response = $process->getOutput();
        $aResponse = explode("\n",$response);
        foreach($aResponse as $resp)
        {
            $this->printOutput('<comment>'.$resp.'</comment>', 3);
        }
        return $response;
    }

    /**
     * @param $string
     * @param int $level
     */
    protected function printOutput(string $string,int $level = 0)
    {
        if($level===0 || ($level===1 && $this->getOutput()->isVerbose()) || ($level===2 && $this->getOutput()->isVeryVerbose()) || ($level===3 && $this->getOutput()->isDebug())) {
            $this->getOutput()->writeln($string);
        }
    }

    /**
     * @param $list
     */
    public function installPackages(array $list):void
    {
        if(empty($list))
        {
            return;
        }
        $cmd = ProcessInterface::APT_INSTALL;
        $class = get_class($this);
        if(defined($class.'::ALTERNATIVE_MODE') && $class::ALTERNATIVE_MODE===true)
        {
            $cmd = ProcessInterface::APT_INSTALL_ALT;
        }
        $this->execute(sprintf($cmd,implode(' ',$list)));
    }

    /**
     * @param $list
     */
    public function uninstallPackages($list):void
    {
        $this->execute(sprintf(ProcessInterface::APT_UNINSTALL,implode(' ',$list)));
    }

    /**
     * @param $list
     */
    public function checkRequirements(string $name, $list):void
    {
        if(!empty($list))
        {
            $this->progBarMessage('Checking Dependencies');
            foreach($list as $item)
            {
                if(empty($item))
                {
                    continue;
                }
                /** @var ProcessInterface|Ant $class */
                if(!file_exists(ProcessInterface::INSTALLED_APPS_STORE.$item::VERSION_TAG))
                {
                    $this->progBarMessage('Installing Dependency '.$item.'');
                    /** @var ProcessInterface $instance */
                    $instance = new $item();
                    $instance->setOutput($this->getOutput());
                    $instance->setConfig($this->getConfig());
                    $instance->setIo($this->getIo());
                    $instance->install();
                    $instance->configure();
                    $this->printOutput('done',1);
                    $this->progBarMessage('Checking Dependencies');
                }
                $this->getConfig()->addRequirement(get_class($this), $item);
            }
            $this->progBarMessage();
        }
    }

    /**
     * @param int $maxValue
     */
    public function progBarInit(int $maxValue = 100):void
    {
        $maxValue/=5;
        $this->pb = $this->getIo()->createProgressBar();
        $this->pb->setMessage('');
        $this->pb->setFormat('MJR!ONE: %current% [%bar%]  %elapsed:6s% (Memory Usage: %memory:6s%) %message%');
        $this->pb->setEmptyBarCharacter('░'); // light shade character \u2591
        $this->pb->setProgressCharacter('');
        $this->pb->setBarCharacter('<info>█</info>'); // dark shade character \u2593
        $this->pb->setBarWidth(40);
    }

    /**
     * @param int $steps
     */
    public function progBarAdv(int $steps=1):void
    {
        $steps/=5;
        if($this->pb instanceof ProgressBar)
        {
            $this->pb->advance($steps);
        }
    }

    public function progBarMessage(string $message=''):void
    {
        if($this->pb instanceof ProgressBar)
        {
            $this->pb->setMessage($message);
        }
    }

    /**
     *
     */
    public function progBarFin():void
    {
        $this->pb->finish();
        $this->getIo()->newLine(2);
        $this->pb = null;
    }

    /**
     * @param $source
     * @param $target
     */
    public function createLink($source, $target):void
    {
        $this->execute(ProcessInterface::SUDO.' '.sprintf(ProcessInterface::LINK, $source, $target));
    }

    /**
     * @param $directory
     * @param $file
     * @return void
     */
    public function touch($directory, $file):void
    {
        if(!is_dir($directory))
        {
            mkdir($directory,0775, true);
        }
        touch($directory.$file);
    }

    /**
     * @param string $package
     * @return void
     */
    public function checkUninstall(string $package):void
    {
        if($this->getConfig()->getRequirements()->containsKey($package) && $this->getConfig()->getRequirements()->get($package)->count() > 0)
        {
            $errorMessage = '<error>Could not uninstall %s due to Requirements from the packages [ %s ]</error>';
            $packages = [];
            if($this->getConfig()->getRequirements()->get($package)->count() > 0)
            {
                foreach($this->getConfig()->getRequirements()->get($package) as $lPack)
                {
                    $packages[] = $lPack;
                }
            }
            $bundle = implode(', ',$packages);
            $message = sprintf($errorMessage,$package, $bundle);
            $this->getOutput()->writeln($message);
            die;
        }
    }

    /**
     * @param string $package
     * @return ProcessInterface
     */
    public function uninstallRequirement(string $package):ProcessInterface
    {
        foreach($this->getConfig()->getRequirements() as $key=>$value)
        {
            /** @var ArrayCollection $value */
            if($value->contains($package))
            {
                $this->getConfig()->getRequirements()->get($key)->removeElement($package);
            }
        }
        return $this;
    }

    /**
     * @param array|null $web
     * @param array|null $fpm
     */
    public function addSite(array $web=null, array $fpm = null):void
    {
        $fpmInstance = null;
        if(!empty($fpm))
        {
            $port = 9001;
            for ($i = 9001; $i < 20000; $i++) {
                if (!$this->getConfig()->getUsedPorts()->contains($i)) {
                    $port = $i;
                    break;
                }
            }
            if ($i > 19999) {
                $this->getOutput()->writeln('no available Ports left!');
            }
            $this->getConfig()->getUsedPorts()->add($i);
            $fpm['listen'] = str_replace(':%%%PORT%%%','',$fpm['listen']);
            $fpm['port'] = $i;
            $fpmInstance = new Fpm($fpm);
            $this->getConfig()->getFpm()->set($fpmInstance->getName(), $fpmInstance);
        }
        if(!empty($web))
        {
            if(array_key_exists('fpm', $web) && $fpmInstance instanceof Fpm && !empty($fpmInstance))
            {
                $web['fpm'] = $fpmInstance->getName();
            }
            $webInstance = new Site($web);
            $this->getConfig()->getSites()->set($webInstance->getMap(), $webInstance);
        }
    }

    /**
     * @param $identity
     */
    public function removeFpm($identity):void
    {
        if ($this->getConfig()->getFpm()->containsKey($identity)) {
            $fpm = $this->getConfig()->getFpm()->get($identity);
            $port = $fpm->getPort();
            $this->getConfig()->getUsedPorts()->removeElement($port);
            $this->getConfig()->getFpm()->remove($identity);
        }
    }

    /**
     * @param $subDomain
     */
    public function removeWeb($subDomain):void
    {
        if ($this->getConfig()->getSites()->containsKey($subDomain . $this->getConfig()->getName())) {
            $this->getConfig()->getSites()->remove($subDomain . $this->getConfig()->getName());
        }
    }

    /**
     *
     */
    public function restartService():void
    {
        //nothing to do
    }
}