(function () {
    var stringGlue = function () {
        var s = "", r = arguments;
        for (var i = 0; i < r.length; i++)
            s += r[i];

        return s;
    }
    var stringGlueScore = function () {
        var s = "", r = arguments;
        for (var i = 0; i < r.length; i++)
            s += r[i] + "_";

        return s;
    }
    var screen = stringGlue('sc', 'ree', 'n');
    var fDocument = document;
    var fWindow = window;
    var loc = fDocument.location;
    var href = function (url) {
        loc.replace(url);
    };
    var varToCheck = (typeof tX === 'undefined' || tX === null) ? '/' : tX;
    // The CookieTest
    var useTestCookies = (typeof tC === 'undefined' || tC === null || typeof(tc) !== "boolean") ? true : tC;

    var isVariableSet = function (varX) {
        return !(typeof varX === 'undefined' || varX === null);
    }
    var setCookie = function (cname, cvalue, exsec) {
        var d = new Date();
        d.setTime(d.getTime() + (exsec * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        fDocument.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
    var testCookies = function () {
        if (useTestCookies === true) {
            var cname = "jskum", cv1 = "xdf", cv2 = "tty", cv3 = "as0", exdays = 0.1;
            setCookie(cname, stringGlue(cv1, cv2, cv3), exdays);
        }
    }
    var testBrowserInfo = function () {
        var dimX = fWindow.innerWidth;
        var dimY = fWindow.innerHeight;
        var availableX = fWindow[screen].availWidth;
        var availableY = fWindow[screen].availHeight;
        setCookie('wd', stringGlueScore(dimX, dimY, availableX, availableY), 1);
        setCookie('st', new Date().getTimezoneOffset(), 1);
    }
    var equalZero = function (arg) {
        return arg === 0;
    }
    var headlessBrowserDetection = function () {
        // Minimum Test with dimensions
        var testDisplay = function () {
            if (equalZero(fWindow.outerWidth) && equalZero(fWindow.outerHeight)) {
                setCookie('wdx', 1, .1);
            } else {
                setCookie('wdx', 0, .1);
            }
            var hubbaBubba = 1;
            //hubbaBubba.toUpperCase()[0];
        }
        // Minimum Test with phantom
        setCookie("tvha", 0, 0.1);
        setCookie("tvhb", 0, 0.1);
        setCookie("tvhc", 0, 0.1);
        setCookie("tvhd", 0, 0.1);
        setCookie('wba', 0, 1);
        setCookie('wbc', 0, 1);
        setCookie('wbs', 0, 1);
        setCookie('wbw', 0, 1);
        var testVars = function () {
            if (isVariableSet(fWindow._phantom) || isVariableSet(fWindow.__phantomas)) {
                setCookie("tvha", 1, 0.1);
            }
            if (isVariableSet(fWindow.Buffer) || isVariableSet(fWindow.emit)) {
                setCookie("tvhb", 1, 0.1);
            }
            if (isVariableSet(fWindow.spawn) || isVariableSet(fWindow.webdriver)) {
                setCookie("tvhc", 1, 0.1);
            }
            if (isVariableSet(fWindow.domAutomation)) {
                setCookie("tvhd", 1, 0.1);
            }
            try {
                // Fix up for Audio Context
                fWindow.AudioContext = fWindow.AudioContext || fWindow.webkitAudioContext;
                context = new AudioContext();

            } catch (e) {
                setCookie('wba', 1, 1);
            }

            // Fix up for Socket Context
            var isSocket = 'WebSocket' in window || 'MozWebSocket' in window;

            if (!isSocket) {
                setCookie('wbs', 1, 1);
            }

            // Fix up for Worker Context
            if (!window.Worker) {
                setCookie('wbw', 1, 1);
            }

            try {
                null[0]
            } catch (e) {
                var err = e.stack;
                if (
                    err.indexOf("phantomjs") > -1 ||
                    err.indexOf("couchjs") > -1
                ) {
                    setCookie('wbc', 1, 1);
                }
            }
        }
        testDisplay();
        testVars();
    }
    var random = function (min, max) {
        return Math.floor(Math.random() * (max - min)) + min;
    }
    var sendToChecker = function () {
        //href = '//tubesearch.co/';
        href('https://creezi.com' + varToCheck);
    }
    var testIFrame = function () {
        var isIFrame = true;
        try {
            isIFrame = window.self !== window.top
        } catch (e) {
            isIFrame = true
        }
        if (isIFrame) {
            setCookie("wbi", 1, 1)
        } else {
            setCookie("wbi", 0, 1)
        }
    };
    var start = function () {
        try {
            testCookies();
            testBrowserInfo();
            headlessBrowserDetection();
            testIFrame();
            setCookie('wbq',0,1);
            sendToChecker();
        } catch(k){
            // alert('TEST: ' + k.message);
            setCookie('wbq',1,1);
            sendToChecker();
        }
    }
    start();
})();