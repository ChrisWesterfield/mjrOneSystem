<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class BeanstalkedAdmin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class BeanstalkedAdmin extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [
        Beanstalked::class,
        Composer::class,
    ];
    public const SOFTWARE = [
    ];
    public const COMMAND_INSTALL = self::COMPOSER.' create-project ptrofimov/beanstalk_console /home/vagrant/beanstalkd';
    public const CONFIG = '<?php
\$GLOBALS[\'config\'] = array(
    /**
     * List of servers available for all users
     */
    \'servers\' => array(\'Local Beanstalkd\' => \'beanstalk://localhost:11300\',),
    /**
     * Saved samples jobs are kept in this file, must be writable
     */
    \'storage\' => dirname(__FILE__) . DIRECTORY_SEPARATOR . \'storage.json\',
    /**
     * Optional Basic Authentication
     */
    \'auth\' => array(
        \'enabled\' => false,
        \'username\' => \'admin\',
        \'password\' => \'password\',
    ),
    /**
     * Version number
     */
    \'version\' => \'1.7.9\',
);';
    public const VERSION_TAG = 'beanstalkdAdmin';

    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(15);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::COMMAND_INSTALL);
            $this->progBarAdv(15);
            file_put_contents('/home/vagrant/beanstalkd/config.php',self::CONFIG);
            $this->progBarAdv(5);
            $this->getConfig()->addFeature(get_class($this));
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
            $this->progBarInit(55);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->execute('rm -Rf /home/vagrant/beanstalkd');
            $this->progBarAdv(25);
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