<?php
declare(strict_types=1);
namespace App\Twig;
use App\Services\SystemConfig;
use App\System\Config\Site;
use App\System\Monitoring;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Twig_Extension;

/**
 * Class TwigExtension
 * @package App\Twig
 * @author chris westerfield <chris@mjr.one>
 */
class TwigExtension extends Twig_Extension
{
    /**
     * @var SystemConfig
     */
    protected $sConfig;

    /**
     * @var Router
     */
    protected $router;

    /**
     * TwigExtension constructor.
     * @param SystemConfig $config
     */
    public function __construct(SystemConfig $config, Router $route)
    {
        $this->sConfig = $config;
        $this->router  = $route;
    }
    /**
     * @return array
     */
    public function getFunctions():array
    {
        $monit = new Monitoring();
        return [
            new \Twig_SimpleFunction(
                'checkStatus',
                [
                    $monit,
                    'checkStatus'
                ]
            ),
            new \Twig_SimpleFunction(
                'services',
                [
                    $this->sConfig,
                    'getServiceArray'
                ]
            ),
            new \Twig_SimpleFunction(
                'getIp',
                [
                    $this,
                    'getIp'
                ]
            ),
            new \Twig_SimpleFunction(
                'getCpus',
                [
                    $this,
                    'getCpu'
                ]
            ),
            new \Twig_SimpleFunction(
                'getMemory',
                [
                    $this,
                    'getMemory'
                ]
            ),
            new \Twig_SimpleFunction(
                'getProvider',
                [
                    $this,
                    'getProvider'
                ]
            ),
            new \Twig_SimpleFunction(
                'getSites',
                [
                    $this,
                    'getSites'
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public function getSites():array
    {
        $return = [
            Site::CATEGORY_APP => [],
            Site::CATEGORY_INFO => [],
            Site::CATEGORY_ADMIN => [],
            Site::CATEGORY_STATISTICS => [],
            Site::CATEGORY_OTHER => [],
            Site::CATEGORY_EXTERNAL => [],
        ];
        if($this->sConfig->getConfig()->getSites()->count() > 0)
        {
            foreach($this->sConfig->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                $desc = $site->getDescription();
                $url = 'http'.($site->getHttps()>0?'s':'').'://'.$site->getMap();
                switch ($site->getFpm())
                {
                    case 'admin.info56':
                        $url = $this->router->generate('info56');
                    break;
                    case 'admin.info70':
                        $url = $this->router->generate('info70');
                    break;
                    case 'admin.info71':
                        $url = $this->router->generate('info71');
                    break;
                    case 'admin.info72':
                        $url = $this->router->generate('info72');
                    break;
                }
                if($desc==='Startpage')
                {
                    continue;
                }
                $return[$site->getCategory()][] = [
                    'url'=>$url,
                    'title'=>(!empty($desc)?$desc:$url),
                ];
            }
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getIp():string
    {
        return (string)$this->sConfig->getConfig()->getIp();
    }

    /**
     * @return string
     */
    public function getCpu():string
    {
        return (string)$this->sConfig->getConfig()->getCpus();
    }

    /**
     * @return string
     */
    public function getMemory():string
    {
        return (string)$this->sConfig->getConfig()->getMemory();
    }

    /**
     * @return string
     */
    public function getProvider():string
    {
        return (string)$this->sConfig->getConfig()->getProvider();
    }
}