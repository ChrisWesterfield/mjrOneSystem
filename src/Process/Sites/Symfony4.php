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

        $outputFile = $this->getConfigSet()->getMap().'.vhost';
        if(!$this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/symfony4.conf.twig'))
        {
            $this->getOutput()->writeln('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->getOutput()->writeln('<info>Symfony4 config created</info>');
    }
}