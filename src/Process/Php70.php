<?php
declare(strict_types=1);
namespace App\Process;


use App\System\Config\Fpm;

/**
 * Class Php70
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Php70 extends ProcessAbstract implements ProcessInterface
{
    public const VERSION = '7.0';
    public const SERVICE_NAME = 'php'.self::VERSION.'-fpm';
    public const DIR_MODS_AVAILABLE = '/etc/php/'.self::VERSION.'/mods-available/';
    public const DIR_CONF_CLI = '/etc/php/'.self::VERSION.'/cli/conf.d/';
    public const DIR_CONF_FPM = '/etc/php/'.self::VERSION.'/fpm/conf.d/';
    public const DIR_POOL = '/etc/php/'.self::VERSION.'/fpm/pool.d/';
    public const OPCACHE_FILE = 'opcache.ini';
    public const MJRONE_FILE = 'mjrone.ini';
    public const SOFTWARE = [
        'php'.self::VERSION.'-common',
        'php'.self::VERSION.'-bcmath',
        'php'.self::VERSION.'-cli',
        'php'.self::VERSION.'-dev',
        'php'.self::VERSION.'-enchant',
        'php'.self::VERSION.'-curl',
        'php'.self::VERSION.'-imap',
        'php'.self::VERSION.'-gd',
        'php'.self::VERSION.'-intl',
        'php'.self::VERSION.'-json',
        'php'.self::VERSION.'-mbstring',
        'php'.self::VERSION.'-opcache',
        'php'.self::VERSION.'-pspell',
        'php'.self::VERSION.'-readline',
        'php'.self::VERSION.'-recode',
        'php'.self::VERSION.'-soap',
        'php'.self::VERSION.'-sqlite3',
        'php'.self::VERSION.'-tidy',
        'php'.self::VERSION.'-xml',
        'php'.self::VERSION.'-xmlrpc',
        'php'.self::VERSION.'-zip',
        'php'.self::VERSION.'-xsl',
        'php'.self::VERSION.'-fpm',
    ];
    public const REQUIREMENTS = [];
    public const VERSION_TAG = 'php70';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(35);
            $mjrone = file_get_contents(__DIR__.'/../../templates/configuration/php/config/mjrone.conf');
            $this->progBarAdv(5);
            $this->execute('echo "'.$mjrone.'" | '.self::SUDO.' '.self::TEE.' '.self::DIR_MODS_AVAILABLE.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $opcache = file_get_contents(__DIR__.'/../../templates/configuration/php/config/opcache.conf');
            $this->progBarAdv(5);
            $this->execute('echo "'.$opcache.'" | '.self::SUDO.' '.self::TEE.' '.self::DIR_MODS_AVAILABLE.self::OPCACHE_FILE);
            $this->progBarAdv(5);
            $this->createLink(self::DIR_MODS_AVAILABLE.self::MJRONE_FILE,self::DIR_CONF_CLI.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->createLink(self::DIR_MODS_AVAILABLE.self::MJRONE_FILE,self::DIR_CONF_FPM.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            if(
                $this->getConfig()->getFeatures()->contains(Maria::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL56::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL57::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL8::class)
            )
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.self::VERSION.'-mysql');
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(PostgreSQL::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.self::VERSION.'-pgsql');
                $this->progBarAdv(5);
            }
            $this->execute(self::SERVICE_CMD.' '.self::SERVICE_NAME.' '.self::SERVICE_RESTART);
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(65);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            if(
                $this->getConfig()->getFeatures()->contains(Maria::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL56::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL57::class)
                ||
                $this->getConfig()->getFeatures()->contains(MySQL8::class)
            )
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.self::VERSION.'-mysql');
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(PostgreSQL::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.self::VERSION.'-pgsql');
                $this->progBarAdv(5);
            }
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(15);
            unlink(self::INSTALLED_APPS_STORE. self::VERSION_TAG);
            $this->progBarAdv(5);
            unlink(self::DIR_CONF_FPM.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            unlink(self::DIR_CONF_CLI.'20-'.self::MJRONE_FILE);
            $this->progBarAdv(5);
            unlink(self::DIR_MODS_AVAILABLE.self::MJRONE_FILE);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     * @param $file
     * @param $template
     * @param $variables
     */
    protected function render($file, $template, $variables):void
    {
        $rendered = $this->getContainer()->get('twig')->render($template, $variables);
        @file_put_contents(self::DIR_POOL.$file, $rendered);
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        if($this->getConfig()->getFpm()->count() > 0)
        {
            $this->progBarInit(($this>$this->getConfig()->getFpm()->count()*5));
            foreach($this->getConfig()->getFpm() as $fpm)
            {
                /** @var Fpm $fpm */
                if($fpm->getVersion() === self::VERSION)
                {
                    $vars = [
                        'name'=>$fpm->getName(),
                        'user'=>$fpm->getUser(),
                        'group'=>$fpm->getGroup(),
                        'pm'=>$fpm->getPm(),
                        'xdebug'=>($fpm->isXdebug()?'yes':'no'),
                        'logPath'=>$this->getConfig()->getLogDir(),
                        'maxRam'=>$fpm->getMaxRam(),
                        'flags'=>(!empty($fpm->getFlags())?$fpm->getFlags():[]),
                        'values'=>(!empty($fpm->getValues())?$fpm->getValues():[]),
                    ];
                    switch ($fpm->getPm())
                    {
                        default:
                        case 'dynamic':
                            $vars['pmc'] = [
                                'pm.max_children'=>$fpm->getMaxChildren(),
                                'pm.start_servers'=>$fpm->getStart(),
                                'pm.min_spare_servers'=>$fpm->getMinSpare(),
                                'pm.max_spare_servers'=>$fpm->getMaxSpare(),
                            ];
                            break;
                        case 'static':
                            $vars['pmc'] = [
                                'pm.start_servers'=>$fpm->getStart(),
                            ];
                            break;
                        case 'ondemand':
                            $vars['pmc'] = [
                                'pm.max_children'=>$fpm->getMaxChildren(),
                                'pm.process_idle_timeout'=>$fpm->getProcessIdleTimeOut(),
                                'pm.max_requests'=>$fpm->getMaxRequests(),
                            ];
                            break;
                    }
                    $this->render($fpm->getName().'.conf','configure/php/fpm.conf.twig',$vars);
                    $this->progBarAdv(5);
                }
            }
            $this->progBarFin();
        }
    }
}