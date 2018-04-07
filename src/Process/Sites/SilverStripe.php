<?php
declare(strict_types=1);
namespace App\Process\Sites;

/**
 * Class Html
 * @package App\Process\Sites
 */
class SilverStripe extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->printOutput('<info>generating silverstripe config for '.$this->getConfigSet()->getMap().'</info>',1);
        $outputFile = $this->getConfigSet()->getMap().'.vhost';
        if(!$this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/silverstripe.conf.twig'))
        {
            $this->printOutput('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->printOutput('<info>silverstripe config created</info>',1);
    }
}