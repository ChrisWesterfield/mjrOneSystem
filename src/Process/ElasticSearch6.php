<?php
declare(strict_types=1);

namespace App\Process;

use App\System\Config\Site;

/**
 * Class Ant
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class ElasticSearch6 extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Elastic Search 6 Software';
    public const SOFTWARE = [
        'elasticsearch'
    ];
    public const REQUIREMENTS = [
        Java::class,
    ];
    public const VERSION_TAG = 'elastic6';
    public const LIST_FILE = '/etc/apt/sources.list.d/elastic-6.x.list';
    public const COMMANDS = [
        self::WGET . ' -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | ' . self::SUDO . ' apt-key add - ',
        'echo "deb https://artifacts.elastic.co/packages/6.x/apt stable main" | ' . self::SUDO . ' ' . self::TEE . ' ' . self::LIST_FILE,
        self::SUDO . ' ' . self::APT . ' update',
    ];
    public const DEFAULT_PORT = 9200;
    public const DEFAULT_PORT_I = 9300;
    public const COMMANDS2 = [
        self::SUDO . ' ' . self::SED . ' -i "s/#cluster.name: mjrone/cluster.name: vagrant/" /etc/elasticsearch/elasticsearch.yml',
        self::ENABLE_SERVICE . ' elasticsearch',
        self::SERVICE_CMD . ' elasticsearch ' . self::SERVICE_START,
    ];

    /**
     *
     */
    public const SUBDOMAIN = 'es.';

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }

    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG) && !file_exists(self::INSTALLED_APPS_STORE . ElasticSearch5::VERSION_TAG)) {
            $this->progBarInit(80);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(20);
            foreach (self::COMMANDS as $COMMAND) {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            foreach (self::COMMANDS2 as $COMMAND) {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT_I);
            $this->progBarFin();
        }
    }

    public function uninstall(): void
    {
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(60);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(20);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO . ' rm -f ' . self::LIST_FILE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::APT . ' update');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT_I);
            if ($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN . $this->getConfig()->getName())) {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN . $this->getConfig()->getName());
            }
            $this->progBarFin();
        }
    }

    public function configure(): void
    {
        $this->addSite([
            'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
            'type' => 'Proxy',
            'listen' => '127.0.0.1:' . self::DEFAULT_PORT,
            'category' => Site::CATEGORY_ADMIN,
            'description' => 'Elastic Search 6 Frontend'
        ]);
    }
}