<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class PostgreSQL
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class PostgreSQL extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Postgresql SQL Server';
    public const BIN_PSQL = '/usr/bin/psql';
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'postgresql'
    ];
    public const COMMANDS = [
        self::SUDO.' '.self::SED.' -i "s/#listen_addresses = \'localhost\'/listen_addresses = \'*\'/g" /etc/postgresql/9.5/main/postgresql.conf',
        self::SUDO.' echo "host    all             all             10.0.2.2/32               md5" | '.self::SUDO.' '.self::TEE.' -a /etc/postgresql/9.5/main/pg_hba.conf',
        self::SUDO.' -u postgres psql -c "CREATE ROLE vagrant LOGIN UNENCRYPTED PASSWORD \'123\' SUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;"',
        self::SUDO.' -u postgres /usr/bin/createdb --echo --owner=vagrant vagrant',
        self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART,
    ];
    public const VERSION_TAG = 'postgresql';
    public const DEFAULT_PORT = 5432;

    /**
     *
     */
    public function restartService():void
    {
        $this->execute(self::SERVICE_CMD.' '.self::VERSION_TAG.' '.self::SERVICE_RESTART);
    }
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(25);
            foreach(self::COMMANDS as $COMMAND)
            {
                $this->execute($COMMAND);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.Php56::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.Php70::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.Php71::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' install php'.Php72::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            $this->getConfig()->addFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(90);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            if($this->getConfig()->getFeatures()->contains(Php56::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.Php56::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php56::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php70::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.Php70::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php70::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php71::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.Php71::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php71::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            if($this->getConfig()->getFeatures()->contains(Php72::class))
            {
                $this->execute(self::SUDO.' '.self::APT.' purge php'.Php72::VERSION.'-pgsql');
                $this->progBarAdv(5);
                $this->execute(self::SERVICE_CMD.' '.Php72::SERVICE_NAME.' '.self::SERVICE_RESTART);
                $this->progBarAdv(5);
            }
            $this->getConfig()->removeFeature(get_class($this));
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
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