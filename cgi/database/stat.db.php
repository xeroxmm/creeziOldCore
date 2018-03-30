<?php

class DB
{
    private static $instanceName = 'statDB';
    private static $status = FALSE;
    private static $lastID = -1;
    private static $isInit = FALSE;
    /** @var databasePattern */
    private static $databaseObj;
    private static $databaseEngine = 1;

    public static function getStatus(): bool
    {
        return self::$status;
    }

    public static function getEngine(): int
    {
        return self::$databaseEngine;
    }

    public static function getLastID(): int
    {
        return self::$lastID;
    }

    public static function getInitStatus(): bool
    {
        return self::$isInit;
    }

    private static function initMain(): bool
    {
        if (self::$isInit)
            return TRUE;

        if (self::$databaseEngine == 1)
            return self::initMySQL();

        errorLog::addError(self::$instanceName, 'Database Engine (#' . self::$databaseEngine . ') is not implemented yet', __FILE__, __LINE__);

        return FALSE;
    }

    private static function initMySQL(): bool
    {
        self::$databaseObj = new dbMySQL();

        return self::$databaseObj->isInit();
    }

    public static function queryRaw(string $sqlString):?dbQuerySet
    {
        if (!self::initMain()) {
            errorLog::addError(self::$instanceName, 'Query (' . $sqlString . ') could not be send', __FILE__, __LINE__);

            return new dbQuerySet(FALSE);
        }

        return self::$databaseObj->queryRaw($sqlString);
    }

    public static function harmAndString(string $s): string
    {
        if (!self::initMain()) {
            errorLog::addError(self::$instanceName, 'Query (' . $sqlString . ') could not be send', __FILE__, __LINE__);

            return new dbQuerySet(FALSE);
        }
        return "'" . self::$databaseObj->getHarmonizedString($s) . "'";
    }
}