<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Docker\Compose;
use App\System\Docker\Service;
use App\System\SystemConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerCompose
 * @package App\Process
 */
class DockerCompose extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Docker::class,
    ];
    public const VERSION_TAG = 'dockerCompose';
    public const DOCKER_COMPOSE = self::LOCAL_BIN.'docker-compose';
    public const DESCRIPTION = 'Docker Compose Tools';
    public const SOFTWARE = [];
    public const COMMANDS = [
        self::SUDO.' '.self::CURL.' -L --fail "https://github.com/docker/compose/releases/download/1.11.2/docker-compose-$(uname -s)-$(uname -m)" -o '.self::DOCKER_COMPOSE,
        self::SUDO.' '.self::CHMOD.' +x '.self::DOCKER_COMPOSE,
    ];

    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(30);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(25);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::DOCKER_COMPOSE);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
    }

    public function configureComposerFiles()
    {
        if(SystemConfig::get()->getDockerCompose()->count() > 0)
        {
            $this->getOutput()->writeln('<info>Generating Docker Compose Files</info>');
            $fc = SystemConfig::get()->getDockerCompose()->count()*5;
            $this->progBarInit($fc);
            foreach(SystemConfig::get()->getDockerCompose() as $dc)
            {
                /** @var Compose $dc */
                $file = $dc->getFilename();
                $path = $dc->getFilePath();
                $fn = $path.'/'.$file;
                $this->printOutput('<comment>Composing '.$fn.' File </comment>',1);
                $file = [
                    'version'=>''.$dc->getVersion(),
                    'services'=>[],
                    'networks'=>[
                        $dc->getNetworkName()=>[
                            'driver'=>$dc->getNetworkDriver(),
                        ]
                    ]
                ];
                $envVars = new ArrayCollection();
                foreach($dc->getServices() as $service)
                {
                    /** @var Service $service */
                    $subService = $service->toArray();
                    $subService['volumes'] = [];
                    $subService['ports'] = [];
                    $subService['environment'] = [];
                    $subService['depends_on'] = [];
                    unset($subService['depends']);
                    unset($subService['name'], $subService['build']);
                    if(array_key_exists('buildContext', $subService))
                    {
                        unset($subService['buildContext']);
                    }
                    if(array_key_exists('buildDockerFile', $subService))
                    {
                        unset($subService['buildDockerFile']);
                    }
                    if(array_key_exists('memoryLimit', $subService))
                    {
                        unset($subService['memoryLimit']);
                        $subService['mem_limit'] = $service->getMemoryLimit();
                    }
                    if($service->isBuild() && $service->getBuildContext()!==null && $service->getBuildDockerFile()!==null)
                    {
                        $subService['build'] = [
                            'context'=>$service->getBuildContext(),
                            'dockerfile'=>$service->getBuildDockerFile(),
                        ];
                    }
                    if($service->getVolumes()->count() > 0)
                    {
                        foreach($service->getVolumes() as $vol)
                        {
                            $subService['volumes'][] = $vol->getLocal().':'.$vol->getRemote().($vol->getMode()!==null?':'.$vol->getMode():'');
                        }
                    }
                    if($service->getPorts()->count() > 0)
                    {
                        foreach($service->getPorts() as $port)
                        {
                            $subService['ports'][] = $port->getLocal().':'.$port->getRemote().($port->getProtocol()!==null?'/'.$port->getProtocol():'');
                        }
                    }
                    if($service->getDepends()->count() > 0)
                    {
                        foreach($service->getDepends() as $dep)
                        {
                            $subService['depends_on'][] = $dep;
                        }
                    }
                    if($service->getEnvironment()->count() > 0)
                    {
                        foreach($service->getEnvironment() as $key=>$value)
                        {
                            $subService['environment'][$key] = $value;
                            $matches = [];
                            preg_match('/\$\{[A-Za-z0-9_]*\}/', $value, $matches);
                            if(count($matches)==1 && !$envVars->contains($matches[0]))
                            {
                                $envVars->add($matches[0]);
                            }
                        }
                    }
                    foreach($subService as $sKey=>$sValue)
                    {
                        if(empty($sValue))
                        {
                            unset($subService[$sKey]);
                        }
                    }
                    $file['services'][$service->getName()] = $subService;
                }
                $yaml = Yaml::dump($file,100);
                file_put_contents($fn,$yaml);
                if($envVars->count() > 0)
                {
                    $file = $path.'/.env';
                    $content = '';
                    if(file_exists($file))
                    {
                        $content = file_get_contents($file);
                    }
                    foreach($envVars as $var)
                    {
                        $var = str_replace(['$', '{', '}'], ['','',''], $var);
                        if(strpos($content, $var)===false)
                        {
                            $content.=$var."=\"\"\n";
                        }
                    }
                    file_put_contents($file, $content);
                }
                $this->printOutput('<comment>Composing '.$fn.' File done</comment>',1);
                $this->progBarAdv(5);
            }
            $this->progBarFin();
            $this->getOutput()->writeln('done');
        }
    }
}