<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class PimCore extends SiteBaseAbstract
{
    public const SITES_AVAILABLE = '/etc/apache/sites-available/';
    public const SITES_ENABLED = '/etc/apache/sites-enabled/';
    /**
     *
     */
    public function configure(): void
    {
        $this->printOutput('<info>generating pimcore config for '.$this->getConfigSet()->getMap().'</info>',1);
        $vars = [
            'docRoot' => $this->getConfigSet()->getTo(),
            'hostname'=>$this->getConfigSet()->getMap(),
            'logPath'=>$this->getConfig()->getLogDir(),
            'params'=>$this->getConfigSet()->getFcgiParams(),
            'hostmaster'=>$this->getConfig()->getName(),
        ];
        $access = false;
        if($this->getConfigSet()->getHttp()!==null)
        {
            $vars['port'] = $this->getConfigSet()->getProxyApp();
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
        $outputFile = $this->getConfigSet()->getMap().'.conf';
        $this->render($outputFile,'configuration/apache/apache.conf.twig',$vars);
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);

        $proxy = new Proxy();
        $proxy->setOutput($this->getOutput());
        $proxy->setContainer($this->getContainer());
        $proxy->setConfig($this->getConfig());
        $proxy->setConfigSet($this->getConfigSet());
        $proxy->configure();
        $this->printOutput('<info>pimcore config created</info>',1);
    }
}