<?php
declare(strict_types=1);
namespace App\Process\Sites;


use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\Process\Statsd as StatsdSite;
use App\Process\Errbit as ErrbitSite;
use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class SiteBaseAbstract
 * @package App\Process\Sites
 */
abstract class SiteBaseAbstract extends ProcessAbstract implements ProcessInterface
{
    public const SITES_AVAILABLE = '/etc/nginx/sites-available/';
    public const SITES_ENABLED = '/etc/nginx/sites-enabled/';
    /**
     * @var Site
     */
    protected $configSet;

    /**
     * @return Site
     */
    public function getConfigSet(): Site
    {
        return $this->configSet;
    }

    /**
     * @param Site $configSet
     * @return SiteBaseAbstract
     */
    public function setConfigSet(Site $configSet): SiteBaseAbstract
    {
        $this->configSet = $configSet;
        return $this;
    }
    /**
     *
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
     *
     */
    abstract public function configure(): void;

    /**
     * @param string $file
     * @param string $template
     * @return bool
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function getNginx(string $file, string $template)
    {
        $access = false;
        /** @var Site $site */
        $site = $this->getConfigSet();
        $vars = [
            'docRoot' => $site->getTo(),
            'hostname'=>$site->getMap(),
            'logPath'=>$this->getConfig()->getLogDir(),
            'charSet'=>$site->getCharSet(),
            'params'=>$site->getFcgiParams(),
            'zendServer'=>$site->isZray(),
            'maxPost'=>$site->getClientMaxBodySize(),
            'fcgiBufferSize'=>$site->getFcgiBufferSize(),
            'fcgiBuffer'=>$site->getFcgiBuffer(),
            'fcgiBusyBufferSize'=>$site->getFcgiBusyBufferSize(),
            'fcgiConnectionTimeOut'=>$site->getFcgiConnectionTimeOut(),
            'fcgiSendTimeOut'=>$site->getFcgiSendTimeOut(),
            'fcgiReadTimeOut'=>$site->getFcgiReadTimeOut(),
        ];
        if($site->getHttp()!==null)
        {
            $vars['port'] = $site->getHttp();
            $access = true;
        }
        if($site->getHttps()!==null)
        {
            $vars['portSSL'] = $site->getHttps();
            $access = true;
        }
        if(!$access)
        {
            return false;
        }
        $fpm = $site->getFpm();
        if(!empty($fpm) && $this->getConfig()->getFpm()->containsKey($fpm))
        {
            /** @var Fpm $fpmd */
            $fpmd = $this->getConfig()->getFpm()->get($fpm);
            if($fpmd->getPort() > 0)
            {
                $vars['listen'] = $fpmd->getListen().':'.$fpmd->getPort();
                $vars['listen'] = str_replace(':'.$fpmd->getPort().':'.$fpmd->getPort(),':'.$fpmd->getPort(), $vars['listen']);
            }
            else
            {
                $vars['listen'] = 'unix:'.$fpmd->getListen();
            }
        }else{
            if($site->getListen()!==null)
            {
                $vars['listen'] = $site->getListen();
            }
        }
        if(get_class($this)===Statsd::class)
        {
            $vars['grafanaSrc'] = StatsdSite::GRAPHITE_WEB;
        }
        if(get_class($this)===Errbit::class)
        {
            $vars['runDir'] = ErrbitSite::HOME.'/run';
        }
        $this->render($file, $template, $vars);
        return true;
    }

    /**
     * @param $file
     * @param $template
     * @param $variables
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render($file, $template, $variables):void
    {
        $rendered = $this->getContainer()->get('twig')->render($template, $variables);
        $this->execute('echo "'.$rendered.'" | '.self::SUDO.' '.self::TEE.' '.$file);
    }
}