<?php

namespace Firewall;

class FirewallRules {
    private $urls = [ 'http://hentaixxxchuck.com' ];

    public function addURLs( array $urls ): FirewallRules {
        $this->urls = $urls;

        return $this;
    }

    public function getURL(){
        $count = count($this->urls);
        if($count < 1)
            return FALSE ;

        return $this->urls[mt_rand(0, $count-1)];
    }
}