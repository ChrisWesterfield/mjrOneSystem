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
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating html only config for '.$this->getConfigSet()->getMap().'</info>');
        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        if($this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/html.conf.twig'))
        {
            $this->getOutput()->writeln('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>html only config created</info>');
    }
}