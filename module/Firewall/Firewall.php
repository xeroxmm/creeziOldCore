<?php

namespace Firewall;

class Firewall {
    public $lastHop = FALSE;

    private $status           = NULL;
    private $silent           = FALSE;
    private $isLambdaTest     = FALSE;
    private $isReferrerTest   = FALSE;
    private $lambdaURL        = '';
    private $isInitialRequest = FALSE;
    private $specificURL      = NULL;
    private $specificGETs     = [];
    private $doFirewallOnly   = FALSE;
    private $doRefCheck       = TRUE;
    private $checkRefURL      = '';
    private $globalRefURL     = '';

    private $useBlacklist                = TRUE;
    private $useProxyDetection           = TRUE;
    private $useCurlDetection            = TRUE;
    private $useSessionDetection         = TRUE;
    private $useJSDetection              = TRUE;
    private $useHeadlessBrowserDetection = TRUE;
    private $useCookieDetection          = TRUE;

    private $ruleBlacklist           = NULL;
    private $ruleProxyList           = NULL;
    private $ruleJSList              = NULL;
    private $ruleHeadlessBrowserList = NULL;
    private $ruleSessionList         = NULL;

    private $varSessionCheck      = 's901';
    private $varCookieCheck       = 'jskum';
    private $metaRefreshInSeconds = 200;

    private $forwardOnSuccessToURL  = NULL;
    private $forwardOnSuccessViaURL = NULL;

    const harmless        = 100;
    const blacklist       = 0;
    const proxy           = 1;
    const noJS            = 2;
    const noSessionCookie = 3;
    const noCookie        = 4;
    const headlessBrowser = 5;
    const noHTML5         = 6;
    const noSockets       = 7;
    const noTopTier       = 8;
    const noReferrer      = 9;
    const isTrading       = 10;

    function __construct( string $testURL = '/gateout' ) {
        $this->lambdaURL = $testURL;

        $this->doReferrerTest();
        $this->doLambdaTest($testURL);
        $this->doInitialTest();
        $this->setInitialVars();

        $this->ruleBlacklist = new FirewallRules();
        $this->ruleProxyList = new FirewallRules();
        $this->ruleJSList = new FirewallRules();
        $this->ruleHeadlessBrowserList = new FirewallRules();
        $this->ruleSessionList = new FirewallRules();

        $this->status = new FirewallStatus();
    }

