<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Symfony4 extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating Symfony4 config for '.$this->getConfigSet()->getMap().'</info>');
        $vars = [
            'docRoot' => $this->getConfigSet()->getTo(),
            'hostname'=>$this->getConfigSet()->getMap(),
            'logPath'=>$this->getConfig()->getLogDir(),
            'charSet'=>$this->getConfigSet()->getCharSet(),
            'listen'=>$this->getConfigSet()->getFpm(),
            'params'=>$this->getConfigSet()->getFcgiParams(),
            'zendServer'=>$this->getConfigSet()->isZray(),
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
        if(!$access)
        {
            return;
        }

        if($this->getConfigSet()->getClientMaxBodySize()!==null)
        {
            $vars['maxPost']=$this->getConfigSet()->getClientMaxBodySize();
        }
        if($this->getConfigSet()->getFcgiBufferSize()!==null)
        {
            $vars['fcgiBufferSize']=$this->getConfigSet()->getFcgiBufferSize();
        }
        if($this->getConfigSet()->getFcgiBuffer()!==null)
        {
            $vars['fcgiBuffer']=$this->getConfigSet()->getFcgiBuffer();
        }

        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        $this->render($outputFile,'configuration/nginx/symfony4.conf.twig',$vars);
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>Symfony4 config created</info>');
    }
}