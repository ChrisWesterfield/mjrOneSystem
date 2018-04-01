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

class ListDependencies extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('mjrone:packages:requirements')
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
        if($config->getRequirements()->count() < 1)
        {
            $output->writeln('<error>No Requirements Available</error>');
            return 0;
        }
        foreach($config->getRequirements() as $id=>$feature)
        {
            $output->writeln('<comment>dependencies of package '.str_replace('App\\Process\\','',$id).'</comment>');
            if($feature->count() > 0)
            {
                foreach($feature as $v)
                {
                    $output->writeln(str_replace('App\\Process\\','',$v));
                }
                continue;
            }
            $output->writeln('<error>Package has no dependecies!</error>');
        }
    }

}