    public function setReferrerCheckURL( string $url, ?string $globalURL = '' ) {
        $this->doRefCheck = TRUE;
        $this->checkRefURL = (!empty($globalURL)) ? 'http://' . $_SERVER[ 'HTTP_HOST' ] . $url : 'http://' . $_SERVER[ 'HTTP_HOST' ] . $globalURL;
        $this->globalRefURL = (!empty($globalURL)) ? $globalURL : '';

        if (!empty($globalURL) &&
            (substr(parse_url($_SERVER[ "REQUEST_URI" ], PHP_URL_PATH), 0, 1 * strlen($globalURL)) == $globalURL) &&
            isset($_SESSION[ 'firewallActive' ]) &&
            $_SESSION[ 'firewallActive' ] == 1
        ) {
            $this->isReferrerTest = TRUE;
        }

        $_SESSION[ 'firewallReferrerURL' ] = $url;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isFinalHop(): bool {
        return $_SESSION[ 'firewallFinal' ] ?? TRUE;
    }

    public function getStatusByInteger( int $rule ): bool {
        return isset($_SESSION[ 'firewallRule' ][ $rule ]);
    }

    public function setMetaRefreshTimer( int $seconds ) { $this->metaRefreshInSeconds = $seconds; }

    public function setFirewallOnSpecificURL( string $url ) { $this->specificURL = $url; }

    public function setFirewallOnSpecificGETs( array $gets ) { $this->specificGETs = $gets; }

    public function getStatus(): FirewallStatus { return $this->status; }

    public function addBlacklistForwardToURLs( array $urls ) {
        $this->ruleBlacklist->addURLs($urls);
    }

    public function addSessionDetectionForwardToURLs( array $urls ) {
        $this->ruleSessionList->addURLs($urls);
    }

    public function addProxyDetectionForwardToURLs( array $urls ) {
        $this->ruleProxyList->addURLs($urls);
    }

    public function addHeadlessBrowserDetectionForwardToURLs( array $urls ) {
        $this->ruleHeadlessBrowserList->addURLs($urls);
    }

    public function addCookieDetectionForwardToURLs( array $urls ) {
        $this->ruleJSList->addURLs($urls);
    }

    public function addStandardForwardToURLs( array $urls ) {
        $this->ruleBlacklist->addURLs($urls);
        $this->ruleProxyList->addURLs($urls);
        $this->ruleHeadlessBrowserList->addURLs($urls);
        $this->ruleJSList->addURLs($urls);
        $this->ruleSessionList->addURLs($urls);
    }

    public function doSilentBlock( bool $do ) { $this->silent = $do; }

    public function doFirewallOnly( bool $bool ) { $this->doFirewallOnly = $bool; }

    public function forwardOnSuccessToURL( string $url, ?string $via = NULL ) {
        $this->forwardOnSuccessToURL = $url;
        $this->forwardOnSuccessViaURL = $via;
    }

    public function enable() {
        if (
            (
                ($this->specificURL === NULL || $this->isRequestEqualURL($this->specificURL)) &&
                ($this->specificGETs === NULL || $this->isRequestEqualGET($this->specificGETs))
            ) ||
            $this->isLambdaTest ||
            $this->isReferrerTest
        ) {
            $this->test();

            if (!$this->silent) {
                echo var_dump($this->status);
                echo var_dump($_SESSION);
                echo var_dump($_COOKIE);
            }
            if ($this->doFirewallOnly)
                exit;
        } else
            $_SESSION[ 'firewallFinal' ] = FALSE;

    }

    private function sendJS_VIA( string $url, string $via ) {
        $_SESSION[ 'firewallForwardDestination' ] = $url;
        $this->sendJS($via);
    }

    private function test() {
        // PURE PHP TESTS
        if (!$this->isReferrerTest && !$this->isLambdaTest && $this->isInitialRequest) {
            $_SESSION[ $this->varSessionCheck ] = 1;
            /*if (!$this->isLambdaTest && $this->isBlacklist() && $this->silent)
                $this->sendJS($this->ruleBlacklist->getURL());
            else if (!$this->isLambdaTest && $this->isProxy() && $this->silent)
                $this->sendJS($this->ruleProxyList->getURL());*/

            $_SESSION[ 'firewallActive' ] = 1;
            if (isset($_GET[ 'td' ]))
                $_SESSION[ 'firewallRule' ][ self::isTrading ] = TRUE;

            $this->isBlacklist();
            $this->isProxy();

            $_SESSION[ 'firewallStatusObj' ] = $this->status;

            $this->outInitialRequest();
            exit;
        }

        // TESTS WHERE Javascript is involved
        //
        // cURL, js-Test and js-Cookies need predefined VALUES from the SCRIPT
        else if ($this->isLambdaTest) {
            $_SESSION[ 'firewallTestLambda' ] = TRUE;
            /*if (($this->isSessionFraud() || $this->isCookieFraud()) && $this->silent)
                $this->sendJS($this->ruleJSList->getURL());*/
            if ($this->isSessionFraud()) {
                $_SESSION[ 'firewallFinal' ] = TRUE;
                $this->lastHop = TRUE;
                $_SESSION[ 'firewallNextReferrerTest' ] = FALSE;

                return;
            };
            $this->isCookieFraud();

            $this->doHeadlessBrowserDetection();
            $_SESSION[ 'firewallStatusObj' ] = $this->status;
            if (TRUE || $this->doRefCheck) {
                $_SESSION[ 'firewallNextReferrerTest' ] = TRUE;
                $this->sendJS($this->checkRefURL);
            } else {
                $_SESSION[ 'firewallFinal' ] = TRUE;
                $this->lastHop = TRUE;
            }
            exit;
        } else if ($this->isReferrerTest) {
            if (!$this->silent)
                print_r($_SESSION);

            $this->isReferrerMissingTest();
            // $_SESSION[ 'firewallStatusObj' ] = $this->status;
            $_SESSION[ 'firewallFinal' ] = TRUE;
            $this->lastHop = TRUE;
            $_SESSION[ 'firewallNextReferrerTest' ] = FALSE;
        } else {
            return;
        }
    }

    private function isReferrerMissingTest(): bool {
        if (!isset($_SERVER[ 'HTTP_REFERER' ]) || $this->status->isReferrerMissing = (stripos($_SERVER[ "HTTP_REFERER" ], $_SERVER[ "HTTP_HOST" ]) === FALSE)) {
            $_SESSION[ 'firewallRule' ][ self::noReferrer ] = TRUE;

            return FALSE;
        }

        return TRUE;
    }

    private function doHeadlessBrowserDetection() {
        if (!$this->useHeadlessBrowserDetection)
            return;

        $browserTimeZone = $_COOKIE[ 'st' ] ?? NULL;
        $displayString = $_COOKIE[ 'wd' ] ?? NULL;

        $hlWindowOuter = $_COOKIE[ 'wdx' ] ?? TRUE;
        $hlPhantom = $_COOKIE[ 'tvha' ] ?? TRUE;
        $hlEmit = $_COOKIE[ 'tvhb' ] ?? TRUE;
        $hlSelenium = $_COOKIE[ 'tvhc' ] ?? TRUE;
        $hlAuto = $_COOKIE[ 'tvhd' ] ?? TRUE;
        $htmlAudio = $_COOKIE[ 'wba' ] ?? TRUE;
        $htmlWorker = $_COOKIE[ 'wbw' ] ?? TRUE;
        $htmlSockets = $_COOKIE[ 'wbs' ] ?? TRUE;
        $stacktrace = $_COOKIE[ 'wbc' ] ?? TRUE;

        if ($this->status->isHeadlessBrowser = ($hlPhantom || $hlEmit || $hlAuto || $hlSelenium || $stacktrace)) {
            $_SESSION[ 'firewallRule' ][ self::headlessBrowser ] = TRUE;
        } else if ($this->status->isNoHTML5 = ($htmlAudio)) {
            $_SESSION[ 'firewallRule' ][ self::noHTML5 ] = TRUE;
        } else if ($this->status->isWebsocket = ($htmlWorker || $htmlSockets)) {
            $_SESSION[ 'firewallRule' ][ self::noSockets ] = TRUE;
        } else if ($this->status->isNoTopTier = ($hlWindowOuter || empty($displayString))) {
            $_SESSION[ 'firewallRule' ][ self::noTopTier ] = TRUE;
        } else {
            if (empty($_SESSION[ 'firewallRule' ])) {
                $this->status->isHarmless = TRUE;
                $_SESSION[ 'firewallRule' ][ self::harmless ] = TRUE;
            }
        }
    }

    private function doInitialTest() {
        if ($this->isLambdaTest)
            return;

        $this->isInitialRequest = (!isset($_SESSION[ 'firewallStatus' ]));
    }

    private function setInitialVars() {
        if (session_status() == PHP_SESSION_NONE)
            session_start();

        $_SESSION[ 'firewallPoints' ] = $_SESSION[ 'firewallPoints' ] ?? 0;
        $_SESSION[ 'firewallStatus' ] = $_SESSION[ 'firewallStatus' ] ?? TRUE;
        $_SESSION[ 'firewallActive' ] = $_SESSION[ 'firewallActive' ] ?? 0;
        $_SESSION[ 'firewallRule' ] = $_SESSION[ 'firewallRule' ] ?? [];
        $_SESSION[ 'firewallTestLambda' ] = $_SESSION[ 'firewallTestLambda' ] ?? FALSE;
        $_SESSION[ 'firewallForwardDestination' ] = $_SESSION[ 'firewallForwardDestination' ] ?? $_SERVER[ 'HTTP_HOST' ];
        $_SESSION[ 'firewallFinal' ] = $_SESSION[ 'firewallFinal' ] ?? TRUE;
        $_SESSION[ 'firewallNextReferrerTest' ] = $_SESSION[ 'firewallNextReferrerTest' ] ?? FALSE;
        $_SESSION[ 'firewallReferrerURL' ] = $_SESSION[ 'firewallReferrerURL' ] ?? '{';

        $_SESSION[ 'zeroparkCID_ID' ] = (isset($_GET[ 'zp' ]) && isset($_GET[ 'cid' ])) ? $_GET[ 'cid' ] : ($_SESSION[ 'zeroparkCID_ID' ] ?? NULL);
    }

    private function doReferrerTest() {
        if ($this->isRequestEqualURL($_SESSION[ 'firewallReferrerURL' ] ?? '{') && isset($_SESSION[ 'firewallNextReferrerTest' ]) && $_SESSION[ 'firewallNextReferrerTest' ] === TRUE) {
            $this->isReferrerTest = TRUE;
        }
    }

    private function doLambdaTest( string $testURL ) {
        if ($this->isRequestEqualURL($testURL) && isset($_SESSION[ 'firewallTestLambda' ]) && !$_SESSION[ 'firewallTestLambda' ]) {
            $this->isLambdaTest = TRUE;
        }
    }

    private function isRequestEqualGET( array $get ): bool {
        foreach ($get as $value) {
            if (isset($_GET[ $value ]))
                return TRUE;
        }

        return FALSE;
    }

    private function isRequestEqualURL( string $testURL ): bool {
        return (substr(parse_url($_SERVER[ "REQUEST_URI" ], PHP_URL_PATH), -1 * strlen($testURL)) == $testURL);
    }

    private function send( string $url ) {
        if ($url === FALSE)
            exit;

        $this->sendJS($url);
    }

    public function sendJS( string $url ) {
        $this->jsHeader();

        echo '<script>var nxR=' . mt_rand(10000, 99999) . ',dacolp=' . $this->jsObfuscateString($url) . ';window.location.replace(dacolp);</script>';

        $this->jsBody();
        exit;
    }

    private function jsObfuscateString( string $string ) {
        $s = '"';
        for ($i = 0; $i < strlen($string); $i++) {
            $s .= $string[ $i ];
            if ($i % 3 == 0)
                $s .= '"+"';
        }
        $s .= '"';

        return $s;
    }

    private function jsHeader() {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8" /><link rel="icon" href="data:,">';
    }

    private function jsBody() {
        echo '</head><body></body></html>';
    }

    private function isSessionFraud(): bool {
        if (!$this->useSessionDetection)
            return FALSE;

        $this->status->isSession = !isset($_SESSION[ $this->varSessionCheck ]);

        if ($this->status->isSession)
            $_SESSION[ 'firewallRule' ][ self::noSessionCookie ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 5;

        return $this->status->isSession;
    }

    private function isCookieFraud() {
        if (!$this->useCookieDetection)
            return FALSE;

        $this->status->isCookie = !isset($_COOKIE[ $this->varCookieCheck ]);

        if ($this->status->isCookie)
            $_SESSION[ 'firewallRule' ][ self::noCookie ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 10;

        return $this->status->isCookie;
    }

    private function isBlacklist(): bool {
        if (!$this->useBlacklist)
            return FALSE;

        $this->status->isBlacklist = check_blacklist("");

        if ($this->status->isBlacklist)
            $_SESSION[ 'firewallRule' ][ self::blacklist ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 15;

        return $this->status->isBlacklist;
    }

    private function isProxy(): bool {
        if (!$this->useProxyDetection)
            return FALSE;

        $proxy_headers = [
            'CLIENT_IP',
            'FORWARDED',
            'FORWARDED_FOR',
            'FORWARDED_FOR_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR_IP',
            'HTTP_PC_REMOTE_ADDR',
            'HTTP_PROXY_CONNECTION',
            'HTTP_VIA',
            'HTTP_X_FORWARDED',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED_FOR_IP',
            'HTTP_X_IMFORWARDS',
            'HTTP_XROXY_CONNECTION',
            'VIA',
            'X_FORWARDED',
            'X_FORWARDED_FOR'
        ];
        foreach ($proxy_headers as $header) {
            if (isset($_SERVER[ $header ])) {
                $this->status->isProxy = TRUE;
                $_SESSION[ 'firewallRule' ][ self::proxy ] = TRUE;

                return TRUE;
            }
        }

        $_SESSION[ 'firewallPoints' ] += 10;

        return ($this->status->isProxy = FALSE);
    }

    private function jsMetaRefresh() {
        if ($this->useCurlDetection || $this->useJSDetection || $this->useCookieDetection) {
            $url = $this->ruleJSList->getURL();

            if ($url !== FALSE)
                echo '<meta http-equiv="refresh" content="' . $this->metaRefreshInSeconds . '; URL=' . $url . '">';
        }
    }

    private function jsTestFull() {
        if ($this->useCookieDetection) {
            echo '<script>var tX="' . $this->lambdaURL . '";';
            echo '
            !function(){var t=function(){for(var t="",n=arguments,e=0;e<n.length;e++)t+=n[e];return t},n=function(){for(var t="",n=arguments,e=0;e<n.length;e++)t+=n[e]+"_";return t},e=t("sc","ree","n"),o=document,i=window,c=(o.location,function(t){i.location.replace(t)}),u="undefined"==typeof tX||null===tX?"/":tX,a="undefined"==typeof tC||null===tC||"boolean"!=typeof tc||tC,r=function(t){return!(void 0===t||null===t)},f=function(t,n,e){var i=new Date;i.setTime(i.getTime()+24*e*60*60*1e3);var c="expires="+i.toUTCString();o.cookie=t+"="+n+";"+c+";path=/"},d=function(){if(!0===a){f("jskum",t("xdf","tty","as0"),.1)}},w=function(){var t=i.innerWidth,o=i.innerHeight,c=i[e].availWidth,u=i[e].availHeight;f("wd",n(t,o,c,u),1),f("st",(new Date).getTimezoneOffset(),1)},h=function(t){return 0===t},v=function(){f("tvha",0,.1),f("tvhb",0,.1),f("tvhc",0,.1),f("tvhd",0,.1),f("wba",0,1),f("wbc",0,1),f("wbs",0,1),f("wbw",0,1);!function(){h(i.outerWidth)&&h(i.outerHeight)?f("wdx",1,.1):f("wdx",0,.1)}(),function(){(r(i._phantom)||r(i.__phantomas))&&f("tvha",1,.1),(r(i.Buffer)||r(i.emit))&&f("tvhb",1,.1),(r(i.spawn)||r(i.webdriver))&&f("tvhc",1,.1),r(i.domAutomation)&&f("tvhd",1,.1);try{i.AudioContext=i.AudioContext||i.webkitAudioContext,context=new AudioContext}catch(t){f("wba",1,1)}"WebSocket"in window||"MozWebSocket"in window||f("wbs",1,1),window.Worker||f("wbw",1,1);try{null[0]}catch(n){var t=n.stack;(t.indexOf("phantomjs")>-1||t.indexOf("couchjs")>-1)&&f("wbc",1,1)}}()},b=function(){c("https://creezi.com"+u)};!function(){d(),w(),v(),b()}()}();
            ';
            echo '</script>';
            //echo "<script>window.location.href = 'http://tubesearch.co';</script>";
        }
    }

    private function outInitialRequest() {
        $this->jsHeader();
        $this->jsMetaRefresh();
        $this->jsTestFull();
        $this->jsBody();

    }
}