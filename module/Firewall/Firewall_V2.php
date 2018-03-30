<?php

namespace Firewall;

class Firewall_V2 {
    private $URLhook = '';

    private $isInit         = FALSE;
    private $isLambda       = FALSE;
    private $isReferrer     = FALSE;
    private $isDone         = FALSE;
    public  $lastHop        = FALSE;
    private $isJSHeadNeeded = TRUE;

    private $useMetaRefresh    = FALSE;
    private $isJSCheckSeparate = FALSE;

    private $confSilent               = TRUE;
    private $confListenToGets         = [];
    private $confCookieCheck          = 'jskum';
    private $confURLWebsite           = 'https://creezi.com';
    private $confURLMetaRefresh       = '';
    private $confMetaRefreshInSeconds = 3;

    private $tempURLLambda   = '';
    private $tempURLReferrer = '';
    /** @var null|Logger */
    private $logger = NULL;

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
    const noIFrame        = 11;

    function __construct( string $hookURL ) {
        if (session_status() == PHP_SESSION_NONE)
            session_start();

        $this->URLhook = $hookURL;
    }

    public function setLogger( Logger $logger ) { $this->logger = $logger; }

    public function doSilentBlock( bool $isSilent ) { $this->confSilent = $isSilent; }

    public function setFirewallOnSpecificGETs( array $gets ) { $this->confListenToGets = $gets; }

    public function setReferrerCheckURL( string $url ) { $this->tempURLReferrer = $url; }

    public function setLambdaCheckURL( string $url ) { $this->tempURLLambda = $url; }

    public function getStatusByInteger( int $rule ): bool { return isset($_SESSION[ 'firewallRule' ][ $rule ]); }

    public function setMetaRefreshURL( string $url ) {
        $this->useMetaRefresh = TRUE;
        $this->confURLMetaRefresh = $url;
    }

    public function setDropFromNextTestPositiveBlacklistAndProxyTraffic( bool $status ) { $this->isJSCheckSeparate = $status; }

    public function sendJS( string $sendToURL ) {
        if ($this->isJSHeadNeeded)
            $this->jsHeader();

        echo "<script>window.location.replace( '$sendToURL' );</script>";

        $this->jsBody();
        exit;
    }

    public function isDone() {
        return $this->lastHop || $_SESSION[ 'firewallIsDone' ] == 1 || $_SESSION[ 'firewallActive' ] == 1 || isset($_COOKIE[ 'wdx' ]);
    }

    public function enable() {
        if(($_SESSION['firewallSkip'] ?? 0) == 1)
            return;

        if ($this->logger === NULL)
            $this->logger = new Logger(FALSE);

        // fill Session Vars to get all necessary information
        $this->doSessionVars();

        // Test, to identify the environment
        $this->doTestDone();
        $this->doTestInit();
        $this->doTestLambda();
        $this->doTestReferrer();

        // Do the final tests
        $this->doTests();
    }

    private function doSessionVars() {
        $_SESSION[ 'firewallActive' ] = $_SESSION[ 'firewallActive' ] ?? 0;
        $_SESSION[ 'firewallPoints' ] = $_SESSION[ 'firewallPoints' ] ?? 0;

        $_SESSION[ 'firewallIsInit' ] = $_SESSION[ 'firewallIsInit' ] ?? 0;
        $_SESSION[ 'firewallIsLambda' ] = $_SESSION[ 'firewallIsLambda' ] ?? 0;
        $_SESSION[ 'firewallIsReferrer' ] = $_SESSION[ 'firewallIsReferrer' ] ?? 0;
        $_SESSION[ 'firewallIsDone' ] = $_SESSION[ 'firewallIsDone' ] ?? 0;

        $_SESSION[ 'firewallURLLambda' ] = $_SESSION[ 'firewallURLLambda' ] ?? '';
        $_SESSION[ 'firewallURLReferrer' ] = $_SESSION[ 'firewallURLReferrer' ] ?? '';

        $_SESSION[ 'firewallRule' ] = $_SESSION[ 'firewallRule' ] ?? [];

        $_SESSION[ 'zeroparkCID_ID' ] = $_SESSION[ 'zeroparkCID_ID' ] ?? '';
        $_SESSION['firewallSkip'] = $_SESSION['firewallSkip'] ?? 0;
    }

    private function doTestDone() { $this->isDone = isset($_SESSION[ 'firewallIsDone' ]) && $_SESSION[ 'firewallIsDone' ] == 1; }

