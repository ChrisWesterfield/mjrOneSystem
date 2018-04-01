<?php
declare(strict_types=1);
namespace App\Process\Sites;
use App\Process\Statsd as StatsdBase;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Statsd extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating proxy config for '.$this->getConfigSet()->getMap().'</info>');
        $vars = [
            'hostname'=>$this->getConfigSet()->getMap(),
            'grafanaSrc'=> StatsdBase::GRAFANA_SRC,
            'logPath'=>$this->getConfig()->getLogDir(),
            'charSet'=>$this->getConfigSet()->getCharSet(),
            'listen'=>'127.0.0.1:'.$this->getConfigSet()->getProxyApp(),
        ];
        $access = false;
        if($this->getConfigSet()->getHttp()!==null)
        {
            $vars['port'] = $this->getConfigSet()->getHttp();
            $access = true;
        }
        if($this->getConfigSet()->getHttps()!==null)
        {
            $vars['portSSL'] = $this->getConfigSet()->getHttps();
            $access = true;
        }
        if($this->getConfigSet()->getClientMaxBodySize()!==null)
        {
            $vars['maxPost']=$this->getConfigSet()->getClientMaxBodySize();
        }
        if(!$access)
        {
            return;
        }
        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        $this->render($outputFile,'configuration/nginx/statsd.conf.twig',$vars);
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>proxy config created</info>');
    }
}