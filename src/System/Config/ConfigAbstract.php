<?php
declare(strict_types=1);
namespace App\System\Config;

/**
 * Class ConfigAbstract
 * @package App\System\Config
 * @author chris westerfield <chris@mjr.one>
 */
class ConfigAbstract
{
    /**
     * @return array
     */
    public function toArray():array
    {
        $result = [];
        $keys = get_object_vars($this);
        foreach($keys as $key=> $value)
        {
            if($value!==null)
            {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}