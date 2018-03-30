<?php

class apiListener {
    private $status     = FALSE;
    private $isUserAuth = FALSE;
    private $userAlias  = NULL;
    private $apiRequest = [];
    private $apiPath    = NULL;

    private $identForLogin             = FALSE;
    private $identForRestrictedActions = FALSE;

    private $credentialsLogin = [];

    private $isUserIdenticated       = FALSE;
    private $isUserAuthenticated     = FALSE;
    private $statusUserIdent         = FALSE;
    private $statusUserAuth          = FALSE;
    private $userHasWrongCredentials = FALSE;

    private $imagePublishCredObject = NULL;
    private $imagePublishArray      = [];

    function __construct() {
        // Load From URL OBject
        //error_reporting(0);
        $url = core::getURLObj()->getPathArray();

        // Check Dependencies
        if (!is_array($url) || $url[ 0 ] != LINK_API || count($url) > 20)
            return;

        // Check Session Parameter
        $this->loadPOSTVariablesLogin();
    }

    public function init() {
        // Check API Path
        $this->apiPath = new apiListenerPath(core::getURLObj()->getPathArray());

        if ($this->apiPath->isDone()) {
            api::send200();
            exit;
        }
    }

    private function loadPOSTVariablesLogin() {
        if (isset($_POST[ 'user' ]) && isset($_POST[ 'usersignature' ]) && !isset($_POST[ 'sessionsig' ])) {
            $this->credentialsLogin[ 'key' ] = preg_replace('/[^\da-z]+/i', '', $_POST[ 'user' ]);
            $this->credentialsLogin[ 'sig' ] = preg_replace('/[^\da-z]+/i', '', $_POST[ 'usersignature' ]);

            $this->identForLogin = TRUE;
        } else if (isset($_POST[ 'user' ]) && isset($_POST[ 'sessionsig' ]) && !isset($_POST[ 'usersignature' ])) {
            $this->credentialsLogin[ 'key' ] = preg_replace('/[^\da-z]+/i', '', $_POST[ 'user' ]);
            $this->credentialsLogin[ 'sig' ] = preg_replace('/[^\da-z]+/i', '', $_POST[ 'sessionsig' ]);

            $this->identForRestrictedActions = TRUE;
        }
    }

    private $lastDataFromJSON  = NULL;
    private $videoPublishArray = [];
    private $imgAltText        = NULL;

    public function isVideoPublishCredentials() {
        if (!isset($_POST[ 'amount' ]) || (int)$_POST[ 'amount' ] < 1 || !isset($_POST[ 'data' ]))
            return FALSE;

        $data = @json_decode($_POST[ 'data' ]);
        //print_r($data);
        if (!isset($data->files) || !is_array($data->files)) {
            api::addGlobalError('error reading file-array');

            return FALSE;
        }
        if (isset($data->userAlias) && !empty($data->userAlias) && is_string($data->userAlias))
            $this->userAlias = $data->userAlias;

        $i = 0;
        foreach ($data->files as $val) {
            $temp = new apiListenerFileVideo($val);
            if ($temp->status) {
                $this->imagePublishArray[] = $temp;
                $i++;
            }
        }

        if ($i < 1) {
            api::addGlobalError('amount doesnt match file-array-length (1)');

            return FALSE;
        }
        $this->lastDataFromJSON = $data;

        return TRUE;
    }

    public function isImagePublishCredentials() {
        if (!isset($_POST[ 'amount' ]) || (int)$_POST[ 'amount' ] < 1 || !isset($_POST[ 'data' ]))
            return FALSE;

        $data = @json_decode($_POST[ 'data' ]);
        //print_r($data);
        if (!isset($data->files) || !is_array($data->files)) {
            api::addGlobalError('error reading file-array');

            return FALSE;
        }
        if (isset($data->userAlias) && !empty($data->userAlias) && is_string($data->userAlias))
            $this->userAlias = $data->userAlias;

        $i = 0;
        foreach ($data->files as $val) {
            $temp = new apiListenerFileImage($val);
            if ($temp->status) {
                $this->imagePublishArray[] = $temp;
                $i++;
            }
        }

        if ($i < 1) {
            api::addGlobalError('amount doesnt match file-array-length');

            return FALSE;
        }

        $this->lastDataFromJSON = $data;

        return TRUE;
    }

