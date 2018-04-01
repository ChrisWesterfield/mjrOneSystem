<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Blackfire as BlackfireConfig;

/**
 * Class Blackfire
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Blackfire extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Php72::class,
    ];
    public const SOFTWARE = [
        'blackfire-agent',
        'blackfire-php',
    ];
    public const SERVICE_NAME = 'blackfire-agent';
    public const COMMANDS = [
        self::CURL.' -s https://packagecloud.io/gpg.key | '.self::SUDO.' apt-key add -',
        'echo "deb http://packages.blackfire.io/debian any main" | '.self::SUDO.' '.self::TEE.' /etc/apt/sources.list.d/blackfire.list',
        self::SUDO.' '.self::APT.' update',
    ];
    public const VERSION_TAG = 'blackfire';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(70);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
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
            $this->progBarInit(55);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO.' rm -Rf /etc/blackfire/agent');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf /home/vagrant/.blackfire.ini');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function configure(): void
    {
        if($this->getConfig()->getBlackFire() instanceof BlackfireConfig)
        {
            $vars = [
                'clientId'=>$this->getConfig()->getBlackFire()->getClientId(),
                'clientToken'=>$this->getConfig()->getBlackFire()->getClientToken(),
                'serverId'=>$this->getConfig()->getBlackFire()->getServerId(),
                'serverToken'=>$this->getConfig()->getBlackFire()->getServerToken(),
            ];
            $agent = $this->getContainer()->get('twig')->render('configuration/blackfire/agent.config.twig',$vars);
            $client = $this->getContainer()->get('twig')->render('configuration/blackfire/client.config.twig',$vars);
            $this->execute('echo "'.$agent.'" | '.self::SUDO.' '.self::TEE.' /etc/blackfire/agent');
            $this->execute('echo "'.$client.'" | '.self::TEE.' /home/vagrant/.blackfire.ini');
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
            }
            $this->execute(self::SERVICE_CMD.' '.self::SERVICE_NAME.' '.self::SERVICE_RESTART);
        }
    }
}