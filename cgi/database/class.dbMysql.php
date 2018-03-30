<?php

class dbMySQL implements databasePattern {
    private $databaseName = NULL;
    /** @var mysqli * */
    private $mql;
    private $isInit = FALSE;
    private $engine = 1;

    function __construct() {
        $this->initMySQL(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
        $this->databaseName = DB_NAME;
    }

    private function initMySQL( $host, $user, $pass, $name, $port ) {
        $this->mql = new mysqli($host, $user, $pass, $name, $port);

        if ($this->mql->connect_errno) {
            errorLog::addError('connectorMySQL', 'Connection failed: ' . $host . ',' . $user . ',' . $pass . ',' . $name . ',' . $port . '.', __FILE__, __LINE__);

            return;
        }

        $this->isInit = TRUE;
        $this->engine = 1;

        $this->queryRaw('SET NAMES \'utf8mb4\'');
        $this->queryRaw("SET CHARACTER SET 'utf8mb4'");

        return;
    }

    public function isInit(): bool { return $this->isInit; }

    public function getDatabaseName():?string { return $this->databaseName; }

    public function queryRaw( string $sqlString ): dbQuerySet {
        if (!$this->isInit) {
            errorLog::addError('connectorMySQL', 'DB-Connection has not initialized -> ' . $sqlString, __FILE__, __LINE__);

            return new dbQuerySet(FALSE);
        }


        $res = $this->mql->query($sqlString);

        if (!is_bool($res)) {
            $a = [];
            while ($obj = $res->fetch_object()) {
                $a[] = $obj;
            }
            mysqli_free_result($res);

            return new dbQuerySet(TRUE, count($a), $a);
        } else
            return new dbQuerySet($res);
    }

    public function close(): bool { return $this->mql->close(); }

    public function getLastError(): string { return '#' . $this->mql->errno . ' : ' . $this->mql->error; }

    public function getHarmonizedString( string $string ): string { return $this->mql->real_escape_string($string); }
}