    private function doTestInit() {
        // Do URL check
        if (!$this->isURLEqualTo($this->URLhook) || !$this->isRequestEqualGET($this->confListenToGets))
            return;

        $this->isInit = TRUE;

        // Set the right vars on the Session
        $_SESSION[ 'firewallActive' ] = 1;
        $_SESSION[ 'firewallIsInit' ] += 1;
        $_SESSION[ 'firewallURLLambda' ] = $this->tempURLLambda;
        $_SESSION[ 'firewallURLReferrer' ] = (!empty($_SESSION[ 'firewallURLReferrer' ])) ? $_SESSION[ 'firewallURLReferrer' ] : $this->tempURLReferrer;

        // check for ZeroPark
        if (isset($_GET[ 'cid' ]))
            $_SESSION[ 'zeroparkCID_ID' ] = $_GET[ 'cid' ];

        // check if its trading
        if (isset($_GET[ 'td' ]))
            $_SESSION[ 'firewallRule' ][ self::isTrading ] = TRUE;

        // Log initialing
        $this->logger->doEventInitial();
    }

    private function doTestLambda() {
        if (!$this->isURLEqualTo($_SESSION[ 'firewallURLLambda' ]))
            return;

        $_SESSION[ 'firewallIsLambda' ] = 1;
        $this->isLambda = TRUE;
    }

    private function doTestReferrer() {
        if (!$this->isURLEqualTo($_SESSION[ 'firewallURLReferrer' ]))
            return;

        $_SESSION[ 'firewallIsReferrer' ] = 1;
        $this->isReferrer = TRUE;
    }

    private function isRequestEqualGET( array $arrayOfGETParam ): bool {
        foreach ($arrayOfGETParam as $value) {
            if (isset($_GET[ $value ]))
                return TRUE;
        }

        return FALSE;
    }

    private function isURLEqualTo( string $testURL ): bool {
        return (substr(parse_url($_SERVER[ "REQUEST_URI" ], PHP_URL_PATH), -1 * strlen($testURL)) == $testURL);
    }

    private function isBlacklist(): bool {
        $isBl = check_blacklist("");

        if ($isBl)
            $_SESSION[ 'firewallRule' ][ self::blacklist ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 15;

        return $isBl;
    }

    private function isProxy(): bool {
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
                $_SESSION[ 'firewallRule' ][ self::proxy ] = TRUE;

                return TRUE;
            }
        }

        $_SESSION[ 'firewallPoints' ] += 10;

