<?php
declare(strict_types=1);
namespace App\Process;


/**
 * Class MasterSlaveSetup
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class MasterSlaveSetup extends ProcessAbstract implements ProcessInterface
{
    public const VERSION_TAG = 'masterSlave';
    public const DESCRIPTION = 'Master Slave Servers';
    public const TEMPLATE = '[Unit]
Description=MySQL Server %%%ID%%%
After=syslog.target
After=network.target

[Service]
Type=simple
PermissionsStartOnly=true
ExecStart=/usr/sbin/mysqld  --basedir=/usr --datadir=/var/lib/mysql/%%%DIR%%% --plugin-dir=/usr/lib/mysql/plugin --log-error=/var/log/mysql/error.log --pid-file=/tmp/mysqld%%%ID%%%.pid  --socket=/tmp/mysql%%%ID%%%.sock --port=3307 --server-id=%%%ID%%% --log-bin=/var/log/mysql/%%%DIR%%%/mysql-bin.log --relay-log=/var/log/mysql/%%%DIR%%%/mysql-relay.log 
TimeoutSec=300
PrivateTmp=true
User=mysql
Group=mysql
WorkingDirectory=/usr

[Install]
WantedBy=multi-user.target';


    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.
                           ' '.
                           self::SERVICE.
                           ' '.
                           MySQL56::SERVICE_NAME.
                           ' '.
                           self::SERVICE_STOP);
            $this->execute(self::SUDO.
                           ' mkdir /var/lib/mysql/0');
            $this->execute(self::SUDO.
                           ' '.
                           self::MV.
                           ' /var/lib/mysql/0/* /var/lib/mysql/0');
            $this->execute(self::SUDO.
                           ' '.
                           self::CHMOD.
                           ' /var/lib/mysql/0');
            $content = file_get_contents('/etc/mysql/mysql.conf.d/mysqld.cnf');
            $content = str_replace('datadir		= /var/lib/mysql', 'datadir		= /var/lib/mysql/0', $content);
            $this->execute('echo "'.
                           $content.
                           '" | '.
                           self::SUDO.
                           ' '.
                           self::TEE);

        }
    }
    /**
     *
     */
    public function uninstall()
    : void
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @return mixed
     */
    public function configure()
    : void
    {
        // TODO: Implement configure() method.
    }
}