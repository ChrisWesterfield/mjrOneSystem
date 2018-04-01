<?php
declare(strict_types=1);
namespace App\Process\Sites;


use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;
use App\System\Config\Site;

/**
 * Class SiteBaseAbstract
 * @package App\Process\Sites
 */
abstract class SiteBaseAbstract extends ProcessAbstract implements ProcessInterface
{
    public const SITES_AVAILABLE = '/etc/nginx/sites-available/';
    public const SITES_ENABLED = '/etc/nginx/sites-enabled/';
    /**
     * @var Site
     */
    protected $configSet;

    /**
     * @return Site
     */
    public function getConfigSet(): Site
    {
        return $this->configSet;
    }

    /**
     * @param Site $configSet
     * @return SiteBaseAbstract
     */
    public function setConfigSet(Site $configSet): SiteBaseAbstract
    {
        $this->configSet = $configSet;
        return $this;
    }
    /**
     *
     */
    public function install(): void
    {
    }

    /**
     *
     */
    public function uninstall(): void
    {
    }

    /**
     *
     */
    abstract public function configure(): void;

    /**
     * @param $file
     * @param $template
     * @param $variables
     */
    protected function render($file, $template, $variables):void
    {
        $rendered = $this->getContainer()->get('twig')->render($template, $variables);
        @file_put_contents(self::SITES_AVAILABLE.$file, $rendered);
    }
}