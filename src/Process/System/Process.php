<?php
declare(strict_types=1);
namespace App\Process\System;

/**
 * Class Process
 * @package App\Process\System
 * @author chris westerfield <chris@mjr.one>
 */
class Process
{
    /**
     * @var string|null
     */
    protected $user;
    /**
     * @var string|null
     */
    protected $processId;
    /**
     * @var string|null
     */
    protected $parentProcessId;
    /**
     * @var string|null
     */
    protected $startTime;
    /**
     * @var string|null
     */
    protected $cpuTime;
    /**
     * @var string|null
     */
    protected $tty;
    /**
     * @var string
     */
    protected $command = '';
    /**
     * @var string|null
     */
    protected $commandLine;

    /**
     * Process constructor.
     * @param array $command
     */
    public function __construct(array $command)
    {
        $cmdC = count($command);
        if ($cmdC > 7) {
            $this->commandLine = $command[7];
            $this->command = $command[7];
            if ($cmdC > 8) {
                for ($i = 8; $i < $cmdC; $i++) {
                    $this->commandLine .= ' ' . $command[$i];
                }
            }
            $this->setUser($command[0]);
            $this->setProcessId($command[1]);
            $this->setParentProcessId($command[2]);
            if (preg_match('/\d+:\d+/', $command [4])) {
                $startTime = date('Y-m-d H:i:s', strtotime($command [4]));
            } else {
                $startTime = date('Y-m-d', strtotime($command [4])) . ' ??:??:??';
            }
            $this->setStartTime($startTime);
            $this->setCpuTime($command[5]);
            $this->setTty($command[6]);
        }
    }

    /**
     * @return null|string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param null|string $user
     * @return Process
     */
    public function setUser(?string $user): Process
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    /**
     * @param null|string $processId
     * @return Process
     */
    public function setProcessId(?string $processId): Process
    {
        $this->processId = $processId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParentProcessId(): ?string
    {
        return $this->parentProcessId;
    }

    /**
     * @param null|string $parentProcessId
     * @return Process
     */
    public function setParentProcessId(?string $parentProcessId): Process
    {
        $this->parentProcessId = $parentProcessId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * @param null|string $startTime
     * @return Process
     */
    public function setStartTime(?string $startTime): Process
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCpuTime(): ?string
    {
        return $this->cpuTime;
    }

    /**
     * @param null|string $cpuTime
     * @return Process
     */
    public function setCpuTime(?string $cpuTime): Process
    {
        $this->cpuTime = $cpuTime;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTty(): ?string
    {
        return $this->tty;
    }

    /**
     * @param null|string $tty
     * @return Process
     */
    public function setTty(?string $tty): Process
    {
        $this->tty = $tty;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return Process
     */
    public function setCommand(string $command): Process
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCommandLine(): ?string
    {
        return $this->commandLine;
    }

    /**
     * @param null|string $commandLine
     * @return Process
     */
    public function setCommandLine(?string $commandLine): Process
    {
        $this->commandLine = $commandLine;
        return $this;
    }
}