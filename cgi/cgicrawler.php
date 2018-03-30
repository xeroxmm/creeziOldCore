<?php
require_once 'database/conf.mysql.php';
require_once 'database/__loader.php';

$ID = 0;
$startID = 120000;

$removed = 0;
$found = 0;

// THE RANDOMIZER
if( TRUE ){
    $sql = "SELECT ID FROM content_libraryList WHERE is_private = 0 LIMIT 120000;";

    $r = db::queryRaw($sql);
    shuffle($r->results);

    $i = 0;
    foreach($r->results as $res){
        if($i > 50)
            break;

        $i++;
        echo $i."\n";
        $sql = "UPDATE content_libraryList SET dateCreated = NOW() WHERE ID = " . $res->ID;
        db::queryRaw($sql);
    }

    exit;
}

// do the image stuff
$ID = $startID;
while(FALSE && $ID > 0){
    $sql = "SELECT * FROM `content_libraryList` WHERE ID <= $ID AND type = 'i' ORDER BY ID DESC LIMIT 1000;";
    $r = db::queryRaw( $sql );

    echo "removed: ".$removed." - found: ".$found." \n";

    if($r->hasResults()){
        foreach($r->results as $value){
            // check if file exists
            $fileExT = file_exists('/usr/local/lsws/SITES/creezi.com/ressources/images/thumbs/'.$value->thumbnailLink.'.jpg');
            $fileExR = file_exists('/usr/local/lsws/SITES/creezi.com/ressources/images/full/'.$value->thumbnailLink.'.jpg');

            // var_dump(file_exists('/usr/local/lsws/SITES/creezi.com/ressources/images/full/8/3270.jpg'));

            if(!$fileExR || !$fileExT){
                // set file private

                $sql = "UPDATE content_libraryList SET is_private = 1 WHERE ID = ".$value->ID.';';
                db::queryRaw($sql);
                $removed++;
            } else {
                $sql = "UPDATE content_libraryList SET userID = ".mt_rand(1,10)." WHERE ID = ".$value->ID.';';
                db::queryRaw($sql);
                $found++;
            }
        }
    }
    $ID -= 1000;
}

// REMOVE SPECIFIC HOSTER (SOFT)
if(FALSE) {
    $sql = "SELECT t2.contentID
        FROM content_srcOrigin AS t1 
        LEFT JOIN content_matchElements AS t2 ON t1.srcID = t2.imageID
        WHERE t1.hoster LIKE '%hdwidewallpaper.com%'";

    $resX = db::queryRaw($sql);

    if ($resX->hasResults()) {
        echo "has Results: " . $resX->amount . "\n";
        foreach ($resX->results as $r) {
            $sql = "UPDATE content_libraryList SET is_private = 1 WHERE contentID ='" . $r->contentID . "';";
            echo "" . ((int)db::queryRaw($sql)) . "\n";
        }
    }
    echo "DONE\n";
    exit;
}
// do the collection stuff
$ID = $startID;
while($ID > 0) {
    $usedID = $ID;
    $sql = "SELECT * FROM `content_libraryList` WHERE ID < $ID AND type = 'c' ORDER BY ID DESC LIMIT 1000;";
    $r = db::queryRaw($sql);
    echo "doing collection stuff... -> {$ID} -> $sql \n";
    if ($r->hasResults()) {
        foreach ($r->results as $value) {
            // load collection images
            $sql = "SELECT t2.thumbnailLink, t2.ID AS t2ID, t2.is_private, t1.* 
                    FROM content_matchElements AS t1
                    LEFT JOIN content_libraryList AS t2 ON t1.contentIDSub = t2.contentID
                    WHERE t1.contentID = '" . $value->contentID . "' AND t1.type = 'i' AND t2.is_private = 0;";

            $subs = db::queryRaw($sql);

            //echo "\t Loading imageset for ".$value->ID." with ".$subs->amount." results... \n $sql \n\n";

            $results = $subs->amount;
            $deleted = 0;
            $found = [];
            if ($subs->hasResults()) {
                foreach ($subs->results as $sub) {
                    if ($sub->is_private == 0) {
                        $found[] = $sub->thumbnailLink;
                    }
                }
                //echo "found good objects: ".count($found)."\n\n";
            }
            if (!empty($found)) {
                // check if file exists
                $fileExT = file_exists('/usr/local/lsws/SITES/creezi.com/ressources/images/thumbs/' . $value->thumbnailLink . '.jpg');
                $thumbFile = $value->thumbnailLink;

                // GENERATE NEW THUMB 4 COLLECTION AND SET COLLECTION VISIBLE (SOFT)
                shuffle($found);
                $thumbFile = $found[ 0 ];
                $sql = "UPDATE content_libraryList SET is_private = 0, userID = " . mt_rand(1, 10) . ", thumbnailLink = '" . $thumbFile . "' WHERE ID = {$value->ID};";

            } else {
                // REMOVE COLLECTION FROM LIBRARY LIST (SOFT)
                $sql = "UPDATE content_libraryList SET is_private = 1 WHERE ID = " . $value->ID;
            }
            db::queryRaw($sql);
            $ID = $value->ID;
        }
    } else {
        echo "OUT OF RESULTS...\n";
        exit;
    }
    if ($usedID == $ID)
        $ID -= 1000;
}