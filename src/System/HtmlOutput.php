<?php
declare(strict_types=1);
namespace App\System;
use Symfony\Component\Console\Output\Output;
class HtmlOutput extends Output
{
    /**
     * @var FlushHelper
     */
    protected $helper;
    public function setHelper(FlushHelper $fh)
    {
        $this->helper = $fh;
    }

    /**
     * @param string $message
     * @param bool $newline
     */
    protected function doWrite($message, $newline)
    {
        $this->helper->out($message.($newline?'<br><br>':''));
    }
}