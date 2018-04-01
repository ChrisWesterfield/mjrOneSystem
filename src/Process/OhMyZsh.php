<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class OhMyZsh
 * @package App\Process
 * @author chris westerfield <chris@mjr.one>
 */
class OhMyZsh extends ProcessAbstract implements ProcessInterface
{
    public const REQUIREMENTS = [];
    public const SOFTWARE = [
        'zsh'
    ];
    public const COMMANDS = [
        self::GIT_CLONE.' git://github.com/robbyrussell/oh-my-zsh.git /home/vagrant/.oh-my-zsh',
        'cp /home/vagrant/.oh-my-zsh/templates/zshrc.zsh-template /home/vagrant/.zshrc',
        'printf "\nsource ~/.bash_aliases\n" | tee -a /home/vagrant/.zshrc',
        'printf "\nsource ~/.profile\n" | tee -a /home/vagrant/.zshrc',
        self::SUDO .' '. self::CHOWN.'chown -R vagrant:vagrant /home/vagrant/.oh-my-zsh',
        self::SUDO .' '. self::CHOWN.'vagrant:vagrant /home/vagrant/.zshrc',
    ];
    public const VERSION_TAG = 'ohmyzsh';
    /**
     * @return void
     */
    public function install(): void
    {
        if(!file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(50);
            $this->touch(self::INSTALLED_APPS_STORE, self::VERSION_TAG);
            $this->progBarAdv(5);
            $this->checkRequirements(get_class($this), self::REQUIREMENTS);
            $this->progBarAdv(5);
            $this->installPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            foreach(self::COMMANDS as $COMMAND)
            {
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
        if(file_exists(self::INSTALLED_APPS_STORE.self::VERSION_TAG))
        {
            $this->progBarInit(35);
            $this->checkUninstall(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallRequirement(get_class($this));
            $this->progBarAdv(5);
            $this->uninstallPackages(self::SOFTWARE);
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf /home/vagrant/.zshrc');
            $this->progBarAdv(5);
            $this->execute(self::SUDO.' rm -Rf /home/vagrant/.oh-my-zsh');
            $this->progBarAdv(5);
            unlink(self::INSTALLED_APPS_STORE.self::VERSION_TAG);
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