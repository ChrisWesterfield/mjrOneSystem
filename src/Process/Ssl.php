<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class Ssl
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Ssl extends ProcessAbstract implements ProcessInterface
{
    public const PATH_CA_DIR = '/home/vagrant/base/etc/ssl/';
    public const PATH_SSL_DIR = '/etc/ssl/private/';
    public const CA_CERT = [
        'CNF'=>self::PATH_CA_DIR.'vagrant.cnf',
        'CRT'=>self::PATH_CA_DIR.'vagrant.crt',
        'KEY'=>self::PATH_CA_DIR.'vagrant.key',
    ];
    public const CERT = [
        'CNF'=>self::PATH_SSL_DIR.'%s/vagrant.cnf',
        'CRT'=>self::PATH_SSL_DIR.'%s/vagrant.crt',
        'CSR'=>self::PATH_SSL_DIR.'%s/vagrant.csr',
        'KEY'=>self::PATH_SSL_DIR.'%s/vagrant.key',
    ];
    public const SOFTWARE = [
        'openssl',
    ];
    public const REQUIREMENTS = [];
    public const VERSION_TAG = 'SSL';
    public const OPENSSL = '/usr/bin/openssl';
    public const COMMANDS_CA_GENERATE = [
        self::OPENSSL.' genrsa -out "'.self::CA_CERT['KEY'].'" 4096',
        self::OPENSSL.' req -config "'.self::CA_CERT['CNF'].'" -key "'.self::CA_CERT['KEY'].'" -x509 -new -extensions v3_ca -days 3650 -sha256 -out "'.self::CA_CERT['CRT'].'" ',
    ];
    public const COMMANDS_CRT_GENERATE = [
        'GENKEY'=>self::SUDO.' '.self::OPENSSL.' genrsa -out "'.self::CERT['KEY'].'" 2048 2>/dev/null',
        'GENRSA'=>self::SUDO.' '.self::OPENSSL.' req -config "'.self::CERT['CNF'].'" -key "'.self::CERT['KEY'].'" -new -sha256 -out "'.self::CERT['CSR'].'"',
        'GENCRT'=>self::SUDO.' '.self::OPENSSL.' x509 -req -extfile "'.self::CERT['CNF'].'" -extensions server_cert -days 3650 -in "'.self::CERT['CSR'].'" -CA "'.self::CA_CERT['CRT'].'" -CAkey "'.self::CA_CERT['KEY'].'" -CAcreateserial -out "'.self::CERT['CRT'].'"'
    ];

    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->installPackages(self::SOFTWARE);
            $this->getConfig()->addFeature(get_class($this));
        }
    }

    /**
     *
     */
    public function uninstall(): void
    {
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->checkUninstall(get_class($this));
            $this->uninstallRequirement(get_class($this));
            $this->uninstallPackages(self::SOFTWARE);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->getConfig()->removeFeature(get_class($this));
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        if(!file_exists(self::PATH_CA_DIR) || !file_exists(self::CA_CERT['CNF']) || !file_exists(self::CA_CERT['CRT']) || !file_exists(self::CA_CERT['KEY']))
        {
            mkdir(self::PATH_CA_DIR,0775);
            $rendered = $this->getContainer()->get('twig')->render(
                'configuration/ssl.ca.conf.twig',
                [
                    'hostname'=>$this->getConfig()->getName(),
                    'path_ssl'=>self::PATH_CA_DIR,
                    'root_key'=>self::CERT['KEY'],
                    'root_crt'=>self::CERT['CRT'],
                ]
            );
            $this->execute('echo "'.$rendered.'" | '.self::SUDO.' '.self::TEE.' '.self::CA_CERT['CNF']);
            foreach(self::COMMANDS_CA_GENERATE as $cmd)
            {
                $this->execute($cmd);
            }

        }
        if(!file_exists(self::PATH_SSL_DIR))
        {
            mkdir(self::PATH_SSL_DIR,0775);
        }
        $this->execute(self::SUDO.' sed -i \'/copy_extensions\ =\ copy/s/^#\ //g\' /etc/ssl/openssl.cnf');
    }

    /**
     * @param string $crt
     */
    public function generateCert(string $crt):void
    {
        $rendered = $this->getContainer()->get('twig')->render(
            'configuration/ssl.crt.conf.twig',
            [
                'hostname'=>$this->getConfig()->getName(),
                'path_ssl'=>self::PATH_CA_DIR,
                'root_key'=>sprintf(self::CERT['KEY'], $crt),
                'root_crt'=>sprintf(self::CERT['CRT'], $crt),
                'name'=>$crt,
            ]
        );
        $this->execute(self::SUDO.' /bin/mkdir '.self::PATH_SSL_DIR.'/'.$crt);
        $this->execute('echo "'.$rendered.'" | '.self::SUDO.' '.self::TEE.' '.sprintf(self::CERT['CNF'], $crt));
        $this->printOutput('Generating Certificate '.$crt,1);
        $this->execute(
            sprintf(
                self::COMMANDS_CRT_GENERATE['GENKEY'],
                $crt
            )
        );
        $this->execute(
            sprintf(
                self::COMMANDS_CRT_GENERATE['GENRSA'],
                $crt,
                $crt,
                $crt
            )
        );
        $this->execute(
            sprintf(
                self::COMMANDS_CRT_GENERATE['GENCRT'],
                $crt,
                $crt,
                $crt,
                $crt,
                $crt
            )
        );
        $this->printOutput('done',1);
    }
}