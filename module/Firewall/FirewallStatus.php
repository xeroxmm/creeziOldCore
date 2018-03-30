<?php

namespace Firewall;

class FirewallStatus {
    /** @var null|bool  */
    public $isBlacklist       = NULL;
    /** @var null|bool  */
    public $isProxy           = NULL;
    /** @var null|bool  */
    public $isCurl            = NULL;
    /** @var null|bool  */
    public $isSession         = NULL;
    /** @var null|bool  */
    public $isJS              = NULL;
    /** @var null|bool  */
    public $isHeadlessBrowser = NULL;
    /** @var null|bool  */
    public $isCookie          = NULL;
    /** @var null|bool  */
    public $isNoHTML5         = NULL;
    /** @var null|bool  */
    public $isNoTopTier       = NULL;
    /** @var bool */
    public $isHarmless        = FALSE;
    /** @var null|bool  */
    public $isWebsocket       = NULL;

    public $isReferrerMissing = NULL;
}