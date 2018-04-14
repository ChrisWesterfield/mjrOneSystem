<?php
declare(strict_types=1);
namespace App\Process;

/**
 * Class RestartHelper
 * @package App\Console
 * @author chris westerfield <chris@mjr.one>
 */
class ProcessHelper extends ProcessAbstract implements ProcessInterface
{
    public const EXCLUDE = true;
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