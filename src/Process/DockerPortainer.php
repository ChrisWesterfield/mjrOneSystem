<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

/**
 * Class DockerPortainer
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class DockerPortainer extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Docker::class,
    ];
    public const VERSION_TAG = 'dockerPortainer';
    public const DESCRIPTION = 'Docker Compose Tools';
    public const SOFTWARE = [];
    public const DEFAULT_PORT = 8020;
    public const APP_DIR = self::VAGRANT_USER_DIR . '/portainer';
    public const SUBDOMAIN = 'ui.';

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarAdv(5);
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->progBarAdv(5);
            $this->execute(self::MKDIR . ' ' . self::APP_DIR);
            $this->progBarAdv(5);
            $this->start();
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->stop();
            $this->progBarAdv(15);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::RM . ' -Rf ' . self::APP_DIR);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarAdv(5);
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            $this->progBarAdv(5);
            $this->removeWeb(self::SUBDOMAIN);
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite([
            'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
            'type' => 'Proxy',
            'listen' => '127.0.0.1:' . self::DEFAULT_PORT,
            'category' => Site::CATEGORY_ADMIN,
            'description' => 'Docker UI (Portainer)'
        ]);
    }

    /**
     *
     */
    public function restartService(): void
    {
        $this->stop();
        $this->start();
    }

    /**
     *
     */
    public function start()
    {
        $command = self::SUDO . ' ' . Docker::DOCKER_CMD . ' run -d -p ' . self::DEFAULT_PORT . ':9000 --name portainer -v "' . Docker::DOCKER_SOCKET . ':' . Docker::DOCKER_SOCKET . '" -v"' . self::APP_DIR . ':/data"  -e VIRTUAL_HOST=' . self::SUBDOMAIN . $this->getConfig()->getName() . ' portainer/portainer --no-auth --no-analytics --sync-interval 30s';
        $this->getOutput()->writeln('<info>Starting Docker UI Portainer</info>');
        $this->execute($command);
        $this->getOutput()->writeln('done');
    }

    /**
     *
     */
    public function stop()
    {
        $this->getOutput()->writeln('<info>Stopping Docker UI Portainer</info>');
        $command = self::SUDO . ' ' . Docker::DOCKER_CMD . ' stop portainer';
        $this->execute($command);
        $command = self::SUDO . ' ' . Docker::DOCKER_CMD . ' rm portainer';
        $this->execute($command);
        $this->getOutput()->writeln('done');
    }
}