<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 02.04.18
 * Time: 10:41
 */

namespace App\Process;


class phpShell extends ProcessAbstract implements ProcessInterface
{
    public const PHP_CMD = '/usr/bin/php%s';
    public const COMMAND = self::SUDO.' '.self::UPD_ALT.' --set php %s';
    /**
     * @return void
     */
    public function install(): void{}
    /**
     *
     */
    public function uninstall(): void{}

    /**
     * @return mixed
     */
    public function configure(): void{}

    public function changeShell(string $shellVersion)
    {
        $php = sprintf(self::PHP_CMD, $shellVersion);
        if (!file_exists($php))
        {
            $this->getOutput()->writeln('<error>PHP Binary not found!</error>');
            return;
        }
        $this->getOutput()->writeln('<info>Changing PHP Binary</info>');
        $this->execute(sprintf(self::COMMAND,$php));
        $this->getOutput()->writeln('done');
    }
}