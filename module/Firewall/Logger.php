<?php

namespace Firewall;

class Logger {
    private $isActive        = FALSE;
    private $sessionID       = 0;
    private $useHop          = TRUE;
    private $useFirewallExit = TRUE;

    function __construct( bool $active ) {
        $this->isActive = $active;
        if (!$active)
            return;

        require_once dirname(__FILE__, 3) . '/cgi/__autoload.php';
    }

    public function noHop( bool $use ) { $this->useHop = !$use; }

    public function noExitFirewall( bool $use ) { $this->useFirewallExit = !$use; }

    public function getSessionID(): int { return $this->sessionID; }

    public function doEventInitial() {
        if (!$this->isActive)
            return;

        if (session_status() == PHP_SESSION_NONE)
            session_start();

        $this->doInitial();

        $_SESSION[ 'logger' ][ 'init' ] = 1;
        $_SESSION[ 'logger' ][ 'ID' ] = $this->getSessionID();
    }

    public function doEventHop( int $type ) {
        if (!$this->isActive || !$this->useHop)
            return;

        if ($this->doEventSessionLost(1))
            return;

        $sql = 'INSERT INTO `LoggerHopTable` (`IDID`,`type`,`time`) VALUES (' . $_SESSION[ 'logger' ][ 'ID' ] . ',' . $type . ',' . time() . ');';
        \db::queryRAW($sql);
    }

    public function doEventFirewallFinal( int $type ) {
        if (!$this->isActive || !$this->useFirewallExit)
            return;

        if ($this->doEventSessionLost(2))
            return;

        $sql = 'INSERT INTO `LoggerFirewallFinal` (`IDID`,`type`,`time`) VALUES (' . $_SESSION[ 'logger' ][ 'ID' ] . ',' . $type . ',' . time() . ');';
        \db::queryRAW($sql);
    }

    public function doEventExitFinal( int $type ) {
        if (!$this->isActive)
            return;

        if ($this->doEventSessionLost(3))
            return;

        $sql = 'INSERT INTO `LoggerExitFinal` (`IDID`,`type`,`time`) VALUES (' . $_SESSION[ 'logger' ][ 'ID' ] . ',' . $type . ',' . time() . ');';
        \db::queryRAW($sql);
        $sql = 'UPDATE `LoggerInitTable` SET `isDone` = 1 WHERE ID = ' . $_SESSION[ 'logger' ][ 'ID' ] . ';';
        \db::queryRAW($sql);
    }

    private function doEventSessionLost( int $identifier = 0 ): bool {
        if (!$this->isActive)
            return FALSE;

        if (isset($_SESSION[ 'logger' ][ 'ID' ]) && $_SESSION[ 'logger' ][ 'ID' ] > 0)
            return FALSE;

        $sql = 'INSERT INTO `LoggerLostSession` (`time`,`IP`, `session`) VALUES (' . time() . ',\'' . trim($_SERVER[ 'REMOTE_ADDR' ]) . '\',' . $identifier . ');';
        \db::queryRAW($sql);

        return TRUE;
    }

    private function doInitial() {
        if (!$this->isActive)
            return;

        $sql = 'INSERT INTO `LoggerInitTable` (`time`,`IP`) VALUES (' . time() . ',\'' . trim($_SERVER[ 'REMOTE_ADDR' ]) . '\');';
        \db::queryRAW($sql);
        $this->sessionID = \db::getLastID();
    }
}