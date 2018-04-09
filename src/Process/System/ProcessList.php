<?php
declare(strict_types=1);

namespace App\Process\System;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Class ProcessList
 * @package App\Process\System
 * @author chris westerfield <chris@mjr.one>
 */
class ProcessList implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array|Process[]
     */
    protected $processList;

    /**
     * ProcessList constructor.
     * @param bool $load
     */
    public function __construct($load = true)
    {
        if ($load)
        {
            $this->Refresh();
        }
    }

    /**
     * @param $id
     * @return Process|bool|mixed
     */
    public function GetProcess($id)
    {
        foreach ($this->processList as $process) {
            if ($process->getProcessId() == $id)
                return ($process);
        }

        return (false);
    }

    /**
     * @param $name
     * @return array
     */
    public function GetProcessByName($name)
    {
        $result = [];

        foreach ($this->processList as $process) {
            if ($process->getCommand() == $name)
                $result [] = $process;
        }

        return ($result);
    }

    /**
     * @param $id
     * @return array
     */
    public function GetChildren($id)
    {
        $result = [];
        foreach ($this->processList as $process) {
            if ($process->getParentProcessId() == $id)
                $result [] = $process;
        }

        return ($result);
    }

    /**
     *
     */
    public function Refresh()
    {
        $process = new \Symfony\Component\Process\Process("ps -aefwwww");
        $process->run();
        $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);

        $this->processList = [];
        foreach($iterator as $item)
        {
            $lines = explode("\n",$item);
            foreach($lines as $id=>$line)
            {
                if($id<1)
                {
                    continue;
                }
                $line		=  trim ( $line ) ;
                $lineArray = preg_split('/\s+/', $line);
                if(count($lineArray) > 7 && strpos('[', substr($lineArray[7],0,1))===false)
                {
                    $this->processList[] = new Process($lineArray);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function  Count ( )
    {
        return ( count ( $this->processList ) ) ;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function  getIterator ( )
    {
        return ( new \ArrayIterator ( $this->processList ) ) ;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function  offsetExists ( $offset )
    {
        return ( $offset  >=  0  &&  $offset  <  count ( $this->processList ) ) ;
    }

    /**
     * @param mixed $offset
     * @return Process|mixed
     */
    public function  offsetGet ( $offset )
    {
        return ( $this->processList [ $offset ] ) ;
    }

    /**
     * @param mixed $offset
     * @param mixed $member
     * @throws \Exception
     */
    public function  offsetSet ( $offset, $member )
    {
        throw ( new \Exception ( "Unsupported operation.") ) ;
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function  offsetUnset ( $offset )
    {
        throw ( new \Exception ( "Unsupported operation.") ) ;
    }

    /**
     * @return Process[]|array
     */
    public function getProcessList()
    {
        return $this->processList;
    }
}