<?php

interface databasePattern {
    function __construct();

    public function getDatabaseName():?string;

    public function queryRaw(string $sqlString):?dbQuerySet;

    public function close(): bool;

    public function getLastError(): string;

    public function getHarmonizedString(string $string): string;
}