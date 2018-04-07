<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 01.04.18
 * Time: 23:02
 */

namespace App\Process;


use App\System\Config\Database;

interface DatabaseInterface
{
    /**
     * @param string $db
     * @param bool $create
     * @param bool $delete
     */
    public function db(?Database $db,bool $create=false,bool $delete=false):void;
}