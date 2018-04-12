<?php
declare(strict_types=1);

namespace App\Process;

use App\System\Config\Site;

/**
 * Class CockroachDb
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class CockroachDb extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const DESCRIPTION = 'NoSQL Database with Support for PgSQL SQL';
    public const SOFTWARE = [];
    public const VERSION_TAG = 'cockroachdb';
    public const DATA_DIR = '/var/lib/cockroachdb/';
    public const DEFAULT_PORT = 26257;
    public const PORT_HTTP = 7005;
    public const SUBDOMAIN = 'roachdb.';
    public const SYSTEMD = '[Unit]
Description=Cockroach db auto starter

[Install]
WantedBy=multi-user.target

[Service]
User=vagrant
Group=vagrant
ExecStart=/usr/local/bin/cockroach start --insecure --store=' . self::DATA_DIR . ' --port=' . self::DEFAULT_PORT . ' --http-port=' . self::PORT_HTTP . ' --logtostderr=ERROR
ExecStop=/usr/local/bin/cockroach quit --insecure
SyslogIdentifier=cockroachdb
Restart=always
LimitNOFILE=35000';
    public const CONNECT = '/usr/local/bin/cockroach sql --insecure';

    /**
     *
     */
    public function restartService(): void
    {
        $this->execute(self::SERVICE_CMD . ' cockroach ' . self::SERVICE_RESTART);
    }

    /**
     * @return void
     */

    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(70);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute(self::WGET . ' -qO- https://binaries.cockroachdb.com/cockroach-v2.0.0.linux-amd64.tgz | tar  xvz');
            $this->progBarAdv(15);
            $this->execute(self::SUDO . ' ' . self::MV . ' /home/vagrant/cockroach-v2.0.0.linux-amd64/cockroach /usr/local/bin');
            $this->progBarAdv(5);
            $this->execute(self::RM . ' -Rf /home/vagrant/cockroach-v2.0.0.linux-amd64/');
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::MKDIR . ' ' . self::DATA_DIR);
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::CHOWN . ' vagrant:vagrant ' . self::DATA_DIR);
            $this->progBarAdv(5);
            $this->execute('echo "' . self::SYSTEMD . '" | ' . self::SUDO . ' ' . self::TEE . ' /lib/systemd/system/cockroachdb.service');
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::SYSTEMCTL . ' daemon-reload');
            $this->progBarAdv(5);
            $this->execute(self::ENABLE_SERVICE . ' cockroachdb.service');
            $this->progBarAdv(5);
            $this->execute(self::SERVICE_CMD . ' ' . 'cockroachdb ' . self::SERVICE_START);
            $this->progBarAdv(5);
            $this->execute('echo "'.self::CONNECT.'" | '.self::SUDO.' '.self::TEE.' /usr/local/bin/cconnect');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::CHMOD.' +x /usr/local/bin/cconnect');
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(55);
            $this->execute(self::SERVICE_CMD . ' ' . 'cockroachdb ' . self::SERVICE_STOP);
            $this->progBarAdv(5);
            $this->execute(self::DISABLE_SERVICE . ' cockroachdb.service');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' /lib/systemd/system/cockroachdb.service');
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::SYSTEMCTL . ' daemon-reload');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' -Rf '.self::DATA_DIR);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' /usr/local/bin/cockroach');
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarAdv(5);
            $this->removeWeb(self::SUBDOMAIN);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' '.self::RM.' /usr/local/bin/cconnect');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite([
            'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
            'type' => 'Proxy',
            'listen' => '127.0.0.1:' . self::PORT_HTTP,
            'category' => Site::CATEGORY_OTHER,
            'description' => 'Cockroach HTTP Port'
        ]);
    }
}