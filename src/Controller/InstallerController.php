<?php
declare(strict_types=1);

namespace App\Controller;

use App\Process\Apache2;
use App\Process\Beanstalked;
use App\Process\Cockpit;
use App\Process\CockroachDb;
use App\Process\CouchDb;
use App\Process\DarkStat;
use App\Process\DatabaseInterface;
use App\Process\Docker;
use App\Process\ElasticSearch5;
use App\Process\ElasticSearch6;
use App\Process\Errbit;
use App\Process\Hhvm;
use App\Process\Jenkins;
use App\Process\Kibana;
use App\Process\Logstash;
use App\Process\MailHog;
use App\Process\Maria;
use App\Process\Memcached;
use App\Process\Mongo;
use App\Process\Munin;
use App\Process\MySQL56;
use App\Process\MySQL57;
use App\Process\MySQL8;
use App\Process\Netdata;
use App\Process\Nginx;
use App\Process\Php56;
use App\Process\Php70;
use App\Process\Php71;
use App\Process\Php72;
use App\Process\PhpFpmSites;
use App\Process\PostgreSQL;
use App\Process\ProcessAbstract;
use App\Process\ProcessHostsFile;
use App\Process\ProcessInterface;
use App\Process\RabbitMQ;
use App\Process\Redis;
use App\Process\Supervisor;
use App\Process\WebSitesApache;
use App\Process\WebSitesNginx;
use App\System\FlushHelper;
use App\System\HtmlOutput;
use App\System\Input;
use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

define('POST_PONE_RELOAD', true);

/**
 * Class InstallerController
 * @package App\Controller
 * @author Chris Westerfield <chris@mjr.one>
 */
class InstallerController extends Controller
{
    /**
     * @Route("/Software", name="software")
     */
    public function indexAction(): Response
    {
        $finder = new Finder();
        $finder->files()->in('/home/vagrant/system/src/Process')->depth(0);
        $available = [];
        foreach ($finder as $file) {
            $className = 'App\\Process\\' . $file->getBasename('.' . $file->getExtension());
            if ((defined("$className::EXCLUDE") && $className::EXCLUDE === true) || $className === ProcessInterface::class || $className === DatabaseInterface::class || $className === ProcessAbstract::class) {
                continue;
            }
            $requirements = $className::REQUIREMENTS;
            if (!empty($requirements)) {
                foreach ($requirements as $key => $req) {
                    $requirements[$key] = str_replace('App\\Process\\', '', $req);
                }
            }
            $restartable = [
                Apache2::class,
                Beanstalked::class,
                Cockpit::class,
                CouchDb::class,
                ElasticSearch5::class,
                ElasticSearch6::class,
                Errbit::class,
                Hhvm::class,
                Jenkins::class,
                Kibana::class,
                Logstash::class,
                MailHog::class,
                Memcached::class,
                Mongo::class,
                Munin::class,
                MySQL8::class,
                MySQL57::class,
                MySQL56::class,
                Netdata::class,
                Nginx::class,
                Php56::class,
                Php70::class,
                Php71::class,
                Php72::class,
                RabbitMQ::class,
                Redis::class,
                PostgreSQL::class,
                Supervisor::class,
                Maria::class,
                DarkStat::class,
                Docker::class,
                CockroachDb::class,
            ];
            $blocked = false;
            switch ($className) {
                case ElasticSearch5::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(ElasticSearch6::class);
                    break;
                case ElasticSearch6::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(ElasticSearch5::class);
                    break;
                case Maria::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(MySQL56::class) || SystemConfig::get()->getFeatures()->contains(MySQL57::class) || SystemConfig::get()->getFeatures()->contains(MySQL8::class);
                    break;
                case MySQL56::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(Maria::class) || SystemConfig::get()->getFeatures()->contains(MySQL57::class) || SystemConfig::get()->getFeatures()->contains(MySQL8::class);
                    break;
                case MySQL57::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(MySQL56::class) || SystemConfig::get()->getFeatures()->contains(Maria::class) || SystemConfig::get()->getFeatures()->contains(MySQL8::class);
                    break;
                case MySQL8::class:
                    $blocked = SystemConfig::get()->getFeatures()->contains(MySQL56::class) || SystemConfig::get()->getFeatures()->contains(MySQL57::class) || SystemConfig::get()->getFeatures()->contains(Maria::class);
            }
            $available[$className] = [
                'name' => $file->getBasename('.' . $file->getExtension()),
                'description' => (defined("$className::DESCRIPTION") ? (string)constant("$className::DESCRIPTION") : ''),
                'requirements' => $requirements,
                'blocked' => $blocked,
            ];
        }
        $installed = SystemConfig::get()->getFeatures()->toArray();
        $blockedRequirements = [];
        foreach ($installed as $avail) {
            if (SystemConfig::get()->getRequirements()->containsKey($avail) && SystemConfig::get()->getRequirements()->get($avail)->count() > 0) {
                $blockedRequirements[] = $avail;
            }
        }
        return $this->render(
            'software.html.twig',
            [
                'installed' => $installed,
                'available' => $available,
                'blocked' => $blockedRequirements,
                'restartable' => $restartable,
                'specialRestart' => ['Nginx', 'Php72'],
            ]
        );
    }

