<?php
declare(strict_types=1);
namespace App\Process;


use App\Process\Sites\Apache;
use App\Process\Sites\PimCore;
use App\Process\Sites\SiteBaseAbstract;
use App\System\Config\Site;

/**
 * Class WebSitesApache
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class WebSitesApache extends ProcessAbstract implements ProcessInterface
{
    public const APACHE = [
        'apache'=>Apache::class,
        'pimcore'=> PimCore::class,
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
                if(array_key_exists($site->getType(),self::APACHE))
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
                    $class = self::APACHE[$site->getType()];
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