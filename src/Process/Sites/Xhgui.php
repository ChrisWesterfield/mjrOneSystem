<?php
declare(strict_types=1);
namespace App\Process\Sites;
use App\Process\XhGui as XhGuiBase;

/**
 * Class Html
 * @package App\Process\Sites
 */
class Xhgui extends SiteBaseAbstract
{
    /**
     *
     */
    public function configure(): void
    {
        $this->printOutput('<info>generating Xhgui config for '.$this->getConfigSet()->getMap().'</info>',1);
        $outputFile = $this->getConfigSet()->getMap().'.vhost';
        if(!$this->getNginx(self::SITES_AVAILABLE.$outputFile, 'configuration/nginx/xhgui.conf.twig'))
        {
            $this->printOutput('<error>Could not generate File!</error>');
            return;
        }
        $this->createLink(self::SITES_AVAILABLE.$outputFile,self::SITES_ENABLED.'20.'.$outputFile);
        $this->printOutput('<info>Xhgui config created</info>',1);
    }
}