    public function isUpdateMetaCredentials() {
        if (!isset($_POST[ 'data' ]))
            return FALSE;

        $data = @json_decode($_POST[ 'data' ]);

        if (
            !isset($data->id) ||
            (
                !isset($data->title) &&
                !isset($data->description) &&
                !isset($data->tags) &&
                !isset($data->imgAltText)
            )
        ) {
            api::addGlobalError('error reading data-array');

            return FALSE;
        }

        if (!isset($data->title))
            $data->title = NULL;
        if (!isset($data->description))
            $data->description = NULL;
        if (!isset($data->tags))
            $data->tags = '';
        if (!isset($data->imgAltText))
            $data->imgAltText = NULL;

        $data->tags = explode(',', $data->tags);

        foreach ($data->tags as $k => $val) {
            if (empty($val))
                unset($data->tags[ $k ]);
        }

        $this->lastDataFromJSON = $data;

        return TRUE;
    }

    public function isCollectionPublishCredentials() {
        if (!isset($_POST[ 'amount' ]) || (int)$_POST[ 'amount' ] < 1 || !isset($_POST[ 'data' ]))
            return FALSE;

        $data = @json_decode($_POST[ 'data' ]);

        if (!isset($data->files) || !is_array($data->files)) {
            api::addGlobalError('error reading file-array');

            return FALSE;
        }
        $i = 0;

        if (isset($data->userAlias) && !empty($data->userAlias) && is_string($data->userAlias))
            $this->userAlias = $data->userAlias;

        foreach ($data->files as $val) {
            $temp = new apiListenerFileImageCollections($val);
            if ($temp->status) {
                $this->imagePublishArray[] = $temp;
                $i++;
            }
        }

        if ($i < 1) {
            api::addGlobalError('amount doesnt match file-array-length (min 1)');

            return FALSE;
        }
        $this->lastDataFromJSON = $data;
        if (!isset($this->lastDataFromJSON->coldescription)) {
            $this->lastDataFromJSON->coldescription = '';
        }
        if (!isset($this->lastDataFromJSON->files))
            return FALSE;
        if (!isset($this->lastDataFromJSON->coltitle)) {
            api::addGlobalError('no collection title set');

            return FALSE;
        }

        return TRUE;
    }

    public function getLastDataJsonArray() {
        return $this->lastDataFromJSON;
    }

    public function getImageUploadArray() {
        return $this->imagePublishArray;
    }

    public function getVideoUploadArray() {
        return (is_array($this->imagePublishArray)) ? $this->imagePublishArray : [];
    }

    public function isLoginInformationProvided() {
        return $this->identForLogin;
    }

    public function isRestrictedInformationProvided() {
        return $this->identForRestrictedActions;
    }

    public function isUserIdenticated() {
        if ($this->isUserIdenticated)
            return $this->statusUserIdent;
        else if (!$this->isUserIdenticated && !$this->userHasWrongCredentials)
            return $this->doIdentProcess();
        else
            return FALSE;
    }

    public function isUserAuthenticated() {
        if ($this->isUserAuthenticated)
            return $this->statusUserAuth;
        else if (!$this->isUserAuthenticated && !$this->userHasWrongCredentials)
            return ($this->doAuthProcess());
        else
            return FALSE;
    }

    private function doIdentProcess() {
        $status = FALSE;
        if (!$this->identForLogin)
            return FALSE;

        $status = security::loginUserAPI($this->credentialsLogin[ 'key' ], $this->credentialsLogin[ 'sig' ], $this->userAlias);

        if ($status) {
            $this->isUserIdenticated = TRUE;
            $this->statusUserIdent = TRUE;

            $this->isUserAuthenticated = TRUE;
            $this->statusUserAuth = TRUE;
        } else {
            $this->userHasWrongCredentials = TRUE;
        }

        return $status;
    }