        return FALSE;
    }

    private function doTests() {
        $this->analyzeClientsInit();
        $this->analyzeClientsLambda();
        $this->analyzeClientsReferrer();
    }

    private function analyzeClientsReferrer() {
        if (!$this->isReferrer)
            return;

        $this->isReferrerMissing();

        $_SESSION[ 'firewallIsDone' ] = 1;
        $this->lastHop = TRUE;
        $this->logger->doEventFirewallFinal($this->getRestrictiveRule());
    }

    private function getRestrictiveRule(): int {
        if (!isset($_SESSION[ 'firewallRule' ])) return self::noSessionCookie;
        else if (empty($_SESSION[ 'firewallRule' ])) return self::noTopTier;
        else return min(array_keys($_SESSION[ 'firewallRule' ]));
    }

    private function isReferrerMissing(): bool {
        if (!isset($_SERVER[ 'HTTP_REFERER' ]) || (stripos($_SERVER[ "HTTP_REFERER" ], $_SERVER[ "HTTP_HOST" ]) === FALSE)) {
            $_SESSION[ 'firewallRule' ][ self::noReferrer ] = TRUE;

            return TRUE;
        }

        return FALSE;
    }

    private function analyzeClientsLambda() {
        if (!$this->isLambda)
            return;

        $this->logger->doEventHop(1);

        if ($this->isSessionFraud()) {
            $_SESSION[ 'firewallIsDone' ] = 1;
            $this->lastHop = TRUE;
            $_SESSION[ 'firewallNextReferrerTest' ] = FALSE;
            $this->logger->doEventFirewallFinal(self::noSessionCookie);

            return;
        };
        $this->isCookieFraud();

        $this->doHeadlessBrowserDetection();
        $this->logger->doEventHop(2);
        $this->sendJS('' . $this->confURLWebsite . $_SESSION[ 'firewallURLReferrer' ]);
    }

    private function isSessionFraud(): bool {
        $fraud = !isset($_SESSION[ 'firewallActive' ]) || (((int)$_SESSION[ 'firewallActive' ]) == 0);

        if ($fraud)
            $_SESSION[ 'firewallRule' ][ self::noSessionCookie ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 5;

        return $fraud;
    }

    private function isCookieFraud(): bool {
        $fraud = !isset($_COOKIE[ $this->confCookieCheck ]);

        if ($fraud)
            $_SESSION[ 'firewallRule' ][ self::noCookie ] = TRUE;
        else
            $_SESSION[ 'firewallPoints' ] += 10;

        return $fraud;
    }

    private function doHeadlessBrowserDetection() {
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
        $iFrame = $_COOKIE[ 'wbi' ] ?? TRUE;

        $jsErrorCatch = $_COOKIE['wbq'] ?? TRUE;

        if ($hlPhantom || $hlEmit || $hlAuto || $hlSelenium || $stacktrace) {
            $_SESSION[ 'firewallRule' ][ self::headlessBrowser ] = TRUE;
        }
        if ($htmlAudio) {
            $_SESSION[ 'firewallRule' ][ self::noHTML5 ] = TRUE;
        }
        if ($htmlWorker || $htmlSockets) {
            $_SESSION[ 'firewallRule' ][ self::noSockets ] = TRUE;
        }
        if ($hlWindowOuter || empty($displayString)) {
            $_SESSION[ 'firewallRule' ][ self::noTopTier ] = TRUE;
        }
        if ($iFrame) {
            $_SESSION[ 'firewallRule' ][ self::noIFrame ] = TRUE;
        }
        if($jsErrorCatch) {
            $_SESSION[ 'firewallRule' ][ self::noJS ] = TRUE;
        }
        if (empty($_SESSION[ 'firewallRule' ])) {
            $_SESSION[ 'firewallRule' ][ self::harmless ] = TRUE;
        }

    }

    private function analyzeClientsInit() {
        if (!$this->isInit)
            return;

        $this->isBlacklist();
        $this->isProxy();

        $this->outInitialRequest();
    }

    private function outInitialRequest() {
        $this->jsHeader();
        $this->jsMetaRefresh();
        if (!$this->isJSCheckSeparate || (!isset($_SESSION[ 'firewallRule' ][ self::blacklist ]) && !isset($_SESSION[ 'firewallRule' ][ self::proxy ]))) {
            $this->logger->doEventHop(0);
            $this->jsTestFull();
            $this->jsBody();
            exit;
        } else {
            $this->isDone = TRUE;
            $this->lastHop = TRUE;
            $this->isJSHeadNeeded = FALSE;
            $this->logger->doEventFirewallFinal(self::blacklist);
        }
    }

    private function jsHeader() {
        echo '<!DOCTYPE html><html><head><title>Creezi</title><meta charset="utf-8" /><link rel="icon" href="data:,">';
    }

    private function jsMetaRefresh() {
        if ($this->useMetaRefresh) {
            if (!empty($this->confURLMetaRefresh))
                echo '<meta http-equiv="refresh" content="' . $this->confMetaRefreshInSeconds . '; URL=' . $this->confURLMetaRefresh . '">';
        }
    }

    private function jsTestFull() {
        $lambdaURL = $_SESSION[ 'firewallURLLambda' ];
        if (TRUE) {
            echo '<script>var tX="' . $lambdaURL . '";';
            /*echo '
            !function(){var t=function(){for(var t="",n=arguments,e=0;e<n.length;e++)t+=n[e];return t},n=function(){for(var t="",n=arguments,e=0;e<n.length;e++)t+=n[e]+"_";return t},e=t("sc","ree","n"),o=document,i=window,c=(o.location,function(t){i.location.replace(t)}),u="undefined"==typeof tX||null===tX?"/":tX,a="undefined"==typeof tC||null===tC||"boolean"!=typeof tc||tC,r=function(t){return!(void 0===t||null===t)},f=function(t,n,e){var i=new Date;i.setTime(i.getTime()+24*e*60*60*1e3);var c="expires="+i.toUTCString();o.cookie=t+"="+n+";"+c+";path=/"},d=function(){if(!0===a){f("jskum",t("xdf","tty","as0"),.1)}},w=function(){var t=i.innerWidth,o=i.innerHeight,c=i[e].availWidth,u=i[e].availHeight;f("wd",n(t,o,c,u),1),f("st",(new Date).getTimezoneOffset(),1)},h=function(t){return 0===t},v=function(){f("tvha",0,.1),f("tvhb",0,.1),f("tvhc",0,.1),f("tvhd",0,.1),f("wba",0,1),f("wbc",0,1),f("wbs",0,1),f("wbw",0,1);!function(){h(i.outerWidth)&&h(i.outerHeight)?f("wdx",1,.1):f("wdx",0,.1)}(),function(){(r(i._phantom)||r(i.__phantomas))&&f("tvha",1,.1),(r(i.Buffer)||r(i.emit))&&f("tvhb",1,.1),(r(i.spawn)||r(i.webdriver))&&f("tvhc",1,.1),r(i.domAutomation)&&f("tvhd",1,.1);try{i.AudioContext=i.AudioContext||i.webkitAudioContext,context=new AudioContext}catch(t){f("wba",1,1)}"WebSocket"in window||"MozWebSocket"in window||f("wbs",1,1),window.Worker||f("wbw",1,1);try{null[0]}catch(n){var t=n.stack;(t.indexOf("phantomjs")>-1||t.indexOf("couchjs")>-1)&&f("wbc",1,1)}}()},b=function(){c("'.$this->confURLWebsite.'"+u)};!function(){d(),w(),v(),b()}()}();
            ';*/
            readfile(dirname(__FILE__,3).'/ressources/dev/js/fwll-org.min.js');
            //echo '(function(){var stringGlue=function(){var s="",r=arguments;for(var i=0;i<r.length;i++)s+=r[i];return s};var stringGlueScore=function(){var s="",r=arguments;for(var i=0;i<r.length;i++)s+=r[i]+"_";return s};var screen=stringGlue("sc","ree","n");var fDocument=document;var fWindow=window;var loc=fDocument.location;var href=function(url){fWindow.location.replace(url)};var varToCheck=typeof tX==="undefined"||tX===null?"/":tX;var useTestCookies=typeof tC==="undefined"||tC===null||typeof tc!=="boolean"?true:tC;var isVariableSet=function(varX){return!(typeof varX==="undefined"||varX===null)};var setCookie=function(cname,cvalue,exsec){var d=new Date;d.setTime(d.getTime()+exsec*24*60*60*1e3);var expires="expires="+d.toUTCString();fDocument.cookie=cname+"="+cvalue+";"+expires+";path=/"};var testCookies=function(){if(useTestCookies===true){var cname="jskum",cv1="xdf",cv2="tty",cv3="as0",exdays=.1;setCookie(cname,stringGlue(cv1,cv2,cv3),exdays)}};var testBrowserInfo=function(){var dimX=fWindow.innerWidth;var dimY=fWindow.innerHeight;var availableX=fWindow[screen].availWidth;var availableY=fWindow[screen].availHeight;setCookie("wd",stringGlueScore(dimX,dimY,availableX,availableY),1);setCookie("st",(new Date).getTimezoneOffset(),1)};var equalZero=function(arg){return arg===0};var headlessBrowserDetection=function(){var testDisplay=function(){if(equalZero(fWindow.outerWidth)&&equalZero(fWindow.outerHeight)){setCookie("wdx",1,.1)}else{setCookie("wdx",0,.1)}};setCookie("tvha",0,.1);setCookie("tvhb",0,.1);setCookie("tvhc",0,.1);setCookie("tvhd",0,.1);setCookie("wba",0,1);setCookie("wbc",0,1);setCookie("wbs",0,1);setCookie("wbw",0,1);var testVars=function(){if(isVariableSet(fWindow._phantom)||isVariableSet(fWindow.__phantomas)){setCookie("tvha",1,.1)}if(isVariableSet(fWindow.Buffer)||isVariableSet(fWindow.emit)){setCookie("tvhb",1,.1)}if(isVariableSet(fWindow.spawn)||isVariableSet(fWindow.webdriver)){setCookie("tvhc",1,.1)}if(isVariableSet(fWindow.domAutomation)){setCookie("tvhd",1,.1)}try{fWindow.AudioContext=fWindow.AudioContext||fWindow.webkitAudioContext;context=new AudioContext}catch(e){setCookie("wba",1,1)}var isSocket="WebSocket"in window||"MozWebSocket"in window;if(!isSocket){setCookie("wbs",1,1)}if(!window.Worker){setCookie("wbw",1,1)}try{null[0]}catch(e){var err=e.stack;if(err.indexOf("phantomjs")>-1||err.indexOf("couchjs")>-1){setCookie("wbc",1,1)}}};testDisplay();testVars()};var random=function(min,max){return Math.floor(Math.random()*(max-min))+min};var sendToChecker=function(){href("http://tubesearch.co"+varToCheck)};var testIFrame=function(){var isIFrame=true;try{isIFrame=window.self!==window.top}catch(e){isIFrame=true}if(isIFrame){setCookie("wbi",1,1)}else{setCookie("wbi",0,1)}};var start=function(){testCookies();testBrowserInfo();headlessBrowserDetection();testIFrame();sendToChecker()};start()})();';
            echo '</script>';
            //echo "<script>window.location.href = 'http://tubesearch.co';</script>"; aaaaaaaaaaaaaaa
        }
    }

    private function jsBody() {
        echo '</head><body></body></html>';
    }
}