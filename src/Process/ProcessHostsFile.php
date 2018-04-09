<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

/**
 * Class ProcessHostsFile
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class ProcessHostsFile extends ProcessAbstract implements ProcessInterface
{
    public const HOSTS_FILE = "127.0.0.1	localhost\n127.0.1.1	schulung.test	schulung\n# The following lines are desirable for IPv6 capable hosts\n::1     localhost ip6-localhost ip6-loopback\nff02::1 ip6-allnodes\nff02::2 ip6-allrouters\n";
    public const FILENAME = '/etc/hosts';

    /**
     * @return void
     */
    public function install(): void{}

    /**
     *
     */
    public function uninstall(): void{}

    /**
     * @return mixed
     */
    public function configure(): void
    {
        if($this->getConfig()->getSites()->count() > 0)
        {
            $ip = $this->getConfig()->getIp();
            $template = self::HOSTS_FILE;
            foreach($this->getConfig()->getSites() as $site)
            {
                /** @var Site $site */
                $template.= $ip.' '.$site->getMap()."\n";
            }
            $this->execute('echo "'.$template.'" | '.self::SUDO.' '.self::TEE.' '.self::FILENAME);
        }
    }
}