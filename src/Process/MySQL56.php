<?php
declare(strict_types=1);

namespace App\Process;

/**
 * Class MySQL56
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class MySQL56 extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'mysql-community-client',
        'mysql-community-server',
        'mysql-community-source',
        'mysql-shell',
        'mysql-utilities',
    ];
    public const APT_FILE = '/etc/apt/sources.list.d/mysql.list';
    public const PREFERENCES_CONTENT = 'Package: *
Pin: origin repo.mysql.com
Pin-Priority: 1001';
    public const PREFERENCES_FILE = '/etc/apt/preferences.d/mysql';
    public const COMMANDS = [
        self::SUDO . ' apt-key adv --keyserver pgp.mit.edu --recv-keys 5072E1F5 ',
        'echo "deb http://repo.mysql.com/apt/ubuntu xenial mysql-5.6  mysql-tools" | ' . self::SUDO . ' ' . self::TEE . ' ' . self::APT_FILE,
        self::SUDO.' '.self::APT.' autoclean ',
        'echo "'.self::PREFERENCES_CONTENT.'" | "'.self::SUDO .' '.self::TEE .' '.self::PREFERENCES_FILE,
        self::SUDO . ' ' . self::APT . ' update ',
        self::SUDO . ' /bin/bash /home/vagrant/base/system/bin/mysql.install.bash',
        self::SUDO . ' ' . self::SED . '  -i \'/^bind-address/s/bind-address.*=.*/bind-address = 0.0.0.0/\' /etc/mysql/my.cnf',
        'mysql --user="root" --password="123" -e "GRANT ALL ON *.* TO root@\'0.0.0.0\' IDENTIFIED BY \'123\' WITH GRANT OPTION;"',
        self::SUDO . ' ' . self::SERVICE_CMD . ' mysql ' . self::SERVICE_RESTART,
        'mysql --user="root" --password="123" -e "CREATE USER \'application\'@\'%\' IDENTIFIED BY \'123\';"',
        'mysql --user="root" --password="123" -e "FLUSH PRIVILEGES;"',
        self::SUDO . ' ' . self::SERVICE_CMD . ' mysql ' . self::SERVICE_RESTART,
    ];
    public const VERSION_TAG = 'mysql56';
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
            $this->progBarInit(105);
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
            $this->execute(self::SUDO.' '.self::RM.' -Rf /var/lib/mysql');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf /var/log/mysql');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -f '.self::PREFERENCES_FILE);
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