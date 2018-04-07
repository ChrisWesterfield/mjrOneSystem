<?php
declare(strict_types=1);
namespace App\Console;


use App\Process\ProcessAbstract;
use App\Process\ProcessInterface;

/**
 * Class RestartHelper
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class RestartHelper extends ProcessAbstract implements ProcessInterface
{

    /**
     * @return void
     */
    public function install(): void
    {
    }

    /**
     *
     */
    public function uninstall(): void
    {
    }

    /**
     * @return mixed
     */
    public function configure(): void
    {
    }
}