    private function doAuthProcess() {
        return $this->doIdentProcess();
        //
        $status = FALSE;
        if (!$this->identForRestrictedActions)
            return FALSE;

        $status = security::loginUserAPIAuth($this->credentialsLogin[ 'key' ], $this->credentialsLogin[ 'sig' ]);
        if ($status) {
            $this->isUserAuthenticated = TRUE;
            $this->statusUserAuth = TRUE;
        } else {
            $this->userHasWrongCredentials = TRUE;
        }

        return $status;
    }
}

class apiListenerFileImageCollections {
    public $status      = FALSE;
    public $title       = NULL;
    public $tags        = NULL;
    public $url         = NULL;
    public $description = NULL;
    public $imgurl      = "";
    public $imgAltText  = NULL;

    function __construct( $data ) {
        if (!isset($data->title))
            $data->title = '';
        if (isset($data->title) && isset($data->url)) {
            $this->title = $data->title;
            $this->url = $data->url;

            if (isset($data->tags)) {
                $tags = explode(',', $data->tags);
                //if(is_array($tags) || strlen($tags) > 2)
                $this->tags = $tags;
            } else {
                $this->tags = [];
            }
            if (isset($data->description)) {
                $this->description = $data->description;
            } else
                $this->description = '';
            if (isset($data->imgAltText)) {
                $this->imgAltText = $data->imgAltText;
            }
            $this->status = TRUE;
        }
        if (isset($data->imgurl))
            $this->imgurl = $data->imgurl;
    }
}

class apiListenerFileVideo {
    public $status      = FALSE;
    public $title       = NULL;
    public $tags        = NULL;
    public $url         = NULL;
    public $description = NULL;
    public $hoster      = '';
    public $hosterID    = '';

    public $youtubeID = FALSE;

    private $supportedURLs = [];

    function __construct( $data ) {
        if (isset($data->url) && $this->isValidURL($data->url)) {
            $this->url = $data->url;

            if (isset($data->tags)) {
                $tags = explode(',', $data->tags);
                //if(is_array($tags) || strlen($tags) > 2)
                $this->tags = $tags;
            } else {
                $this->tags = [];
            }
            if (isset($data->description)) {
                $this->description = $data->description;
            } else
                $this->description = '';

            if (isset($data->title)) {
                $this->title = $data->title;
            } else {
                $this->title = '';
            }

            if (($tID = $this->isYoutubeURL()) !== FALSE) {
                $this->hoster = 'youtube';
                $this->hosterID = $tID;
                $this->youtubeID = $tID;
            } else {
                return FALSE;
            }

            $this->status = TRUE;
        }
    }

    private function isValidURL( $url ) {
        return is_string($url);
    }

    private function isYoutubeURL() {
        $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?';         #  Optional www subdomain.
        $pattern .= '(?:';                #  Group host alternatives:
        $pattern .= 'youtu\.be/';       #    Either youtu.be,
        $pattern .= '|youtube\.com';    #    or youtube.com
        $pattern .= '(?:';              #    Group path alternatives:
        $pattern .= '/embed/';        #      Either /embed/,
        $pattern .= '|/v/';           #      or /v/,
        $pattern .= '|/watch\?v=';    #      or /watch?v=,
        $pattern .= '|/watch\?.+&v='; #      or /watch?other_param&v=
        $pattern .= ')';                #    End path alternatives.
        $pattern .= ')';                  #  End host alternatives.
        $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.

        preg_match($pattern, $this->url, $matches);

        return (isset($matches[ 1 ])) ? $matches[ 1 ] : FALSE;
    }
}

class apiListenerFileImage {
    public $status      = FALSE;
    public $title       = NULL;
    public $tags        = NULL;
    public $url         = NULL;
    public $description = NULL;
    public $imgAltText  = NULL;