    /**
     * @Route("/Software/{package}/restart.do", name="software_restart")
     * @param string $package
     * @return StreamedResponse
     */
    public function doRestartAction(string $package): Response
    {
        $pName = $package;
        $package = 'App\\Process\\' . $package;
        $helper = new FlushHelper();
        $output = new HtmlOutput();
        $output->setHelper($helper);
        $obj = $this;
        return new StreamedResponse(function () use ($helper, $output, $package, $obj, $pName) {
            if (class_exists($package)) {
                $helper->out('restarting ' . $pName . ' if capable<br>');
                /** @var ProcessInterface $instance */
                $instance = new $package();
                $helper->out('<br><br><a href="' . $obj->generateUrl('software') . '">Return to Installer click here</a>');
                if ($instance instanceof ProcessAbstract && (!defined("$package::EXCLUDE") || $package::EXCLUDE !== false)) {
                    $instance->setOutput($output);
                    $instance->setConfig(SystemConfig::get());
                    $instance->setContainer($this->get('service_container'));
                    $instance->setIo(new SymfonyStyle(new Input(), $output));
                    $instance->restartService();
                    $helper->out('<br>done');
                }
            }
        });
    }

    /**
     * @Route("/Software/{package}/{operation}.do", name="software_do")
     */
    public function doAction(string $package, string $operation): Response
    {
        $pName = $package;
        $package = 'App\\Process\\' . $package;
        $helper = new FlushHelper();
        $output = new HtmlOutput();
        $output->setHelper($helper);
        $obj = $this;
        return new StreamedResponse(function () use ($helper, $output, $package, $operation, $obj, $pName) {
            switch ($operation) {
                case 'install':
                    if (class_exists($package)) {
                        $helper->out('Installing ' . $pName . '<br>');
                        /** @var ProcessInterface $instance */
                        $instance = new $package();
                        if ($instance instanceof ProcessAbstract && (!defined("$package::EXCLUDE") || $package::EXCLUDE !== false)) {
                            $instance->setOutput($output);
                            $instance->setConfig(SystemConfig::get());
                            $instance->setContainer($this->get('service_container'));
                            $instance->setIo(new SymfonyStyle(new Input(), $output));
                            $instance->install();
                            $instance->configure();
                            $instance->getConfig()->writeConfigs();
                            $helper->out('<br>done');
                        }
                        $helper->out('<hr>Writing fpm Configs');
                        SystemConfig::get()->writeConfigs();
                        $fpm = new PhpFpmSites();
                        $fpm->setOutput($output);
                        $fpm->setConfig(SystemConfig::get());
                        $fpm->setContainer($this->get('service_container'));
                        $fpm->setIo(new SymfonyStyle(new Input(), $output));
                        $fpm->install();
                        $fpm->configure();
                        $helper->out('<br>done');
                        $helper->out('<hr>Writing hostfile');
                        SystemConfig::get()->writeConfigs();
                        $host = new ProcessHostsFile();
                        $host->setOutput($output);
                        $host->setConfig(SystemConfig::get());
                        $host->setContainer($this->get('service_container'));
                        $host->setIo(new SymfonyStyle(new Input(), $output));
                        $host->install();
                        $host->configure();
                        $helper->out('<br>done');
                        if (SystemConfig::get()->getFeatures()->contains(Nginx::class)) {
                            $helper->out('<hr>Writing NginX Configs');
                            $nginx = new WebSitesNginx();
                            $nginx->setOutput($output);
                            $nginx->setConfig(SystemConfig::get());
                            $nginx->setContainer($this->get('service_container'));
                            $nginx->setIo(new SymfonyStyle(new Input(), $output));
                            $nginx->install();
                            $nginx->configure();
                            $helper->out('<br>done');
                        }
                        if (SystemConfig::get()->getFeatures()->contains(Apache2::class)) {
                            $helper->out('<hr>Writing Apache2 Configs');
                            $apache2 = new WebSitesApache();
                            $apache2->setOutput($output);
                            $apache2->setConfig(SystemConfig::get());
                            $apache2->setContainer($this->get('service_container'));
                            $apache2->setIo(new SymfonyStyle(new Input(), $output));
                            $apache2->install();
                            $apache2->configure();
                            $helper->out('<br>done');
                        }
                        $helper->out('<hr>');
                        $helper->out('<br><br>Execution has been Completed. Certain Services Install new Sites. If you expierence Issues accessing them (Domain not found etc.) you need to reload vagrant (vagrant reload)<br><br>');
                        $helper->out('<br<br>Newly Added Services (or uninstall/install could require to restart nginx/php-fpm. To do this click the Button on the Software Panel for Restarting these Services');
                        $helper->out('<br><br><a href="' . $obj->generateUrl('software') . '">Return to Installer click here</a>');
                    }
                    break;
                case 'uninstall':
                    if (class_exists($package)) {
                        $helper->out('Uninstalling ' . $pName . '<br>');
                        /** @var ProcessInterface $instance */
                        $instance = new $package();
                        if ($instance instanceof ProcessAbstract && (!defined("$package::EXCLUDE") || $package::EXCLUDE !== false)) {
                            $instance->setOutput($output);
                            $instance->setConfig(SystemConfig::get());
                            $instance->setContainer($this->get('service_container'));
                            $instance->setIo(new SymfonyStyle(new Input(), $output));
                            $instance->uninstall();
                            $instance->getConfig()->writeConfigs();
                            $helper->out('<br>done');
                        }
                        SystemConfig::get()->writeConfigs();
                        $helper->out('<hr>Writing fpm Configs');
                        $fpm = new PhpFpmSites();
                        $fpm->setOutput($output);
                        $fpm->setConfig(SystemConfig::get());
                        $fpm->setContainer($this->get('service_container'));
                        $fpm->setIo(new SymfonyStyle(new Input(), $output));
                        $fpm->uninstall();
                        $helper->out('<br>done');
                        $host = new ProcessHostsFile();
                        $host->setOutput($output);
                        $host->setConfig(SystemConfig::get());
                        $host->setContainer($this->get('service_container'));
                        $host->setIo(new SymfonyStyle(new Input(), $output));
                        $host->install();
                        $host->configure();
                        $helper->out('<br>done');
                        if (SystemConfig::get()->getFeatures()->contains(Nginx::class)) {
                            $helper->out('<hr>Writing NginX Configs');
                            $nginx = new WebSitesNginx();
                            $nginx->setOutput($output);
                            $nginx->setConfig(SystemConfig::get());
                            $nginx->setContainer($this->get('service_container'));
                            $nginx->setIo(new SymfonyStyle(new Input(), $output));
                            $nginx->install();
                            $nginx->configure();
                            $helper->out('<br>done');
                        }
                        if (SystemConfig::get()->getFeatures()->contains(Apache2::class)) {
                            $helper->out('<hr>Writing Apache2 Configs');
                            $apache2 = new WebSitesApache();
                            $apache2->setOutput($output);
                            $apache2->setConfig(SystemConfig::get());
                            $apache2->setContainer($this->get('service_container'));
                            $apache2->setIo(new SymfonyStyle(new Input(), $output));
                            $apache2->install();
                            $apache2->configure();
                            $helper->out('<br>done');
                        }
                        $helper->out('<br><br>Execution has been Completed. Certain Services Install new Sites. If you expierence Issues accessing them (Domain not found etc.) you need to reload vagrant (vagrant reload)<br><br>');
                        $helper->out('<br<br>Newly Added Services (or uninstall/install could require to restart nginx/php-fpm. To do this click the Button on the Software Panel for Restarting these Services');
                        $helper->out('<br><br><a href="' . $obj->generateUrl('software') . '">Return to Installer click here</a>');
                    }
                    break;
                default:
                    $this->addFlash(
                        'danger',
                        'Operation not supported!'
                    );
                    break;
            }
        });
    }
}