<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 02.04.18
 * Time: 17:29
 */

namespace App\Process;


use App\System\Config\Fpm;
use App\System\SystemConfig;

class PhpFpmSites extends ProcessAbstract implements ProcessInterface
{
    public const EXCLUDE = true;
    public const TEMPLATE = 'configuration/php/fpm.conf.twig';
    public const DELETE = self::SUDO . ' find /etc/php/%s/fpm/pool.d/ -type f -not -name \'www.conf\'  -delete';
    public const VERSION_TAG = 'PHP FPM Site Generator';
    /**
     * @return void
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
     * @return mixed
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function configure(): void
    {
        $this->progBarInit(((5 * 4) + ($this->getConfig()->getFpm()->count() * 5)));
        //remove existing sites
        if ($this->getConfig()->getFeatures()->contains(Php72::class)) {
            $this->execute(sprintf(self::DELETE, Php72::VERSION));
            $this->progBarAdv(5);
        }
        if ($this->getConfig()->getFeatures()->contains(Php71::class)) {
            $this->execute(sprintf(self::DELETE, Php71::VERSION));
            $this->progBarAdv(5);
        }
        if ($this->getConfig()->getFeatures()->contains(Php70::class)) {
            $this->execute(sprintf(self::DELETE, Php70::VERSION));
            $this->progBarAdv(5);
        }
        if ($this->getConfig()->getFeatures()->contains(Php56::class)) {
            $this->execute(sprintf(self::DELETE, Php56::VERSION));
            $this->progBarAdv(5);
        }
        //add defined sites
        if ($this->getConfig()->getFpm()->count() > 0) {
            foreach ($this->getConfig()->getFpm() as $fpm) {
                /** @var Fpm $fpm */
                $listen = $fpm->getListen();
                if ($listen === '127.0.0.1') {
                    $listen .= ':' . $fpm->getPort();
                }
                $vars = [
                    'name' => $fpm->getName(),
                    'user' => $fpm->getUser(),
                    'group' => $fpm->getGroup(),
                    'listen' => $listen,
                    'pm' => $fpm->getPm(),
                    'pmc' => [],
                    'displayError' => ($fpm->isDisplayError() ? 'on' : 'off'),
                    'xdebug' => ($fpm->isXdebug() ? 'on' : 'off'),
                    'logPath' => self::VAGRANT_HOME . '/log',
                    'logErrors' => ($fpm->isLogError() ? 'on' : 'off'),
                    'maxRam' => $fpm->getMaxRam(),
                    'flags' => [],
                    'values' => [],
                ];
                if ($fpm->getPm() === Fpm::DYNAMIC) {
                    $vars['pmc']['pm.max_children'] = $fpm->getMaxChildren();
                    $vars['pmc']['pm.start_servers'] = $fpm->getStart();
                    $vars['pmc']['pm.min_spare_servers'] = $fpm->getMinSpare();
                    $vars['pmc']['pm.max_spare_servers'] = $fpm->getMaxSpare();
                    $vars['pmc']['pm.process_idle_timeout'] = $fpm->getProcessIdleTimeOut();
                    $vars['pmc']['pm.max_requests'] = $fpm->getMaxRequests();
                } else
                    if ($fpm->getPm() === Fpm::ONDEMAND) {
                        $vars['pmc']['pm.max_children'] = $fpm->getMaxChildren();
                        $vars['pmc']['pm.process_idle_timeout'] = $fpm->getProcessIdleTimeOut();
                        $vars['pmc']['pm.max_requests'] = $fpm->getMaxRequests();
                    } else
                        if ($fpm->getPm() === Fpm::STATIC) {
                            $vars['pmc']['pm.max_children'] = $fpm->getMaxChildren();
                            $vars['pmc']['pm.max_requests'] = $fpm->getMaxRequests();
                        }
                $vars['flags'] = $fpm->getFlags();
                $vars['values'] = $fpm->getValues();
                $rendered = $this->getContainer()->get('twig')->render(self::TEMPLATE, $vars);
                $file = '/etc/php/' . $fpm->getVersion() . '/fpm/pool.d/';
                if (is_dir($file)) {
                    $this->printOutput('creating Output file ' . $file . $fpm->getName() . '.conf', 1);
                    $this->execute('echo "' . $rendered . '" | ' . self::SUDO . ' ' . self::TEE . ' ' . $file . $fpm->getName() . '.conf');
                    $this->progBarAdv(5);
                }
            }
        }
        if(!defined('POST_PONE_RELOAD') || POST_PONE_RELOAD!==true)
        {
            if ($this->getConfig()->getFeatures()->contains(Php72::class)) {
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php71::class)) {
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php70::class)) {
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if ($this->getConfig()->getFeatures()->contains(Php56::class)) {
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
        }
        $this->progBarFin();
    }
}