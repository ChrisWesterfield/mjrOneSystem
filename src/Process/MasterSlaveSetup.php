<?php
declare(strict_types=1);
namespace App\Process;

use Symfony\Component\Process\Process;


/**
 * Class MasterSlaveSetup
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class MasterSlaveSetup extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        MySQL57::class,
    ];
    public const DEFAULT_PASSWORD = '123';
    public const DEFAULT_ADMIN = 'root';
    public const DEFAULT_HOST = '127.0.0.1';
    public const PORT_MASTER = 3306;
    public const VERSION_TAG = 'masterSlave';
    public const DESCRIPTION = 'Master Slave Servers';
    public const DIR_MYSQL_LIB = '/var/lib/mysql';
    public const MYSQLD_BIN = '/usr/sbin/mysqld';
    public const SYSTEMD_DIR = '/lib/systemd/system';
    public const FILE_SYSTEMD = 'mysql%s.service';
    public const MYSQL_ADMIN = '/usr/bin/mysqladmin';
    public const MYSQL_BIN = '/usr/bin/mysql';
    public const LOG_DIR = '/var/log/mysql/%s';
    public const MYSQL_SETPASSWORD = [
        self::MYSQL_BIN.' --user="'.self::DEFAULT_ADMIN.'" -h '.self::DEFAULT_HOST.' -P %s -e "CREATE USER \''.self::DEFAULT_ADMIN.'\'@\'##host##\' IDENTIFIED BY \''.self::DEFAULT_PASSWORD.'\';"',
        self::MYSQL_BIN.' --user="'.self::DEFAULT_ADMIN.'" -h '.self::DEFAULT_HOST.' -P %s -e "GRANT ALL PRIVILEGES ON *.* to \''.self::DEFAULT_ADMIN.'\'@\'##host##\'  WITH GRANT OPTION;"',
        self::MYSQL_BIN.' --user="'.self::DEFAULT_ADMIN.'" -h '.self::DEFAULT_HOST.' -P %s -e "ALTER USER \''.self::DEFAULT_ADMIN.'\'@\'localhost\' IDENTIFIED BY \''.self::DEFAULT_PASSWORD.'\'"',
    ];
    public const MYSQL_GRANT_REPLICATION = self::MYSQL_BIN.' mysql -u'.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "GRANT ALL PRIVILEGES ON *.* TO '.self::DEFAULT_ADMIN.'@\'%\' IDENTIFIED BY \''.self::DEFAULT_PASSWORD.'\';"';
    public const CMD_POSITION = self::MYSQL_BIN.' -u '.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "show master status \G" | awk \'/Position/  {print $2}\'';
    public const CMD_FILE = self::MYSQL_BIN.' -u '.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "show master status \G" | awk \'/File/  {print $2}\'';
    public const SLAVE_ON = self::MYSQL_BIN.' -u '.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "CHANGE MASTER TO MASTER_HOST=\''.self::DEFAULT_HOST.'\', MASTER_PORT=%s,MASTER_USER=\''.self::DEFAULT_ADMIN.'\', MASTER_PASSWORD=\''.self::DEFAULT_PASSWORD.'\', MASTER_LOG_FILE=\'%s\', MASTER_LOG_POS=  %s;"';
    public const SLAVE_START = self::MYSQL_BIN.' -u'.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "START SLAVE;"';
    public const SLAVE_STATUS = self::MYSQL_BIN.' -u'.self::DEFAULT_ADMIN.' -p'.self::DEFAULT_PASSWORD.' -h '.self::DEFAULT_HOST.' -P %s -e "SHOW SLAVE STATUS\G;"';
    public const MYSQL_USERGROUP = 'mysql:mysql';
    public const TEMPLATE = '[Unit]
Description=MySQL Server %s
After=syslog.target
After=network.target

[Service]
Type=simple
PermissionsStartOnly=true
ExecStart=/usr/sbin/mysqld  --basedir=/usr --datadir=/var/lib/mysql/%s --plugin-dir=/usr/lib/mysql/plugin --log-error=/var/log/mysql/error.log --pid-file=/tmp/mysqld%s.pid  --socket=/tmp/mysql%s.sock --port=%s --server-id=%s --log-bin=/var/log/mysql/%s/mysql-bin.log --relay-log=/var/log/mysql/%s/mysql-relay.log 
TimeoutSec=300
PrivateTmp=true
User=mysql
Group=mysql
WorkingDirectory=/usr

[Install]
WantedBy=multi-user.target';

    public const PHPMYADMIN_CORE_TEMPLATE = '<?php
$cfg[\'blowfish_secret\'] = \'r6EA8vX7c6LRoAykpCgGHxV6RvJm6QQNkdifRpG4xWkxUmZjxZxaV7RakMjj2Y2m\';
$i = 0;
$cfg[\'UploadDir\'] = \'\';
$cfg[\'SaveDir\'] = \'\';
$cfg[\'PmaNoRelation_DisableWarning\'] = true;
$cfg[\'SuhosinDisableWarning\'] = true;
$cfg[\'LoginCookieValidityDisableWarning\'] = true;
$cfg[\'NavigationTreeDbSeparator\'] = \'\';
$cfg[\'ShowPhpInfo\'] = true;
$cfg[\'DefaultLang\'] = \'de\';
$cfg[\'ServerDefault\'] = 1;
$cfg[\'ThemeDefault\'] = \'fallen\';
$i++;
//Master Config
$cfg[\'Servers\'][$i][\'verbose\'] = \'Master\';
$cfg[\'Servers\'][$i][\'host\'] = \'127.0.0.1\';
$cfg[\'Servers\'][$i][\'port\'] = \'3306\';
$cfg[\'Servers\'][$i][\'socket\'] = \'\';
$cfg[\'Servers\'][$i][\'auth_type\'] = \'config\';
$cfg[\'Servers\'][$i][\'user\'] = \'root\';
$cfg[\'Servers\'][$i][\'password\'] = \'123\';
$cfg[\'Servers\'][$i][\'pmadb\'] = \'phpmyadmin\';
$cfg[\'Servers\'][$i][\'controlhost\'] = \'127.0.0.1\';
$cfg[\'Servers\'][$i][\'controlport\'] = \'3306\';
$cfg[\'Servers\'][$i][\'controluser\'] = \'root\';
$cfg[\'Servers\'][$i][\'controlpass\'] = \'123\';
$cfg[\'Servers\'][$i][\'bookmarktable\'] = \'pma__bookmark\';
$cfg[\'Servers\'][$i][\'relation\'] = \'pma__relation\';
$cfg[\'Servers\'][$i][\'userconfig\'] = \'pma__userconfig\';
$cfg[\'Servers\'][$i][\'users\'] = \'pma__users\';
$cfg[\'Servers\'][$i][\'usergroups\'] = \'pma__usergroups\';
$cfg[\'Servers\'][$i][\'navigationhiding\'] = \'pma__navigationhiding\';
$cfg[\'Servers\'][$i][\'table_info\'] = \'pma__table_info\';
$cfg[\'Servers\'][$i][\'column_info\'] = \'pma__column_info\';
$cfg[\'Servers\'][$i][\'history\'] = \'pma__history\';
$cfg[\'Servers\'][$i][\'recent\'] = \'pma__recent\';
$cfg[\'Servers\'][$i][\'favorite\'] = \'pma__favorite\';
$cfg[\'Servers\'][$i][\'table_uiprefs\'] = \'pma__table_uiprefs\';
$cfg[\'Servers\'][$i][\'tracking\'] = \'pma__tracking\';
$cfg[\'Servers\'][$i][\'table_coords\'] = \'pma__table_coords\';
$cfg[\'Servers\'][$i][\'pdf_pages\'] = \'pma__pdf_pages\';
$cfg[\'Servers\'][$i][\'savedsearches\'] = \'pma__savedsearches\';
$cfg[\'Servers\'][$i][\'central_columns\'] = \'pma__central_columns\';
$cfg[\'Servers\'][$i][\'export_templates\'] = \'pma__export_templates\';';

    public const PHPMYADMIN_TEMPLATE = '
// Slave %s Setup
$i++;
$cfg[\'Servers\'][$i][\'verbose\'] = \'SLAVE %s\';
$cfg[\'Servers\'][$i][\'host\'] = \'127.0.0.1\';
$cfg[\'Servers\'][$i][\'port\'] = \'%s\';
$cfg[\'Servers\'][$i][\'socket\'] = \'\';
$cfg[\'Servers\'][$i][\'auth_type\'] = \'config\';
$cfg[\'Servers\'][$i][\'user\'] = \'root\';
$cfg[\'Servers\'][$i][\'password\'] = \'123\';
$cfg[\'Servers\'][$i][\'pmadb\'] = \'phpmyadmin\';
$cfg[\'Servers\'][$i][\'controlhost\'] = \'127.0.0.1\';
$cfg[\'Servers\'][$i][\'controlport\'] = \'3306\';
$cfg[\'Servers\'][$i][\'controluser\'] = \'root\';
$cfg[\'Servers\'][$i][\'controlpass\'] = \'123\';
$cfg[\'Servers\'][$i][\'bookmarktable\'] = \'pma__bookmark\';
$cfg[\'Servers\'][$i][\'relation\'] = \'pma__relation\';
$cfg[\'Servers\'][$i][\'userconfig\'] = \'pma__userconfig\';
$cfg[\'Servers\'][$i][\'users\'] = \'pma__users\';
$cfg[\'Servers\'][$i][\'usergroups\'] = \'pma__usergroups\';
$cfg[\'Servers\'][$i][\'navigationhiding\'] = \'pma__navigationhiding\';
$cfg[\'Servers\'][$i][\'table_info\'] = \'pma__table_info\';
$cfg[\'Servers\'][$i][\'column_info\'] = \'pma__column_info\';
$cfg[\'Servers\'][$i][\'history\'] = \'pma__history\';
$cfg[\'Servers\'][$i][\'recent\'] = \'pma__recent\';
$cfg[\'Servers\'][$i][\'favorite\'] = \'pma__favorite\';
$cfg[\'Servers\'][$i][\'table_uiprefs\'] = \'pma__table_uiprefs\';
$cfg[\'Servers\'][$i][\'tracking\'] = \'pma__tracking\';
$cfg[\'Servers\'][$i][\'table_coords\'] = \'pma__table_coords\';
$cfg[\'Servers\'][$i][\'pdf_pages\'] = \'pma__pdf_pages\';
$cfg[\'Servers\'][$i][\'savedsearches\'] = \'pma__savedsearches\';
$cfg[\'Servers\'][$i][\'central_columns\'] = \'pma__central_columns\';
$cfg[\'Servers\'][$i][\'export_templates\'] = \'pma__export_templates\';';

    /**
     *
     */
    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG) && $this->getConfig()->getMasterCount()===1 && $this->getConfig()->getSlaveCount() >= 1) {
            $init = 60;
            if(!is_dir(self::DIR_MYSQL_LIB.'/0'))
            {
                $init +=30;
            }
            $init += (115*$this->getConfig()->getSlaveCount());
            $this->progBarInit(5);
            if (!$this->getConfig()
                      ->getFeatures()
                      ->contains(MySQL56::class) &&
                !$this->getConfig()
                      ->getFeatures()
                      ->contains(MySQL57::class) &&
                !$this->getConfig()
                      ->getFeatures()
                      ->contains(MySQL8::class)) {
                $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            }
            $this->progBarAdv(5);
            if ($this->getConfig()
                     ->getFeatures()
                     ->contains(MySQL56::class) &&
                !file_exists(self::INSTALLED_APPS_STORE.
                             MySQL56::VERSION_TAG)) {
                $this->checkRequirements(get_class($this), [MySQL56::class]);
            }
            $this->progBarAdv(5);
            if ($this->getConfig()
                     ->getFeatures()
                     ->contains(MySQL57::class) &&
                !file_exists(self::INSTALLED_APPS_STORE.
                             MySQL57::VERSION_TAG)) {
                $this->checkRequirements(get_class($this), [MySQL57::class]);
            }
            $this->progBarAdv(5);
            if ($this->getConfig()
                     ->getFeatures()
                     ->contains(MySQL8::class) &&
                !file_exists(self::INSTALLED_APPS_STORE.
                             MySQL8::VERSION_TAG)) {
                $this->checkRequirements(get_class($this), [MySQL8::class]);
            }
            $this->progBarAdv(5);
            $file  = '/etc/mysql/mysql.conf.d/mysqld.cnf';
            $check = file_get_contents($file);
            $this->progBarAdv(5);
            $restart = false;
            if (strpos($check, 'log_bin') === false)
            {
                $check.= "\nlog_bin = /var/log/mysql/mysql-bin.log\n";
                $this->progBarAdv(5);
                $restart = true;
            }
            if (strpos($check, 'server_id') === false)
            {
                $check.= "\nserver_id = 1\n";
                $this->progBarAdv(5);
                $restart = true;
            }
            if($restart)
            {
                $this->execute('echo "'.$check.'" | '.self::SUDO.' '.self::TEE.' '.$file);
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::SERVICE.' '.MySQL56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            $cmd = new Process(sprintf(self::CMD_POSITION,self::PORT_MASTER));
            $cmd->enableOutput();
            $position = '';
            $file = '';
            $cmd->run();
            $position = str_replace("\n","",$cmd->getOutput());
            $this->progBarAdv(5);
            unset($cmd);
            $cmd = new Process(sprintf(self::CMD_FILE,self::PORT_MASTER));
            $cmd->enableOutput();
            $cmd->run();
            $file = str_replace("\n","",$cmd->getOutput());
            unset($cmd);
            $this->progBarAdv(5);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            if(!is_dir(self::DIR_MYSQL_LIB.'/0'))
            {
                $this->execute(self::SUDO.' '.self::SERVICE.' '.MySQL56::SERVICE_NAME.' '.self::SERVICE_STOP);
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' mkdir '.self::DIR_MYSQL_LIB.'/0');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::MV.' '.self::DIR_MYSQL_LIB.'/* '.self::DIR_MYSQL_LIB.'/0');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::CHOWN.' '.self::MYSQL_USERGROUP.' /var/lib/mysql/0');
                $this->progBarAdv(5);
                $content = file_get_contents('/etc/mysql/mysql.conf.d/mysqld.cnf');
                $content = str_replace('/var/lib/mysql', self::DIR_MYSQL_LIB.'/0', $content);
                $this->execute('echo "'.$content.  '" | '. self::SUDO. ' '.  self::TEE.' /etc/mysql/mysql.conf.d/mysqld.cnf');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::SERVICE.' '.MySQL56::SERVICE_NAME.' '.self::SERVICE_START);
                $this->progBarAdv(5);
            }
            $phpMyAdmin = '';
            //phpMyAdmin Operations
            if($this->getConfig()->getFeatures()->contains(PhpMyAdmin::class))
            {
                $phpMyAdmin .= self::PHPMYADMIN_CORE_TEMPLATE;
            }
            for($i=1; $i<=$this->getConfig()->getSlaveCount(); $i++)
            {
                $this->execute(self::SUDO.' '.self::MKDIR.' '.self::DIR_MYSQL_LIB.'/'.$i);
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::CHOWN.' '.self::MYSQL_USERGROUP.' '.self::DIR_MYSQL_LIB.'/'.$i);
                $this->progBarAdv(5);
                $cmd = self::SUDO.' '.self::MYSQLD_BIN.' --initialize-insecure --user=mysql --datadir='.self::DIR_MYSQL_LIB.'/'.$i;
                $this->execute($cmd);
                unset($cmd);
                $this->progBarAdv(5);
                $logDir = sprintf(self::LOG_DIR, $i);
                $this->execute(self::SUDO.' '.self::MKDIR.' '.$logDir);
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::CHOWN .' '.self::MYSQL_USERGROUP.' '.$logDir);
                $this->progBarAdv(5);
                $c = $i+1;
                $p = 3306 + $i;
                $content = sprintf(self::TEMPLATE, $i, $i, $i, $i, $p, $c, $i, $i);
                $file = sprintf(self::FILE_SYSTEMD, $i);
                $this->execute('echo "'.$content.'" | '.self::SUDO.' '.self::TEE.' '.self::SYSTEMD_DIR.'/'.$file);
                $this->progBarAdv(5);
                $this->execute(self::RELOAD_DAEMON);
                $this->progBarAdv(5);
                $this->execute(self::ENABLE_SERVICE.' '.$file);
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' '.self::SERVICE.' '.str_replace('.service','',$file).' '.self::SERVICE_START);
                $this->progBarAdv(5);
                sleep(10);
                foreach(self::MYSQL_SETPASSWORD as $query)
                {
                    $cmd = sprintf($query,$p);
                    $cmd = str_replace('##host##','%', $cmd);
                    $this->execute($cmd);
                    $this->progBarAdv(5);
                    unset($cmd);
                }
                $cmd = sprintf(self::SLAVE_ON,$p, self::PORT_MASTER, $file, $position);
                $this->execute($cmd);
                unset($cmd);
                $this->progBarAdv(5);
                $this->execute(sprintf(self::SLAVE_START, $p));
                $this->progBarAdv(5);
                $this->execute(sprintf(self::SLAVE_STATUS, $p));
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/ca-key.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/ca-key.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/ca.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/ca.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/client-cert.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/client-cert.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/client-key.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/client-key.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/private_key.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/private_key.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/public_key.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/public_key.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/server-cert.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/server-cert.pem');
                $this->progBarAdv(5);
                $this->execute(self::SUDO.' -u mysql '.self::CP.' '.self::DIR_MYSQL_LIB.'/0/server-key.pem '.self::DIR_MYSQL_LIB.'/'.$i.'/server-key.pem');
                $this->progBarAdv(5);
                //phpMyAdmin Operations
                if($this->getConfig()->getFeatures()->contains(PhpMyAdmin::class))
                {
                    $content = sprintf(self::PHPMYADMIN_TEMPLATE, $i, $i, $p);
                    $phpMyAdmin.=$content."\n\n";
                    $this->progBarAdv(5);
                }
            }
            //phpMyAdmin Operations
            if($this->getConfig()->getFeatures()->contains(PhpMyAdmin::class))
            {
                file_put_contents(PhpMyAdmin::APP_DIR.'/config.inc.php',$phpMyAdmin);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }
    /**
     *
     */
    public function uninstall()
    : void
    {
        $this->progBarInit(55);
        for($i=1; $i<=$this->getConfig()->getSlaveCount(); $i++)
        {
            $file = sprintf(self::FILE_SYSTEMD, $i);
            $this->execute(self::DISABLE_SERVICE.' '.$file);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::SERVICE.' mysql'.$i.' '.self::SERVICE_STOP);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf '.sprintf(self::LOG_DIR,$i));
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf '.self::DIR_MYSQL_LIB.'/'.$i);
            $this->progBarAdv(5);
            $p = 3306 + $i;
        }
        //phpMyAdmin Operations
        if($this->getConfig()->getFeatures()->contains(PhpMyAdmin::class))
        {
            file_put_contents(PhpMyAdmin::APP_DIR.'/config.inc.php',self::PHPMYADMIN_CORE_TEMPLATE);
            $this->progBarAdv(5);
        }
        $this->getConfig()->removeFeature(get_class($this));
        $this->progBarAdv(5);
        $this->execute(self::RM.' '.self::INSTALLED_APPS_STORE.self::VERSION_TAG);
        $this->progBarAdv(5);
        $this->getConfig()->setSlaveCount(0);
        $this->progBarAdv(5);
        $this->getConfig()->removeFeature(get_class($this));
        $this->getConfig()->writeConfigs();
        $this->progBarFin();
    }


    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.MySQL56::SERVICE_NAME.' '.self::SERVICE_RESTART);
        for($i=1; $i<$this->getConfig()->getSlaveCount(); $i++)
        {
            $this->execute(self::SERVICE_CMD.' mysql'.$i.' '.self::SERVICE_RESTART);
        }
    }

    /**
     * @return mixed
     */
    public function configure()
    : void
    {
    }
}