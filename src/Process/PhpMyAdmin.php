<?php
declare(strict_types=1);

namespace App\Process;

use App\System\Config\Fpm;
use App\System\Config\Site;

/**
 * Class PhpMyAdmin
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class PhpMyAdmin extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Database Administration Tools';
    public const REQUIREMENTS = [
        Php72::class,
        Composer::class,
        PhpMysql::class,
    ];
    public const SOFTWARE = [];
    public const APP_DIR = '/home/vagrant/PhpMyAdmin';
    public const COMMANDS = [
        self::GIT_CLONE . ' https://github.com/phpmyadmin/phpmyadmin.git ' . self::APP_DIR,
        'cd ' . self::APP_DIR . ' && git checkout RELEASE_4_7_9 ',
        self::COMPOSER.' install -vvv --profile -d '.self::APP_DIR,
        'cd '.self::APP_DIR.'/themes && '.self::WGET .' https://files.phpmyadmin.net/themes/fallen/0.5/fallen-0.5.zip',
        'cd '.self::APP_DIR.'/themes && unzip fallen-0.5.zip',
        'cd '.self::APP_DIR.'/themes && '.self::RM.' -f fallen-0.5.zip',
    ];
    public const VERSION_TAG = 'phpmyadmin';
    public const FPM_IDENTITY = 'admin.phpmyadmin';
    public const SUBDOMAIN = 'pma.';
    public const CONFIG_CONTENT = '<?php
\$cfg[\'blowfish_secret\'] = \'r6EA8vX7c6LRoAykpCgGHxV6RvJm6QQNkdifRpG4xWkxUmZjxZxaV7RakMjj2Y2m\';
\$i = 0;
\$cfg[\'UploadDir\'] = \'\';
\$cfg[\'SaveDir\'] = \'\';
\$cfg[\'PmaNoRelation_DisableWarning\'] = true;
\$cfg[\'SuhosinDisableWarning\'] = true;
\$cfg[\'LoginCookieValidityDisableWarning\'] = true;
\$cfg[\'NavigationTreeDbSeparator\'] = \'\';
\$cfg[\'ShowPhpInfo\'] = true;
\$cfg[\'DefaultLang\'] = \'de\';
\$cfg[\'ServerDefault\'] = 1;
\$cfg[\'ThemeDefault\'] = \'fallen\';
\$i++;
//Master Config
\$cfg[\'Servers\'][\$i][\'verbose\'] = \'Master\';
\$cfg[\'Servers\'][\$i][\'host\'] = \'127.0.0.1\';
\$cfg[\'Servers\'][\$i][\'port\'] = \'3306\';
\$cfg[\'Servers\'][\$i][\'socket\'] = \'\';
\$cfg[\'Servers\'][\$i][\'auth_type\'] = \'config\';
\$cfg[\'Servers\'][\$i][\'user\'] = \'root\';
\$cfg[\'Servers\'][\$i][\'password\'] = \'123\';
\$cfg[\'Servers\'][\$i][\'pmadb\'] = \'phpmyadmin\';
\$cfg[\'Servers\'][\$i][\'controlhost\'] = \'127.0.0.1\';
\$cfg[\'Servers\'][\$i][\'controlport\'] = \'3306\';
\$cfg[\'Servers\'][\$i][\'controluser\'] = \'root\';
\$cfg[\'Servers\'][\$i][\'controlpass\'] = \'123\';
\$cfg[\'Servers\'][\$i][\'bookmarktable\'] = \'pma__bookmark\';
\$cfg[\'Servers\'][\$i][\'relation\'] = \'pma__relation\';
\$cfg[\'Servers\'][\$i][\'userconfig\'] = \'pma__userconfig\';
\$cfg[\'Servers\'][\$i][\'users\'] = \'pma__users\';
\$cfg[\'Servers\'][\$i][\'usergroups\'] = \'pma__usergroups\';
\$cfg[\'Servers\'][\$i][\'navigationhiding\'] = \'pma__navigationhiding\';
\$cfg[\'Servers\'][\$i][\'table_info\'] = \'pma__table_info\';
\$cfg[\'Servers\'][\$i][\'column_info\'] = \'pma__column_info\';
\$cfg[\'Servers\'][\$i][\'history\'] = \'pma__history\';
\$cfg[\'Servers\'][\$i][\'recent\'] = \'pma__recent\';
\$cfg[\'Servers\'][\$i][\'favorite\'] = \'pma__favorite\';
\$cfg[\'Servers\'][\$i][\'table_uiprefs\'] = \'pma__table_uiprefs\';
\$cfg[\'Servers\'][\$i][\'tracking\'] = \'pma__tracking\';
\$cfg[\'Servers\'][\$i][\'table_coords\'] = \'pma__table_coords\';
\$cfg[\'Servers\'][\$i][\'pdf_pages\'] = \'pma__pdf_pages\';
\$cfg[\'Servers\'][\$i][\'savedsearches\'] = \'pma__savedsearches\';
\$cfg[\'Servers\'][\$i][\'central_columns\'] = \'pma__central_columns\';
\$cfg[\'Servers\'][\$i][\'export_templates\'] = \'pma__export_templates\';';
    public const SERVER_CONFIG = "
\$i++;
\$cfg['Servers'][\$i]['verbose'] = '%s';
\$cfg['Servers'][\$i]['host'] = '127.0.0.1';
\$cfg['Servers'][\$i]['port'] = '%s';
\$cfg['Servers'][\$i]['socket'] = '';
\$cfg['Servers'][\$i]['auth_type'] = 'config';
\$cfg['Servers'][\$i]['user'] = 'root';
\$cfg['Servers'][\$i]['password'] = '123';";
    /**
     * @return void
     */
    public function install(): void
    {
        if (!file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(90);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach (self::COMMANDS as $com) {
                $this->execute($com);
                $this->progBarAdv(10);
            }
            $this->progBarAdv(5);
            $this->execute(self::SUDO . ' ' . self::CHOWN . ' -R vagrant:vagrant ' . self::APP_DIR);
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
        if (file_exists(self::INSTALLED_APPS_STORE . self::VERSION_TAG)) {
            $this->progBarInit(45);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(20);
            $this->execute(self::SUDO . ' rm -Rf ' . self::APP_DIR);
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE . self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->getConfig()->removeFeature(get_class($this));
            $this->removeWeb(self::SUBDOMAIN);
            $this->removeFpm(self::FPM_IDENTITY);
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $main = self::CONFIG_CONTENT;
        //@todo multi Server Environment
        $this->execute('echo "' . $main . '" | ' . self::TEE . ' ' . self::APP_DIR . '/config.inc.php');
        $this->execute(self::CHMOD . ' 0644 ' . self::APP_DIR . '/config.inc.php');
        $this->addSite(
            [
                'map' => self::SUBDOMAIN . $this->getConfig()->getName(),
                'type' => 'PhpMyAdmin',
                'to' => self::APP_DIR,
                'fpm' => true,
                'zRay' => false,
                'category' => Site::CATEGORY_ADMIN,
                'description' => 'PhpMyAdmin'
            ],
            [
                'name' => self::FPM_IDENTITY,
                'user' => 'vagrant',
                'group' => 'vagrant',
                'listen' => '127.0.0.1:%%%PORT%%%',
                'pm' => Fpm::ONDEMAND,
                'maxChildren' => 2,
                'xdebug'=>false,
            ]
        );
    }
}