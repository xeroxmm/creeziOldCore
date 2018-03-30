<?php

namespace Firewall;

class Distributor {
    private $fw           = NULL;
    private $isLogClients = FALSE;
    /** @var null | Logger */
    private $logger = NULL;
    private $tier_1 = NULL;
    private $tier_2 = NULL;
    private $tier_3 = NULL;
    private $tier_4 = NULL;

    /** @var null|DistributorURLs */
    private $tierFallback = NULL;
    private $rules        = [];

    function __construct( Firewall_V2 $firewall ) {
        $this->fw = $firewall;
    }

    public function logClients( bool $logClients ) { $this->isLogClients = $logClients; }

    /** @param $ints int[] */
    function setTier_1( array $ints ): DistributorURLs {
        if ($this->tier_1 === NULL)
            $this->tier_1 = new DistributorURLs();

        foreach ($ints as $int) {
            $this->rules[ $int ] = &$this->tier_1;
        }

        return $this->tier_1;
    }

    /** @param $ints int[] */
    function setTier_2( array $ints ): DistributorURLs {
        if ($this->tier_2 === NULL)
            $this->tier_2 = new DistributorURLs();

        foreach ($ints as $int) {
            $this->rules[ $int ] = &$this->tier_2;
        }

        return $this->tier_2;
    }

    /** @param $ints int[] */
    function setTier_3( array $ints ): DistributorURLs {
        if ($this->tier_3 === NULL)
            $this->tier_3 = new DistributorURLs();

        foreach ($ints as $int) {
            $this->rules[ $int ] = &$this->tier_3;
        }

        return $this->tier_3;
    }

    /** @param $ints int[] */
    function setTier_4( array $ints ): DistributorURLs {
        if ($this->tier_4 === NULL)
            $this->tier_4 = new DistributorURLs();

        foreach ($ints as $int) {
            $this->rules[ $int ] = &$this->tier_4;
        }

        return $this->tier_4;
    }
	
    /** @param $ints int[] */
    function setTier_5( array $ints ): DistributorURLs {
        if ($this->tier_5 === NULL)
            $this->tier_5 = new DistributorURLs();

        foreach ($ints as $int) {
            $this->rules[ $int ] = &$this->tier_5;
        }

        return $this->tier_5;
    }	
	

    public function go() {
        if ($this->logger === NULL)
            $this->logger = new Logger(FALSE);

        if ($_SESSION['firewallSkip'] == 1 || !$this->fw->isDone()) {
            // echo "no final hop";
            return;
        }
        $_SESSION['firewallSkip'] = 1;
        $this->sendByRule();
    }

    public function setLogger( Logger $logger ) { $this->logger = $logger; }

    function getTierFallback(): DistributorURLs {
        if ($this->tierFallback === NULL)
            $this->tierFallback = new DistributorURLs();

        return $this->tierFallback;
    }

    private function sendByRule() {
        // sort the rules by key value
        ksort($this->rules);

        // loop through
        /**
         * @var  $ruleInteger DistributorURLs[]
         * @var  $distr DistributorURLs
         */
        //print_r($this->rules);

        foreach ($this->rules as $ruleInteger => $distr) {
            if ($this->fw->getStatusByInteger($ruleInteger) === TRUE) {
                $this->logClient($ruleInteger);
                $distr->sendConvRohitX($_SESSION[ 'zeroparkCID_ID' ] ?? NULL);
                $this->fw->sendJS($distr->getURLRandom());
            }
        }
        if ($this->tierFallback !== NULL) {
            $this->logClient(-1);
            $this->fw->sendJS($this->tierFallback->getURLRandom());
        }
        $this->logClient(-100);
        header('Location: http://creezi.com/popular?offer=trial');
        exit;
    }

    private function logClient( int $rule ) {
        if (!$this->isLogClients)
            return;
        $this->logger->doEventExitFinal($rule);
    }
}