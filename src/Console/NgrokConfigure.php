<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 02.04.18
 * Time: 02:32
 */

namespace App\Console;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class NgrokConfigure extends ContainerAwareCommand
{
    use LockableTrait;
    public const PATH_NGROK = '/home/vagrant/.ngrok2';
    public const PATH_CONFIG = self::PATH_NGROK.'/ngrok.yml';
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:ngrok')
            ->setHelp('Configure Ngrok')
            ->setDescription('Configure Ngrok')
            ->addOption('remove','r',InputOption::VALUE_NONE, 'remove ngrok config')
            ->addArgument('port', InputArgument::REQUIRED,'Port to Map')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>Command is locked!</error>');
            return 0;
        }
        if(!is_dir(self::PATH_NGROK))
        {
            mkdir(self::PATH_NGROK,0775,true);
        }
        if($input->hasOption('remove') && $input->getOption('remove')===true)
        {
            $output->writeln('<info>Removing Config File</info>');
            if(file_exists(self::PATH_CONFIG))
            {
                unlink(self::PATH_CONFIG);
                $output->writeln('done');
                return 0;
            }
            $output->writeln('<error>No config file exists!</error>');
            return 0;
        }
        $output->writeln('<info>Creating Config File</info>');
        $config = ['web_addr'=>$input->getArgument('port').':4040'];
        $yaml = Yaml::dump($config);
        file_put_contents(self::PATH_CONFIG,$yaml);
        $output->writeln('done');
    }
}