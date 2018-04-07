<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Symfony2 extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->getOutput()->writeln('<info>generating Symfony2 config for '.$this->getConfigSet()->getMap().'</info>');
        $outputFile = $this->getConfigSet()->getTo().'.vhost';
        if($this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/symfony2.conf.twig'))
        {
            $this->getOutput()->writeln('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>Symfony2 config created</info>');
    }
}