<?php
declare(strict_types=1);
namespace App\Process\Sites;
use App\Process\Errbit as ErrbitBase;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Errbit extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating errbit config for '.$this->getConfigSet()->getMap().'</info>');
        $vars = [
            'docRoot' => ErrbitBase::HOME.'/public',
            'runDir'=> ErrbitBase::RUN,
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
        if($this->getConfigSet()->getClientMaxBodySize()!==null)
        {
            $vars['maxPost']=$this->getConfigSet()->getClientMaxBodySize();
        }
        if(!$access)
        {
            return;
        }
        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        $this->render($outputFile,'configuration/nginx/errbit.conf.twig',$vars);
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>errbit config created</info>');
    }
}