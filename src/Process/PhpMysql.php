<?php
declare(strict_types=1);

namespace App\Process;


use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class Php72
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class PhpMysql extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'PHP MySQL Extension';
    public const SOFTWARE = [
        'openjdk-8-jre-headless',
        'openjdk-8-jdk-headless',
    ];
    public const REQUIREMENTS = [

    ];
    public const VERSION_TAG = 'phpmysql';

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(120);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            if ($this->getConfig()->getFeatures()->contains(Php56::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install -y php' . Php56::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php56::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php70::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install -y php' . Php70::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php70::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php71::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install -y php' . Php71::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php71::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php72::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install -y php' . Php72::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php72::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php73::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' install -y php' . Php73::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php73::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
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
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(125);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(30);
            if ($this->getConfig()->getFeatures()->contains(Php56::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge -y php' . Php56::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php56::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php70::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge -y php' . Php70::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php70::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php71::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge -y php' . Php71::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php71::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php73::class)) {
                $this->execute(self::SUDO . ' ' . self::APT . ' purge -y php' . Php73::VERSION . '-mysql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD . ' ' . Php73::SERVICE_NAME . ' ' . self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function configure(): void
    {
    }
}