<?php
declare(strict_types=1);
namespace App\System\Config;
use Doctrine\Common\Collections\ArrayCollection;

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
            if($value instanceof ArrayCollection)
            {
                $res = [];
                foreach($value as $v)
                {
                    if($v instanceof ConfigAbstract)
                    {
                        $res[] = $v->toArray();
                    }
                }
                $result[$key] = $res;
            }
            else
                if($value!==null)
            {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}