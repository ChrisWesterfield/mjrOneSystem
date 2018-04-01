<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Html extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating html only config for '.$this->getConfigSet()->getMap().'</info>');
        $vars = [
            'docRoot' => $this->getConfigSet()->getTo(),
            'hostname'=>$this->getConfigSet()->getMap(),
            'logPath'=>$this->getConfig()->getLogDir(),
            'charSet'=>$this->getConfigSet()->getCharSet(),
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
        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        $this->render($outputFile,'configuration/nginx/html.conf.twig',$vars);
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>html only config created</info>');
    }
}