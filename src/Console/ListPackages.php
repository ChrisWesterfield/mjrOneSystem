<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 31.03.18
 * Time: 15:24
 */

namespace App\Console;


use App\System\SystemConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListPackages extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:packages:list')
            ->setHelp('lists installed packages')
            ->setDescription('lists installed packages');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = SystemConfig::get();
        if($config->getFeatures()->count() < 1)
        {
            $output->writeln('<error>No Packages Installed</error>');
            return 0;
        }
        foreach($config->getFeatures() as $feature)
        {
            $output->writeln(str_replace('App\\Process\\','',$feature));
        }
    }

}