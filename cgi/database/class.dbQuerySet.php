<?php

class dbQuerySet {
    public $isValid = FALSE;
    public $amount  = 0;
    public $results = [];

    function __construct( bool $valid, int $amount = 0, array $results = [] ) {
        $this->isValid = $valid;
        $this->amount = $amount;
        $this->results = $results;
    }

    public function hasResults(): bool { return $this->amount > 0; }

    public function firstResultInt(string $key):int{
        return isset($this->results[0]) && isset($this->results[0]->{$key}) ? (int)$this->results[0]->{$key} : 0;
    }
}