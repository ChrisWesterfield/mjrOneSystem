<?php
declare(strict_types=1);
namespace App\System;

/**
 * Class Monitoring
 * @package App\System
 * @author chris westerfield <chris@mjr.one>
 */
class Monitoring
{
    /**
     * @var float
     */
    private $timeout=0.5;

    /**
     * @var array
     */
    protected $httpPorts = [
        81,
    ];

    /**
     * @param string $port
     * @param string $hostname
     * @param bool $advancedCheck
     * @return bool
     */
    public function checkStatus(string $port, string $hostname='127.0.0.1',bool $advancedCheck=false):bool
    {
        if($advancedCheck || in_array($port,$this->httpPorts))
        {
            return $this->checkHttp($hostname, $port);
        }
        return $this->check($hostname, $port);
    }

    /**
     * @param null|string $hostname
     * @param null|string $port
     * @param int|null $errorNumber
     * @param null|string $errString
     * @return resource
     */
    protected function openConnection(?string $hostname, ?int $port, ?int &$errorNumber, ?string &$errString)
    {
        return @fsockopen($hostname, $port, $errorNumber, $errString, $this->timeout);
    }

    /**
     * @param string $hostname
     * @param string $port
     * @return bool
     */
    protected function checkHttp(string $hostname, string $port):bool
    {
        $errorNumber = null;
        $errorString = null;
        $socket = $this->openConnection($hostname, (int)$port, $errorNumber, $errorString);
        if($errorNumber===0)
        {
            if(in_array($port, $this->httpPorts))
            {
                $response = $this->write($socket, "HEAD / HTTP/1.1\r\nHost: $hostname\r\nConnection:close\r\n\r\n");
                preg_match('/([1-5][0-5][0-9])/', $response, $statusCode);
                $code = (int)$statusCode[0];
                fclose($socket);
                if($code < 400)
                {
                    return true;
                }
            }
            else
            {
                return $this->check($hostname, $port);
            }
        }
        return false;
    }

    /**
     * @param string $hostname
     * @param string $port
     * @return bool
     */
    protected function check(string $hostname, string $port):bool
    {
        $socket = $this->openConnection($hostname, (int)$port, $errorNumber, $errorString);
        if($errorNumber == 0){
            fclose($socket);
            return true;
        }
        return false;
    }

    /**
     * @param $socket
     * @param $string
     * @return bool|string
     */
    protected function write($socket, $data)
    {
        if($socket && !empty($data)){
            fwrite($socket, $data);
            $resp = '';
            while(!feof($socket)){
                $resp.= fgets($socket);
            }
            return $resp;
        }
        return false;
    }
}