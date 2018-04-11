<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Zray
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Statsd extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Performance Monitoring Toolkit';
    public const REQUIREMENTS = [
        NodeJs::class,
        Python::class,
        Supervisor::class,
    ];
    public const SOFTWARE = [
        'libcairo2-dev',
    ];

    public const OPT = '/opt';
    public const GRAPHITE_SRC = '/graphite';
    public const GRAPHITE_WEB = self::GRAPHITE_SRC.'/graphite-web';
    public const STATSD_SRC = self::OPT.'/statsd';
    public const GRAFANA_SRC = self::OPT.'/grafana';
    public const SUBDOMAIN = 'statsd.';
    public const COMMANDS = [
        self::SUDO.' '.Python::VIRTUAL_ENV.' '.self::GRAPHITE_SRC,
        self::SUDO.' '.self::GIT_CLONE.' https://github.com/graphite-project/graphite-web.git '.self::GRAPHITE_WEB,
        'cd '.self::GRAPHITE_WEB.' && '.self::SUDO.' '.self::GIT.' checkout 0.9.12',
        self::SUDO.' '.Python::BIN.' '.self::GRAPHITE_WEB.'/setup.py install ',
        self::SUDO.' '.Python::PIP.' '.self::GRAPHITE_WEB.'/requirements.txt',
        self::SUDO.' django-admin.py syncdb --settings=graphite.settings --pythonpath='.self::GRAPHITE_WEB,
        self::SUDO.' '.self::GIT_CLONE.' https://github.com/etsy/statsd.git '.self::STATSD_SRC,
        self::SUDO.' '.self::GIT_CLONE.' https://github.com/grafana/grafana.git '.self::GRAFANA_SRC,
        self::SUDO.' cp '.self::GRAFANA_SRC.'/src/config.sample.js '.self::GRAFANA_SRC.'/src/config.js',
        self::SUDO.' adduser grafan',
        self::SUDO.' '.self::CHOWN.' -R grafana:grafana '.self::GRAFANA_SRC.' '.self::STATSD_SRC.' '.self::GRAPHITE_SRC,
    ];
    public const VERSION_TAG = 'zray';
    public const CONFIG_DIR = self::VAGRANT_HOME.'/etc/graphite/';
    public const DEFAULT_DIR = 8126;
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(300);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(100);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(10);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(10);
            }
            $this->createLink(self::CONFIG_DIR.'supervisor.carbon.conf',self::SUPERVISOR_D.'supervisor.carbon.conf');
            $this->progBarAdv(5);
            $this->createLink(self::CONFIG_DIR.'supervisor.gunicorn.conf',self::SUPERVISOR_D.'supervisor.gunicorn.conf');
            $this->progBarAdv(5);
            $this->createLink(self::CONFIG_DIR.'supervisor.statsd.conf',self::SUPERVISOR_D.'supervisor.statsd.conf');
            $this->progBarAdv(5);
            $this->execute(self::SERVICE_CMD.' '.Supervisor::VERSION_TAG.' '.self::SERVICE_RESTART);
            $this->progBarAdv(5);
            $this->createLink(self::CONFIG_DIR.'carbone.conf',self::GRAPHITE_SRC.'/conf/carbone.conf');
            $this->progBarAdv(5);
            $this->createLink(self::CONFIG_DIR.'storage-schema.conf',self::GRAPHITE_SRC.'/conf/storage-schema.conf');
            $this->progBarAdv(5);
            $this->createLink(self::CONFIG_DIR.'config.js',self::STATSD_SRC.'/conf/config.js');
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
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
            $this->progBarInit(70);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $delete = [
                self::SUPERVISOR_D.'supervisor.carbon.conf',
                self::SUPERVISOR_D.'supervisor.gunicorn.conf',
                self::SUPERVISOR_D.'supervisor.statsd.conf',
                self::GRAPHITE_SRC,
                self::GRAFANA_SRC,
                self::STATSD_SRC,
            ];
            foreach($delete as $value)
            {
                $this->execute(self::RM.' -Rf '.$value);
                $this->progBarAdv(5);
            }
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            if($this->getConfig()->getSites()->containsKey(self::SUBDOMAIN .$this->getConfig()->getName()))
            {
                $this->getConfig()->getSites()->remove(self::SUBDOMAIN .$this->getConfig()->getName());
            }
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $site = new Site(
            [
                'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
                'type'=>'Proxy',
                'listen'=>'127.0.0.1:'.self::DEFAULT_PORT,
                'category'=>Site::CATEGORY_ADMIN,
                'description'=>'StatsD UI'
            ]
        );
        $this->getConfig()->getSites()->set($site->getMap(),$site);
    }
}