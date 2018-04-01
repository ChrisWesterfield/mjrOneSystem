<?php
declare(strict_types=1);
namespace App\Process;


use App\Process\Sites\Elgg;
use App\Process\Sites\Errbit;
use App\Process\Sites\Html;
use App\Process\Sites\Laravel;
use App\Process\Sites\PhpApp;
use App\Process\Sites\PhpMyAdmin;
use App\Process\Sites\Proxy;
use App\Process\Sites\SilverStripe;
use App\Process\Sites\SiteBaseAbstract;
use App\Process\Sites\Statsd;
use App\Process\Sites\Symfony2;
use App\Process\Sites\Xhgui;
use App\System\Config\Site;

/**
 * Class WebSitesNginx
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class WebSitesNginx extends ProcessAbstract implements ProcessInterface
{
    public const NGINX = [
        'elgg'=>Elgg::class,
        'errbit'=> Errbit::class,
        'html'=>Html::class,
        'laravel'=>Laravel::class,
        'phpApp'=>PhpApp::class,
        'phpMyAdmin'=>PhpMyAdmin::class,
        'proxy'=>Proxy::class,
        'silverstripe'=>SilverStripe::class,
        'statsd'=> Statsd::class,
        'symfony2'=>Symfony2::class,
        'symfony4'=>Symfony2::class,
        'xhgui'=> Xhgui::class,
    ];

    /**
     * @return void
     */
    public function install(): void
    {
    }

    /**
     *
     */
    public function uninstall(): void
    {
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        /** @var Site[] $sites */
        $sites = $this->getConfig()->getSites();
        if(!empty($sites))
        {
            foreach($sites as $site)
            {
                if(array_key_exists($site->getType(),self::NGINX))
                {
                    if($site->getHttps()>0 && $site->getHttps()!==null)
                    {
                        $sslInstance = new Ssl();
                        $sslInstance->setOutput($this->getOutput());
                        $sslInstance->setConfig($this->getConfig());
                        $sslInstance->setContainer($this->getContainer());
                        $sslInstance->setIo($this->getIo());
                        $sslInstance->install();
                        $sslInstance->configure();
                        $sslInstance->generateCert($site->getMap());
                    }
                    $class = self::NGINX[$site->getType()];
                    /** @var SiteBaseAbstract $inst */
                    $inst = new $class();
                    $inst->setOutput($this->getOutput());
                    $inst->setConfig($this->getConfig());
                    $inst->setConfigSet($site);
                    $inst->setContainer($this->getContainer());
                    $inst->configure();
                }
            }
        }
    }
}