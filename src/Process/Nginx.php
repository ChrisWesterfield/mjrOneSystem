<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Nginx
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Nginx extends ProcessAbstract implements ProcessInterface
{
    public const SOFTWARE = [
        'libxml2-dev',
        'libxslt1-dev',
        'libgeoip-dev',
        'libgoogle-perftools-dev',
        'libperl-dev',
        'libpcre++-dev',
        'libcurl4-openssl-dev',
        'libssl-dev'
    ];
    public const DIRECTORY = '/home/vagrant/base/src/nginx/nginx-1.13-4';
    public const NGINX_NAME = 'MJR-ONE-NGINX-1.13.4';
    public const COMPILE = [
        ProcessInterface::SUDO.' chmod -R 0777 '.self::DIRECTORY,
        ProcessInterface::SUDO .' '.ProcessInterface::MAKE.' clean ',
        ProcessInterface::SUDO .' '.ProcessInterface::MAKE.' ./configure --prefix=/usr/share/nginx --sbin-path=/usr/sbin/nginx --conf-path=/etc/nginx/nginx.conf --pid-path=/var/run/nginx.pid --lock-path=/var/lock/nginx.lock --error-log-path=/var/log/nginx/error.log --http-log-path=/var/log/access.log --user=www-data --group=www-data --build='.self::NGINX_NAME.' --with-threads --with-file-aio --with-http_gzip_static_module --with-http_realip_module --with-http_xslt_module --with-http_geoip_module --with-http_dav_module --with-http_flv_module --with-http_mp4_module --with-http_gunzip_module --with-http_secure_link_module --with-http_random_index_module --with-http_auth_request_module --with-http_stub_status_module --with-http_perl_module --with-mail=dynamic --with-mail_ssl_module --with-stream=dynamic --with-stream_ssl_module --with-google_perftools_module --with-pcre --add-dynamic-module=/vagrant/src/nginx/headers-more-nginx-module-master --add-dynamic-module=/vagrant/src/nginx/naxsi-master/naxsi_src --add-dynamic-module=/vagrant/src/nginx/nginx-upload-progress-module-master --add-dynamic-module=/vagrant/src/nginx/ngx_http_accounting_module-master --add-dynamic-module=/vagrant/src/nginx/nginx-module-vts-master --add-dynamic-module=/vagrant/src/nginx/graphite-nginx-module-master  --with-http_ssl_module  --with-http_v2_module',
        ProcessInterface::SUDO.' '.ProcessInterface::MAKE.' -j `cat /proc/cpuinfo | grep processor | wc -l`',
        ProcessInterface::SUDO .' '.ProcessInterface::MAKE.' install',
        ProcessInterface::SUDO.' /bin/rm -Rf /etc/nginx/',
        ProcessInterface::SUDO.' tar -xzf /vagrant/nginx-etc.tgz  -C /etc',
        ProcessInterface::SUDO.' /usr/sbin/service nginx restart',
        ProcessInterface::SUDO.' cp /vagrant/src/nginx/nginx.service /lib/systemd/system/nginx.service',
        ProcessInterface::SUDO.' '.ProcessInterface::SYSTEMCTL.' daemon-reload',
        ProcessInterface::ENABLE_SERVICE.' nginx.service',
    ];
    public const REQUIREMENTS = [];

    /**
     *
     */
    public const VERSION_TAG = 'nginx';

    public function install():void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(100);
            $this->progBarAdv(5);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $currentDir = getcwd();
            $this->progBarAdv(5);
            chdir(self::DIRECTORY);
            $this->progBarAdv(5);
            $this->execute('cd '.self::DIRECTORY);
            $this->progBarAdv(5);
            foreach(self::COMPILE as $entry)
            {
                $this->getOutput()->writeln($this->execute($entry));
                $this->progBarAdv(5);
            }
            chdir($currentDir);
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function uninstall():void
    {
        if(file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(70);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $currentDir = getcwd();
            $this->progBarAdv(5);
            chdir(self::DIRECTORY);
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SERVICE.' nginx stop'));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::DISABLE_SERVICE.' nginx'));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SUDO.' '.self::MAKE.' uninstall '));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SUDO.' rm -Rf /etc/nginx '));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SUDO.' /lib/systemd/system/nginx.service'));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SUDO.' '.ProcessInterface::DISABLE_SERVICE.' nginx.service'));
            $this->progBarAdv(5);
            $this->getOutput()->writeln($this->execute(self::SUDO.' '.self::SYSTEMCTL.' daemon-reload'));
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->progBarFin();
        }
    }

    /**
     *
     */
    public function configure():void
    {
    }
}