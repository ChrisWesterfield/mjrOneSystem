<?php
declare(strict_types=1);
namespace App\System;


class FlushHelper
{
    static $BUFFER_SIZE_APACHE = 65536;

    /**
     * @var mixed|null 
     */
    protected $bufferSize;

    /**
     * FlushHelper constructor.
     * @param null $bufferSize
     */
    function __construct($bufferSize = null) {
        $this->bufferSize = $bufferSize ? $bufferSize : max(static::$BUFFER_SIZE_APACHE, 0);
    }

    /**
     * @param $output
     */
    function out($output) {
        if(!is_scalar($output))
        {
            throw new InvalidArgumentException();
        }
        echo $output;
        echo str_repeat(' ', min($this->bufferSize,  $this->bufferSize - strlen($output)));
        ob_flush();
        flush();
    }
}