    function __construct( $data ) {
        if (isset($data->title) && isset($data->url)) {
            $this->title = $data->title;
            $this->url = $data->url;

            if (isset($data->tags)) {
                $tags = explode(',', $data->tags);
                //if(is_array($tags) || strlen($tags) > 2)
                $this->tags = $tags;
            } else {
                $this->tags = [];
            }
            if (isset($data->description)) {
                $this->description = $data->description;
            } else
                $this->description = '';
            if (isset($data->imgAltText)) {
                $this->imgAltText = $data->imgAltText;
            }
            $this->status = TRUE;
        }
    }
}

class apiListenerPath {
    private $status       = FALSE;
    private $statusClosed = FALSE;

    private $path = [
        //''	 => '00',
        'status'  => [
            '00'      => '00',
            'user'    => '00',
            'content' => [
                'untagged' => '00',
                'untitled' => '00',
                'unparsed' => '00'
            ]
        ],
        'publish' =>
            [
                'images'     => '00',
                'collection' => '00',
                'videos'     => '00'
            ],
        'edit'    =>
            [
                'content' => [
                    'meta' => '00'
                ]
            ]
    ];

    function __construct( $urlObject ) {
        if (!is_array($urlObject))
            return;

        $j = 0;
        $z = count($urlObject);

        if ($urlObject[ 0 ] == LINK_API)
            $j++;

        if ($j == $z) {
            api::loadInfoTemplate();
            api::getTemplateObject()->setTime(time());
            api::getTemplateObject()->setType('json');
            api::getTemplateObject()->setKey((useAPI) ? 'OK' : 'api offline');

            $this->buildingDone();

            return;
        }

        $ob = $this->path;
        $funcName = '_';
        for ($i = $j; $i < $z; $i++) {
            if (isset($ob[ $urlObject[ $i ] ]) && $urlObject[ $i ] != '00') {
                $ob = $ob[ $urlObject[ $i ] ];
                $funcName .= ucfirst($urlObject[ $i ]);
            } else {
                //echo print_r($ob,true) . ' .> ';
                //echo $funcName . ' -> ';
                return;
            }
        }

        if (!method_exists($this, $funcName) || (is_array($ob) && !isset($ob[ '00' ])))
            return;
        else
            $this->$funcName();

        return;
    }

    private function buildingDone() {
        $this->status = TRUE;
        $this->statusClosed = TRUE;
    }

    private function sendInfo() {
        api::loadInfoTemplate();
        api::getTemplateObject()->setTime(time());
        api::getTemplateObject()->setType('json');
        api::getTemplateObject()->setKey((useAPI) ? 'OK' : 'api offline');
    }

    public function isDone() {
        return ($this->status && $this->statusClosed);
    }

    private function _Status() {
        $this->sendInfo();
        $this->buildingDone();
    }

    private function _Login() {
        if (!api::getAPIListener()->isUserIdenticated())
            return;

        api::loadStatusUserTemplate();
        api::getTemplateObject()->setUserName(user::getNick());
        api::getTemplateObject()->setStatus('logged in');
        api::getTemplateObject()->setSignature(user::getAPISignature());
        api::getTemplateObject()->setTime(time());

        $this->buildingDone();
    }

    private function _StatusUser() {
        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadStatusUserTemplate();
        api::getTemplateObject()->setUserName(user::getNick());
        api::getTemplateObject()->setUserEmail(user::getEmail());
        api::getTemplateObject()->setUserURLProfile(user::getURL());
        api::getTemplateObject()->setStatus('logged in');
        api::getTemplateObject()->setTime(time());

        $this->buildingDone();
    }

    private $videoID = 0;

