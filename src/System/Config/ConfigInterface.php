<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Interface ConfigInterface
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
interface ConfigInterface
{
    public function toArray();
}