<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Proxy extends SiteBaseAbstract
{
    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function configure(): void
    {
        $this->printOutput('<info>generating proxy config for '.$this->getConfigSet()->getMap().'</info>',1);
        $outputFile = $this->getConfigSet()->getMap().'.vhost';
        if(!$this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/proxy.conf.twig'))
        {
            $this->printOutput('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->printOutput('<info>proxy config created</info>',1);
    }
}