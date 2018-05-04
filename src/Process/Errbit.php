<?php
declare(strict_types=1);
namespace App\Process;
use App\System\Config\Site;

/**
 * Class Errbit
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class Errbit extends ProcessAbstract implements ProcessInterface
{
    public const DESCRIPTION = 'Error Tracking for Web Applications';
    public const SUBDOMAIN = 'errbit.';
    public const HOME = '/home/vagrant/errbit';
    public const LOG_FILE = self::HOME.'/errbit.install.log';
    public const USER_FILE = '/home/vagrant/user.json';
    public const RUN_ERRBIT_START = self::HOME.'/bin/errbit.sh start';
    public const RUN_ERRBIT_STOP = self::HOME.'/bin/errbit.sh stop';
    public const RUN = self::HOME.'/run';
    public const SOFTWARE = [];
    public const ERRBIT_PATH = '/home/vagrant/errbit';
    public const PACKAGES = [
        self::GIT_CLONE.' https://github.com/errbit/errbit.git '.self::HOME,
        'cd '.self::HOME,
    ];
    public const PACKAGE_BUNDLE_INSTALL = 'cd '.self::HOME.' && /usr/local/bin/bundle install';
    public const PACKAGE_ASSETS = 'cd '.self::HOME.' && RAILS_ENV=production '.self::BUNDLE.'  exec rake assets:precompile';
    public const PACKAGE_BOOTSTRAP = 'cd '.self::HOME.' && RAILS_ENV=production '.self::BUNDLE.'  exec rake errbit:bootstrap > '.self::LOG_FILE;
    public const USER_GEM_FILE = self::HOME.'/UserGemfile';
    public const DEFAULT_PORT = 8030;
    public const USER_GEM_FILE_CONTENT = '
gem \'unicorn\'
gem \'unicorn-rails\'';
    public const UNICORN_RB_FILE = self::HOME.'/config/unicorn.rb';
    public const UNICORN_CONTENT = '# http://michaelvanrooijen.com/articles/2011/06/01-more-concurrency-on-a-single-heroku-dyno-with-the-new-celadon-cedar-stack/

worker_processes 4 # amount of unicorn workers to spin up
timeout 30         # restarts workers that hang for 30 seconds
preload_app true

listen "/home/vagrant/errbit/run/errbit.socket"
listen "127.0.0.1:'.self::DEFAULT_PORT.'"
pid "/home/vagrant/errbit/run/errbit.pid"
stderr_path "/home/vagrant/base/log/errbit.stderr.log"
stdout_path "/home/vagrant/base/log/errbit.stdout.log"

# Taken from github: https://github.com/blog/517-unicorn
# Though everyone uses pretty miuch the same code
before_fork do |server, worker|
  ##
  # When sent a USR2, Unicorn will suffix its pidfile with .oldbin and
  # immediately start loading up a new version of itself (loaded with a new
  # version of our app). When this new Unicorn is completely loaded
  # it will begin spawning workers. The first worker spawned will check to
  # see if an .oldbin pidfile exists. If so, this means we\'ve just booted up
  # a new Unicorn and need to tell the old one that it can now die. To do so
  # we send it a QUIT.
  #
  # Using this method we get 0 downtime deploys.

  old_pid = "#{server.config[:pid]}.oldbin"
  if File.exists?(old_pid) && server.pid != old_pid
    begin
      Process.kill("QUIT", File.read(old_pid).to_i)
    rescue Errno::ENOENT, Errno::ESRCH
      # someone else did our job for us
    end
  end
end';
    public const ENV_FILE = self::HOME.'/.env';
    public const ENV_CONTENT = 'ERRBIT_HOST=%s
ERRBIT_PROTOCOL=http
ERRBIT_ENFORCE_SSL=false
CONFIRM_RESOLVE_ERR=true
ERRBIT_CONFIRM_ERR_ACTIONS=true
ERRBIT_USER_HAS_USERNAME=false
ERRBIT_USE_GRAVATAR=true
ERRBIT_GRAVATAR_DEFAULT=identicon
ALLOW_COMMENTS_WITH_ISSUE_TRACKER=true
SERVE_STATIC_ASSETS=true
SECRET_KEY_BASE=f258ed69266dc8ad0ca79363c3d2f945c388a9c5920fc9a1ae99a98fbb619f135001c6434849b625884a9405a60cd3d50fc3e3b07ecd38cbed7406a4fccdb59c
ERRBIT_EMAIL_FROM=\'errbit@%s\'
ERRBIT_EMAIL_AT_NOTICES=\'[1,10,100]\'
ERRBIT_PER_APP_EMAIL_AT_NOTICES=false
ERRBIT_NOTIFY_AT_NOTICES=\'[0]\'
ERRBIT_PER_APP_NOTIFY_AT_NOTICES=false
MONGO_URL=\'mongodb://localhost\'
ERRBIT_LOG_LEVEL=info
ERRBIT_LOG_LOCATION=STDOUT
GITHUB_URL=https://github.com
GITHUB_AUTHENTICATION=false
GITHUB_API_URL=https://api.github.com
GITHUB_ACCESS_SCOPE=\'[repo]\'
GITHUB_SITE_TITLE=GitHub
DEVISE_MODULES=\'[database_authenticatable,recoverable,rememberable,trackable,validatable,omniauthable]\'
GOOGLE_AUTHENTICATION=false
GOOGLE_SITE_TITLE=Google
SMTP_SERVER=localhost
SMTP_PORT=1025
SMTP_USERNAME=error@localhost
SMTP_PASSWORD=123
EMAIL_DELIVERY_METHOD=":smtp"
';
    public const REQUIREMENTS = [
        Ruby::class,
        Mongo::class,
    ];
    public const VERSION_TAG = 'errbit';

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
        if(!file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(200);
            $this->touch(self::INSTALLED_APPS_STORE,self::VERSION_TAG);
            $this->progBarAdv(5);
            $orgDir = getcwd();
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(40);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach(self::PACKAGES as $package)
            {
                $this->execute($package);
                $this->progBarAdv(25);
            }
            chdir(self::HOME);
            $this->progBarAdv(5);
            file_put_contents(self::USER_GEM_FILE, self::USER_GEM_FILE_CONTENT);
            $this->progBarAdv(5);
            $this->execute(self::PACKAGE_BUNDLE_INSTALL);
            $this->progBarAdv(35);
            file_put_contents(self::UNICORN_RB_FILE,self::UNICORN_CONTENT);
            $this->progBarAdv(5);
            file_put_contents(self::ENV_FILE,sprintf(self::ENV_CONTENT, $this->getConfig()->getName(), $this->getConfig()->getName()));
            $this->progBarAdv(5);
            mkdir(self::HOME.'/run',0777, true);
            $this->progBarAdv(5);
            $this->execute(self::PACKAGE_ASSETS);
            $this->progBarAdv(5);
            $this->execute(self::PACKAGE_BOOTSTRAP);
            $this->progBarAdv(15);
            $this->getPassword();
            $this->progBarAdv(5);
            $this->execute(self::RUN_ERRBIT_START);
            $this->progBarAdv(5);
            $this->getConfig()->getUsedPorts()->add(self::DEFAULT_PORT);
            $this->progBarFin();
        }
    }

    /**
     *
     */
    protected function getPassword()
    {
        $config = file_get_contents(self::LOG_FILE);
        $config = explode('Creating an initial admin user:',$config);
        $config = $config[1];
        $config = explode('Be sure to note down these credentials now!', $config);
        $config = $config[0];
        $config = explode('-- email:    ',$config);
        $config = $config[1];
        $config = explode('-- password: ',$config);
        $user = str_replace("\n","",$config[0]);
        $user = trim($user);
        $password = str_replace("\n","",$config[1]);
        $password = trim($password);
        unset($config);
        if(file_exists('/home/vagrant/.userData'))
        {
            $config = json_decode(file_get_contents(self::USER_FILE),true);
        }

        if(empty($config))
        {
            $config = [];
        }

        $config['errbit'] = [
            'username'=>$user,
            'password'=>$password,
        ];

        file_put_contents(self::USER_FILE,json_encode($config));
    }


    /**
     *
     */
    public function uninstall(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE. self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->execute(self::RUN_ERRBIT_START);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' mkdir '.self::HOME.' -Rf ');
            $this->getConfig()->getUsedPorts()->removeElement(self::DEFAULT_PORT);
            $this->removeWeb(self::SUBDOMAIN);
            $this->progBarFin();
        }
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
        $this->addSite([
            'map'=> self::SUBDOMAIN .$this->getConfig()->getName(),
            'to'=>self::HOME,
            'type'=>'Errbit',
            'listen'=>'127.0.0.1:'.self::DEFAULT_PORT,
            'category'=>Site::CATEGORY_ADMIN,
            'description'=>'Errbit Error Tracker'
        ]);
    }
}