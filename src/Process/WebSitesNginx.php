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
use App\Process\Sites\Symfony4;
use App\Process\Sites\Xhgui;
use App\System\Config\Site;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class WebSitesNginx
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class WebSitesNginx extends ProcessAbstract implements ProcessInterface
{
    public const EXCLUDE =  true;
    public const NGINX = [
        'Elgg'=>Elgg::class,
        'Errbit'=> Errbit::class,
        'Html'=>Html::class,
        'Laravel'=>Laravel::class,
        'PhpApp'=>PhpApp::class,
        'PhpMyAdmin'=>PhpMyAdmin::class,
        'Proxy'=>Proxy::class,
        'Silverstripe'=>SilverStripe::class,
        'Statsd'=> Statsd::class,
        'Symfony2'=>Symfony2::class,
        'Symfony4'=>Symfony4::class,
        'Xhgui'=> Xhgui::class,
    ];
    public const VERSION_TAG = 'NginX Site Generator';
    public const DEFAULT_NGINX = '
server {
	listen 80 default_server;
	listen [::]:80 default_server;
	server_name _;
	return 301 https://\$host\$request_uri;
}
    ';

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
        /** @var ArrayCollection|Site[] $sites */
        $this->execute(self::SUDO.' '.self::FIND.' '.SiteBaseAbstract::SITES_AVAILABLE.'  -type f -exec rm -f {} +');
        $this->execute(self::SUDO.' '.self::FIND.' '.SiteBaseAbstract::SITES_ENABLED.'  -type f -exec rm -f {} +');
        $this->execute('echo "'.self::DEFAULT_NGINX.'" | '.self::SUDO.' '.self::TEE.' '.SiteBaseAbstract::SITES_AVAILABLE.'default.vhost');
        $this->createLink(SiteBaseAbstract::SITES_AVAILABLE.'default.vhost', SiteBaseAbstract::SITES_ENABLED.'default.vhost');
        $sites = $this->getConfig()->getSites();
        if($sites->count() > 0)
        {
            $this->progBarInit(5);
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
                        $this->progBarAdv(5);
                    }
                    $class = self::NGINX[$site->getType()];
                    /** @var SiteBaseAbstract $inst */
                    $inst = new $class();
                    $inst->setOutput($this->getOutput());
                    $inst->setConfig($this->getConfig());
                    $inst->setConfigSet($site);
                    $inst->setContainer($this->getContainer());
                    $inst->configure();
                    $this->progBarAdv(5);
                }
            }
            $this->progBarFin();
        }

        if(!defined('POST_PONE_RELOAD') || POST_PONE_RELOAD!==true)
        {
            $this->execute(self::SERVICE_CMD.' '.Nginx::SERVICE_NAME.' '.self::SERVICE_RESTART);
        }
    }
}