<?php
declare(strict_types=1);

namespace App\Process;

/**
 * Class Maria
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Maria extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const APT_FILE = '/etc/apt/sources.list.d/maria.list';
    public const SOFTWARE = [
        'mariadb-server',
        'galera-3',
        'mariadb-server-10.2',
        'mariadb-server-core-10.2',
    ];
    public const COMMANDS = [
        self::SUDO . ' apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xF1656F24C74CD1D8',
        'echo "deb [arch=amd64,i386,ppc64el] http://ftp.heanet.ie/mirrors/mariadb/repo/10.2/ubuntu xenial main" | ' . self::SUDO . ' ' . self::TEE . ' ' . self::APT_FILE,
        self::SUDO . ' ' . self::APT . ' update ',
        self::SUDO.' /bin/bash /home/vagrant/base/system/bin/mariadb.install.bash',
        self::SUDO . ' ' . self::SED . '  -i \'/^bind-address/s/bind-address.*=.*/bind-address = 0.0.0.0/\' /etc/mysql/my.cnf',
        'mysql --user="root" --password="123" -e "GRANT ALL ON *.* TO root@\'0.0.0.0\' IDENTIFIED BY \'123\' WITH GRANT OPTION;"',
        self::SUDO . ' ' . self::SERVICE_CMD . ' mysql ' . self::SERVICE_RESTART,
        'mysql --user="root" --password="123" -e "CREATE USER \'application\'@\'%\' IDENTIFIED BY \'123\';"',
        'mysql --user="root" --password="123" -e "FLUSH PRIVILEGES;"',
        self::SUDO . ' ' . self::SERVICE_CMD . ' mysql ' . self::SERVICE_RESTART,
    ];
    public const VERSION_TAG = 'maria';

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(65);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            foreach (self::COMMANDS as $COMMAND) {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(100);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(30);
            $this->execute(self::SUDO.' '.self::APT.' autoremove -y');
            $this->progBarAdv(30);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::APT_FILE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf /etc/mysql');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f /var/lib/mysql');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f /var/log/mysql');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
    }
}