    private function isYoutubeURL( $url ) {
        $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?';         #  Optional www subdomain.
        $pattern .= '(?:';                #  Group host alternatives:
        $pattern .= 'youtu\.be/';       #    Either youtu.be,
        $pattern .= '|youtube\.com';    #    or youtube.com
        $pattern .= '(?:';              #    Group path alternatives:
        $pattern .= '/embed/';        #      Either /embed/,
        $pattern .= '|/v/';           #      or /v/,
        $pattern .= '|/watch\?v=';    #      or /watch?v=,
        $pattern .= '|/watch\?.+&v='; #      or /watch?other_param&v=
        $pattern .= ')';                #    End path alternatives.
        $pattern .= ')';                  #  End host alternatives.
        $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.

        preg_match($pattern, $url, $matches);

        return (isset($matches[ 1 ])) ? $matches[ 1 ] : FALSE;
    }

    private function _PublishVideos( $hidden = FALSE ) {
        if (!$hidden && !api::getAPIListener()->isVideoPublishCredentials())
            return;
        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadPublishPostsTemplate();

        foreach (api::getAPIListener()->getVideoUploadArray() as $data) {
            if (($tID = $this->isYoutubeURL($data->url)) !== FALSE) {
                $data->hoster = 'youtube';
                $data->hosterID = $tID;
                $data->youtubeID = $tID;
            } else
                continue;

            $temp = trim($data->title, ' .;-');
            if (is_numeric($temp))
                $data->title = '';

            if (is_array($data->tags))
                foreach ($data->tags as $key => $val) {
                    $data->tags[ $key ] = security::getTagNameHarmonized($val);
                }
            // Create new SrcListID
            $this->videoID = contentCreation::getItemToSrcListID();

            if ((int)$this->videoID < 1) {
                api::getTemplateObject()->addError('cant create contentSrcID');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    100,
                    'contentID Src Creation -> ',
                    $data->url
                );
                continue;
            }
            // Create Dummy Post in SrcListID
            $hoster = '';
            $hosterID = '';
            if (!($temp = contentCreation::createVideoRawEntryForLibraryPost($this->videoID, $data->url, security::getUserObject()->getDatabaseIDCloaked(), $data->hoster, $data->hosterID))) {
                api::getTemplateObject()->addError('cant create video Entry');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    116,
                    'cant create content video entry in DB',
                    $data->url
                );
                dbQueries::delete()->srcListEntrieByID($this->videoID);
                continue;
            }

