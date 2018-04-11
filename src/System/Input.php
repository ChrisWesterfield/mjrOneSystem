<?php
declare(strict_types=1);
namespace App\System;
use Symfony\Component\Console\Input\Input as BaseInput;

/**
 * Class Input
 * @package App\System
 * @author Chris Westerfield <chris@mjr.one>
 */
class Input extends BaseInput
{
    /**
     *
     */
    protected function parse()
    {
    }

    /**
     *
     */
    public function getFirstArgument()
    {
    }

    /**
     * @param $values
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
    }

    /**
     * @param $values
     * @param bool $default
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
    }
}