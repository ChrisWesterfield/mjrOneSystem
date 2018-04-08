<?php
declare(strict_types=1);

namespace App\Services;

use App\Process\Apache2;
use App\Process\Beanstalked;
use App\Process\Cockpit;
use App\Process\CouchDb;
use App\Process\DarkStat;
use App\Process\ElasticSearch5;
use App\Process\ElasticSearch6;
use App\Process\Errbit;
use App\Process\MailHog;
use App\Process\Maria;
use App\Process\Memcached;
use App\Process\Mongo;
use App\Process\MySQL56;
use App\Process\MySQL57;
use App\Process\MySQL8;
use App\Process\Netdata;
use App\Process\Nginx;
use App\Process\Php56;
use App\Process\Php70;
use App\Process\Php71;
use App\Process\Php72;
use App\Process\PostgreSQL;
use App\Process\RabbitMQ;
use App\Process\Redis;
use App\System\Config\Fpm;
use App\System\SystemConfig as SysConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SystemConfig
 * @package App\Services
 * @author chris westerfield <chris@mjr.one>
 */
class SystemConfig
{
    public const LOCAL_IP = '127.0.0.1';
    /**
     * @var array
     */
    protected $ports;

    /**
     * @var ArrayCollection
     */
    protected $features;

    /**
     * @var SysConfig
     */
    protected $config;

    public function __construct()
    {
        $this->ports = [];
        $this->config = SysConfig::get();
        $this->features = $this->config->getFeatures();

        //Web Server
        $this->getServices(1, Nginx::class, 'nginx', $this->config->getIp(), ['http' => Nginx::DEFAULT_PORT_HTTP, 'https' => Nginx::DEFAULT_PORT]);
        $this->getServices(2, Apache2::class, 'apache2', self::LOCAL_IP, ['http' => Apache2::DEFAULT_PORT]);

        //PHP App Server
        $php56 = $php70 = $php71 = $php72 = [];
        if (!empty($this->config->getFpm()->count() > 0)) {
            foreach ($this->config->getFpm() as $fpm) {
                /** @var Fpm $fpm */
                switch ($fpm->getVersion()) {
                    case Php56::VERSION:
                        $php56[$fpm->getName()] = $fpm->getPort();
                        break;
                    case Php70::VERSION:
                        $php70[$fpm->getName()] = $fpm->getPort();
                        break;
                    case Php71::VERSION:
                        $php71[$fpm->getName()] = $fpm->getPort();
                        break;
                    case Php72::VERSION:
                        $php72[$fpm->getName()] = $fpm->getPort();
                        break;
                }
            }
        }
        if (!empty($php56)) {
            $this->getServices(3, Php56::class, 'App Server Php 5.6', self::LOCAL_IP, $php56);
        }
        if (!empty($php70)) {
            $this->getServices(4, Php70::class, 'App Server Php 7.0', self::LOCAL_IP, $php70);
        }
        if (!empty($php71)) {
            $this->getServices(5, Php71::class, 'App Server Php 7.1', self::LOCAL_IP, $php71);
        }
        if (!empty($php72)) {
            $this->getServices(6, Php72::class, 'App Server Php 7.2', self::LOCAL_IP, $php72);
        }

        //DB
        $this->getServices(7, Maria::class, 'Maria DB Server', self::LOCAL_IP, ['db' => Maria::DEFAULT_PORT]);
        $this->getServices(7, MySQL56::class, 'MySQL 5.6', self::LOCAL_IP, ['db' => MySQL56::DEFAULT_PORT]);
        $this->getServices(7, MySQL57::class, 'MySQL 5.7', self::LOCAL_IP, ['db' => MySQL57::DEFAULT_PORT]);
        $this->getServices(7, MySQL8::class, 'MySQL 8.0', self::LOCAL_IP, ['db' => MySQL8::DEFAULT_PORT]);
        $this->getServices(8, PostgreSQL::class, 'PostgreSQL Server', self::LOCAL_IP, ['db' => PostgreSQL::DEFAULT_PORT]);

        //NoSQL Services
        $this->getServices(9, Mongo::class, 'MongoDB Server', self::LOCAL_IP, ['nosql' => Mongo::DEFAULT_PORT]);
        $this->getServices(10, CouchDb::class, 'CouchDB Server', self::LOCAL_IP, ['nosql' => CouchDb::DEFAULT_PORT]);
        $this->getServices(11, ElasticSearch5::class, 'Elastic Search 5.x', self::LOCAL_IP, ['http' => ElasticSearch5::DEFAULT_PORT, 'transport' => 9300]);
        $this->getServices(11, ElasticSearch6::class, 'Elastic Search 6.x', self::LOCAL_IP, ['http' => ElasticSearch6::DEFAULT_PORT, 'transport' => 9300]);
        $this->getServices(12, Memcached::class, 'Memcache Server', self::LOCAL_IP, ['server' => Memcached::DEFAULT_PORT]);
        $this->getServices(13, Redis::class, 'Redis Server', self::LOCAL_IP, ['server' => Redis::DEFAULT_PORT]);

        //Queue Server
        $this->getServices(14, Beanstalked::class, 'Beanstalkd Queue Server', self::LOCAL_IP, ['server' => Beanstalked::DEFAULT_PORT]);
        $this->getServices(15, RabbitMQ::class, 'Rabbit MQ Server', self::LOCAL_IP, ['server' => RabbitMQ::DEFAULT_PORT, 'http' => RabbitMQ::DEFAULT_PORT_ADMIN]);

        //Mail Services
        $this->getServices(16, MailHog::class, 'MailHog Servers', self::LOCAL_IP, ['http' => MailHog::DEFAULT_PORT, 'smtp' => 1025]);

        //Monitoring
        $this->getServices(17, Errbit::class, 'Errbit Server', self::LOCAL_IP, ['http' => Errbit::DEFAULT_PORT]);
        $this->getServices(18, DarkStat::class, 'Darkstat Server', self::LOCAL_IP, ['http' => DarkStat::DEFAULT_PORT]);
        $this->getServices(19, Netdata::class, 'Netdata Server', self::LOCAL_IP, ['http' => Netdata::DEFAULT_PORT]);

        //Admin Servers
        $this->getServices(20, Cockpit::class, 'Cockpit Server', self::LOCAL_IP, ['http' => Cockpit::DEFAULT_PORT]);
    }

    /**
     * @return array
     */
    public function getServiceArray(): array
    {
        return $this->getPorts();
    }

    /**
     * @param int $id
     * @param string $class
     * @param string $name
     * @param string $host
     * @param array $ports
     */
    protected function getServices(int $id, string $class, string $name, string $host, array $ports = [])
    {
        if ($this->features->contains($class)) {
            $this->ports[$id] = [
                'description' => $name,
                'ip' => $host,
                'ports' => $ports,
                'class' => $class
            ];
        }
    }

    /**
     * @return array
     */
    public function getPorts(): array
    {
        return $this->ports;
    }

    /**
     * @return ArrayCollection
     */
    public function getFeatures(): ArrayCollection
    {
        return $this->features;
    }

    /**
     * @return SysConfig
     */
    public function getConfig(): SysConfig
    {
        return $this->config;
    }
}