            // Create Library List Entry ID
            if (($newContentID = contentCreation::getItemToLibraryListID()) === FALSE) {
                api::getTemplateObject()->addError('cant create contentID');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    105,
                    'contentID Creation -> ',
                    $data->url
                );
                continue;
            }

            // Create dummy entry of video post (content)
            $cItem = new cCreation();
            $cItem->setUserID(user::getDBIDCloaked());
            $cItem->setType('v');
            $cItem->setContentID($newContentID);
            //$cItem->setSrcID( $picUp->getImageID() );
            $cItem->setTitle($data->title);
            $cItem->setLink(security::getNormalizedLinkURL($data->title));
            $cItem->setShortTitle(uploadSanitizer::getStringShortend($data->title));
            $cItem->setIsPrivate(0);
            $cItem->setIsAdult(0);
            //$cItem->setThumbLink( $picUp->getThumbStoreURL() );
            //$cItem->setCountElements();

            if (!$lastLibraryID = contentCreation::createLibraryContentPost($cItem)) {
                // set newly created contentID to is_deleted == 1
                dbQueries::delete()->libraryListIDEntrie($cItem->getContentID());
                api::getTemplateObject()->addError('cant create libraryID');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    105,
                    'cant create libraryID',
                    $data->url
                );
                continue;
            }

            $async = new asynchronCall();
            $async->scrapeVideoInformation($this->videoID, $lastLibraryID, security::getUserObject()->getDatabaseIDCloaked());

            api::getTemplateObject()->addAmountPlusOne();
            api::getTemplateObject()->addFiles([
                'title' => $data->title,
                'url'   => $data->url
            ]);

            if ($hidden) {
                $this->collectionItemsAdded++;
                $this->imageList[] = (object)[ 'type' => 'v', 'contentID' => $newContentID, 'videoID' => $this->videoID, 'ID' => $lastLibraryID, 'link' => '', 'thumb' => '' ];
            }
        }
        if (!$hidden)
            $this->buildingDone();
        else
            return count($this->imageList);
    }

    private function _PublishImages( $hidden = FALSE ) {
        if (!$hidden && !api::getAPIListener()->isImagePublishCredentials())
            return;

        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadPublishPostsTemplate();

        // Create new Database Entries

        foreach (api::getAPIListener()->getImageUploadArray() as $data) {
            // data title normalising
            $temp = trim($data->title, ' .;-');
            if (is_numeric($temp))
                $data->title = '';
            set_time_limit(0);
            ini_set('max_execution_time', '0');
            $u = urldecode(urldecode($data->url));
            if ($data->imgurl != "")
                $u = urldecode(urldecode($data->imgurl));

            $picS = new crawlImage($u);
            $_FILES[ 'file' ] = $picS->getLastImageAsTempFile();
            $picS->deleteLastImage();

            $meta = @getimagesize($_FILES[ 'file' ][ 'tmp_name' ]);

            if (!isset($meta[ 0 ])) {
                api::getTemplateObject()->addError('no meta available -> url: ' . $u);
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    101,
                    'no image object -> ' . $u,
                    $u
                );
                continue;
            }
            $picUp = new uploadImage(TRUE);

            $yt = FALSE;
            if (isset($picS))
                $yt = $picS->getYoutubeObj();

            if (!$picUp->process($yt)) {
                api::getTemplateObject()->addError('pic process went wrong: ' . implode($picUp->getErrors()));
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    102,
                    '' . implode($picUp->getErrors()),
                    $data->url
                );
                continue;
            }

            if (is_array($data->tags))
                foreach ($data->tags as $key => $val) {
                    $data->tags[ $key ] = security::getTagNameHarmonized($val);
                }

            if (($newContentID = contentCreation::getItemToLibraryListID()) === FALSE) {
                api::getTemplateObject()->addError('cant create contentID');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    105,
                    'contentID Creation -> ',
                    $data->url
                );
                continue;
            }
            $cItem = new cCreation();
            $cItem->setUserID(user::getDBIDCloaked());
            $cItem->setType('i');
            $cItem->setContentID($newContentID);
            $cItem->setSrcID($picUp->getImageID());
            $cItem->setTitle($data->title);
            $cItem->setLink(security::getNormalizedLinkURL($data->title));
            $cItem->setShortTitle(uploadSanitizer::getStringShortend($data->title));
            $cItem->setIsPrivate(0);
            $cItem->setIsAdult(0);
            $cItem->setThumbLink($picUp->getThumbStoreURL());
            //$cItem->setCountElements();

            if (!$lastLibraryID = contentCreation::createLibraryContentPost($cItem)) {
                // set newly created contentID to is_deleted == 1
                dbQueries::delete()->libraryListIDEntrie($cItem->getContentID());
                api::getTemplateObject()->addError('cant create libraryID');
                dbQueries::add()->errorAPIImageCrawl(
                    security::getUserObject()->getDatabaseIDCloaked(),
                    105,
                    'cant create libraryID',
                    $data->url
                );
                continue;
            }

            // Add item to searcable Content Images
            if (!$hidden && !dbQueries::add()->libraryElementToSearch_contentImages($lastLibraryID))
                api::getTemplateObject()->addError('no add to searchable content');


            // Add item to searchable content All
            if (!$hidden)
                dbQueries::add()->libraryElementToSearch_contentAll($lastLibraryID);

            // Add item to matchElementsTable
            if (!dbQueries::add()->elementToMatchItems($newContentID, [ (object)[ 'type' => 'si', 'contentID' => $newContentID, 'imageID' => $picUp->getImageID(), 'ID' => $picUp->getLastIDContentLibrary() ] ]))
                api::getTemplateObject()->addError('no add to matchElementsTable');

            // Add item to sourceTable
            if (!dbQueries::add()->elementToSrcTable((object)[ 'type' => 'api', 'userID' => user::getDBIDCloaked(), 'srcID' => $picUp->getImageID(), 'src' => $data->url ]))
                api::getTemplateObject()->addError('no add to sourceTable');

            // Add description text if set to content_srcText
            if (!contentCreation::addTextToContentItem($cItem->getContentID(), $data->description))
                api::getTemplateObject()->addError('no description saved (ID)');

            // Add tags to image if tags are set in API-Call
            if (is_array($data->tags) && count($data->tags) > 0) {
                contentCreation::addTagsToImage($cItem->getContentID(), $data->tags);
            }

            // add alt tag to image
            if (!empty($data->imgAltText)) {
                dbQueries::change()->contentInformationAltTag($newContentID, $data->imgAltText);
            }

            api::getTemplateObject()->addAmountPlusOne();
            api::getTemplateObject()->addFiles([
                'title' => $data->title,
                'url'   => $data->url
            ]);

            if ($hidden) {
                $this->collectionItemsAdded++;
                $this->imageList[] = (object)[ 'type' => 'i', 'contentID' => $newContentID, 'imageID' => $picUp->getImageID(), 'ID' => $picUp->getLastIDContentLibrary(), 'link' => $picUp->getLinkStored(), 'thumb' => $picUp->getThumbStoreURL() ];
            }

            // dbQueries::add()->libraryElementToSearch_contentImagesFrontpage( $lastLibraryID );
        }
        if (!$hidden)
            $this->buildingDone();
        else
            return count($this->imageList);
    }

    private $imageList            = [];
    private $collectionItemsAdded = 0;

    private function _PublishCollection() {
        if (!api::getAPIListener()->isCollectionPublishCredentials())
            return;
        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        $this->collectionItemsAdded = 0;

        $tagsCollection = [];
        $isTrue = FALSE;

        if ($this->_PublishVideos(TRUE))
            $isTrue = TRUE;

        if ($this->_PublishImages(TRUE))
            $isTrue = TRUE;

        if ($isTrue) {
            // Get CollectionTags
            if (isset(api::getAPIListener()->getLastDataJsonArray()->coltags)) {
                $temp = explode(',', api::getAPIListener()->getLastDataJsonArray()->coltags);
                foreach ($temp as $val) {
                    $tagsCollection[ security::getTagNameHarmonized($val) ] = $val;
                }
            }

            // Create Collection ID
            $libraryListID = contentCreation::getItemToLibraryListID();
            //$collID = contentCreation::addCollectionDBAndReturnColID(api::getAPIListener()->getLastDataJsonArray()->coltitle, $colDescription, $imageList);

            $cItem = new cCreation();
            $cItem->setUserID(user::getDBIDCloaked());
            $cItem->setType('c');
            $cItem->setContentID($libraryListID);
            $cItem->setSrcID(0);
            $cItem->setTitle(api::getAPIListener()->getLastDataJsonArray()->coltitle);
            $cItem->setLink(security::getNormalizedLinkURL(api::getAPIListener()->getLastDataJsonArray()->coltitle));
            $cItem->setShortTitle(uploadSanitizer::getStringShortend(api::getAPIListener()->getLastDataJsonArray()->coltitle));
            $cItem->setIsPrivate(0);
            $cItem->setIsAdult(0);
            $cItem->setThumbLink($this->imageList[ 0 ]->thumb);

            $lastLibraryID = contentCreation::createLibraryContentPost($cItem);

            // Add elements to Collection
            dbQueries::add()->elementToMatchItems($libraryListID, $this->imageList);

            // Add Description to Collection
            // Add description text if set to content_srcText
            if (!contentCreation::addTextToContentItem($cItem->getContentID(), api::getAPIListener()->getLastDataJsonArray()->coldescription))
                api::getTemplateObject()->addError('no col-description saved (ID)');

            // Add Tags to match with the collection
            $tagstring = ''; // print_r($tagsCollection);
            if (!empty($tagsCollection)) {
                dbQueries::add()->tagsToAllDatabases($cItem, $tagsCollection);
            }
            /*foreach($tagsCollection as $key => $val)
                $tagstring .= $key.',';

            if(strlen($tagstring) > 1)
                $tagstring = substr($tagstring, 0, -1);*/

            // Add collection to searchable DataTables
            dbQueries::add()->libraryElementToSearch_contentAll($lastLibraryID);
            dbQueries::add()->libraryElementToSearch_contentAllFrontpage($lastLibraryID);

            // Add collection to searchable ColDatatable
            dbQueries::add()->libraryElementToSearch_contentCollections($lastLibraryID);
            dbQueries::add()->libraryElementToSearch_contentCollectionsFrontpage($lastLibraryID);

        }
        $this->buildingDone();
    }

    private function _EditContentMeta() {
        if (!api::getAPIListener()->isUpdateMetaCredentials())
            return;

        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadInfoTemplate();
        $data = api::getAPIListener()->getLastDataJsonArray();

        $cM = new contentManager($data->id);
        if (!$cM->isUserPermission()) {
            api::getTemplateObject()->addError(apiErrors::notPermissionUser());

            return;
        }

        if (!empty($data->title))
            $cM->setTitle($data->title);

        if (!empty($data->description))
            $cM->setDescription($data->description);

        if (!empty($data->tags)) {
            $cM->setTags($data->tags);
        }

        if (!empty($data->imgAltText)) {
            $cM->setImageAltText($data->imgAltText);
        }

        if (!$cM->doDBDump()) {
            api::getTemplateObject()->addError(apiErrors::notStorage());

            return;
        }

        if (isset($data->rID) && isset($data->robot) && $data->robot == '86') {
            $rTags = implode(',', $data->tags);
            dbQueries::change()->aiTagInfoText86($data->rID, $rTags);
        }

        $this->buildingDone();
    }

    private function _StatusContentUnparsed() {
        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadPublishPostsTemplate();

        $data = @json_decode($_POST[ 'data' ] ?? '{}');
        $min = $data->min ?? 0;
        $max = $data->max ?? 10;

        $res = dbQueries::get()->imageInformationByImageIDRange($min, $max);

        if (count($res) >= 1) {
            $files = [];
            foreach ($res as $img) {
                $files[ $img->srcID ] = $img;
            }
            foreach ($files as $val) {
                $file = (object)[
                    'id'            => $val->srcID,
                    'srcLink'       => $val->linkStored,
                    'srcOriginLink' => $val->src
                ];

                api::getTemplateObject()->addAmountPlusOne();
                api::getTemplateObject()->addFiles($file);
            }
        }

        $this->buildingDone();
    }

    private function _StatusContentUntagged() {
        if (!api::getAPIListener()->isUserAuthenticated())
            return;

        api::loadPublishPostsTemplate();

        $data = @json_decode($_POST[ 'data' ]);

        $type = (isset($data->type) && in_array($data->type, [ 'i' ])) ? $data->type : 'i';
        $limit = (isset($data->limit) && is_numeric($data->limit) && (int)$data->limit > 0 && (int)$data->limit < 26) ? (int)$data->limit : 1;

        // select user id(s) by request
        $userID = (isset($data->filter) && is_numeric($data->filter) && user::getUserObject()->getUserLevel() >= userLevelEnum::ADMIN) ? (int)$data->filter : user::getDBID();
        if ($userID < 0)
            return;

        // get untagged Media by User
        $res = dbQueries::get()->untaggedElements($userID, $type, $limit);

        $files = [];

        if (count($res) >= 1) {
            foreach ($res as $val) {
                $t = dbQueries::add()->aiTagCrawlDate($val->contentID);
                $file = (object)[
                    'id'  => $val->contentID,
                    'url' => THUMB_HOST . '/' . $val->thumbnailLink . '-med.jpg',
                    'uri' => HTTP_HOST . '/' . $type . '/' . $val->contentID,
                    'rID' => $t
                ];

                api::getTemplateObject()->addAmountPlusOne();
                api::getTemplateObject()->addFiles($file);
            }
        }

        $this->buildingDone();
    }
}

?>
