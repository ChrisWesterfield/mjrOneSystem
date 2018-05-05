<?php
declare(strict_types=1);
namespace App\Controller;


use App\Process\ProcessInterface;
use App\Services\SystemConfig;
use App\System\Config\Site;
use App\System\SystemConfig as SysConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StartController
 * @package App\Controller
 * @author chris westerfield <chris@mjr.one>
 */
class StartController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @Route("/", name="index")
     */
    public function indexAction(Request $request):Response
    {
        return $this->render(
            'index.html.twig',
            [

            ]
        );
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/user", name="users")
     */
    public function userAction(Request $request):Response
    {
        $sjson = file_get_contents(ProcessInterface::VAGRANT_USER_DIR.'/user.json');
        $json = Yaml::parse($sjson);
        $json['rabbitmq Admin'] = [
            'website'=> 'https://rmqa.'.SysConfig::get()->getName(),
            'username'=>'guest',
            'password'=>'guest',
        ];
        $json['errbit']['website'] = 'https://errbit'.SysConfig::get()->getName();
        $dbs = SysConfig::get()->getDatabases();
        $dbConfig = [];
        foreach($dbs as $db)
        {
            /** @var \App\System\Config\Database $db */
            $row = [
                'db'=>$db->getName(),
            ];
            foreach($db->getUserList() as $ul)
            {
                /** @var \App\System\Config\DbUser $ul */
                $row['user'] = $ul->getUsername();
                $row['pass'] = $ul->getPassword();
                $row['perm'] = $ul->getReadOnly();
                $dbConfig[] = $row;
            }
        }
        return $this->render(
            'user.html.twig',
            [
                'json'=>$json,
                'db'=>$dbConfig,
            ]
        );
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/info56", name="info56")
     */
    public function phpInfo56Action()
    {
        /** @var SystemConfig $cfg */
        $cfg = $this->get('app.services.sys_config');
        $content = '<h1>Php 5.6 not installed!</h1>';
        if($cfg->getConfig()->getSites()->count() > 0)
        {
            $url = '';
            foreach($cfg->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                if($site->getFpm()==='admin.info56')
                {
                    $url = 'http'.($site->getHttps() > 0?'s':'').'://'.$site->getMap();
                    break;
                }
            }
            if(!empty($url))
            {
                $s = curl_init();
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($s, CURLOPT_FOLLOWLOCATION, $url);
                curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
                $body = curl_exec($s);
                $regex = '#<\s*?body\b[^>]*>(.*?)</body\b[^>]*>#s';
                preg_match($regex, $body, $content);
                $content = $content[1];
            }
        }
        return $this->render('info.html.twig', ['info'=>$content]);
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/info70", name="info70")
     */
    public function phpInfo70Action()
    {
        /** @var SystemConfig $cfg */
        $cfg = $this->get('app.services.sys_config');
        $content = '<h1>Php 7.0 not installed!</h1>';
        if($cfg->getConfig()->getSites()->count() > 0)
        {
            $url = '';
            foreach($cfg->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                if($site->getFpm()==='admin.info70')
                {
                    $url = 'http'.($site->getHttps() > 0?'s':'').'://'.$site->getMap();
                    break;
                }
            }
            if(!empty($url))
            {
                $s = curl_init();
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($s, CURLOPT_FOLLOWLOCATION, $url);
                curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
                $body = curl_exec($s);
                $regex = '#<\s*?body\b[^>]*>(.*?)</body\b[^>]*>#s';
                preg_match($regex, $body, $content);
                $content = $content[1];
            }
        }
        return $this->render('info.html.twig', ['info'=>$content]);
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/info71", name="info71")
     */
    public function phpInfo71Action()
    {
        /** @var SystemConfig $cfg */
        $cfg = $this->get('app.services.sys_config');
        $content = '<h1>Php 7.1 not installed!</h1>';
        if($cfg->getConfig()->getSites()->count() > 0)
        {
            $url = '';
            foreach($cfg->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                if($site->getFpm()==='admin.info71')
                {
                    $url = 'http'.($site->getHttps() > 0?'s':'').'://'.$site->getMap();
                    break;
                }
            }
            if(!empty($url))
            {
                $s = curl_init();
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($s, CURLOPT_FOLLOWLOCATION, $url);
                curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
                $body = curl_exec($s);
                $regex = '#<\s*?body\b[^>]*>(.*?)</body\b[^>]*>#s';
                preg_match($regex, $body, $content);
                $content = $content[1];
            }
        }
        return $this->render('info.html.twig', ['info'=>$content]);
    }
    /**
     * @param Request $request
     * @return Response
     * @Route("/info72", name="info72")
     */
    public function phpInfo72Action()
    {
        /** @var SystemConfig $cfg */
        $cfg = $this->get('app.services.sys_config');
        $content = '<h1>Php 7.2 not installed!</h1>';
        if($cfg->getConfig()->getSites()->count() > 0)
        {
            $url = '';
            foreach($cfg->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                if($site->getFpm()==='admin.info72')
                {
                    $url = 'http'.($site->getHttps() > 0?'s':'').'://'.$site->getMap();
                    break;
                }
            }
            if(!empty($url))
            {
                $s = curl_init();
                curl_setopt($s, CURLOPT_URL, $url);
                curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($s, CURLOPT_FOLLOWLOCATION, $url);
                curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
                $body = curl_exec($s);
                $regex = '#<\s*?body\b[^>]*>(.*?)</body\b[^>]*>#s';
                preg_match($regex, $body, $content);
                $content = $content[1];
            }
        }
        return $this->render('info.html.twig', ['info'=>$content]);
    }
}