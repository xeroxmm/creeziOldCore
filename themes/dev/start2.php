<?php
echo "INSERT INTO `sym_new`.content_index (`title`, `link`) VALUES ";
for($i = 11613; $i < 112417; $i++){
 echo "(NULL, NULL), ";
}
exit;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
snippet::getTopContent(1);

$startOffset = 5000;
$page = $_GET['page'] ?? 1;

$sql = "
SELECT c.title, c.contentID, c.thumbnailLink, c.is_private, c.userID, c.type, t.mime, i.colourDistributionHEX AS imageCol, unix_timestamp(c.dateCreated) AS timestamp FROM content_libraryList AS c 
LEFT JOIN content_srcThumbnails as t ON c.thumbnailLink = t.link
LEFT JOIN info_images_fingerprint as i ON t.imageID = i.imageID
ORDER BY c.ID ASC
LIMIT $startOffset
OFFSET " .(($page - 1) * $startOffset). ";";

$res = db::queryRaw( $sql );

/*
 * TAGS
 */
$sql = "
SELECT c.contentID, t.label FROM content_matchTags AS c
LEFT JOIN content_libraryTags as t ON c.tagLinkS = t.tagLinkS
ORDER BY c.ID ASC
LIMIT 9999999;
";

$tags = db::queryRaw( $sql );

/*
 * TEXTE
 */

$sql = "
SELECT c.contentID, t.text FROM content_matchElements as c
LEFT JOIN content_srcText as t ON c.textID = t.textID
WHERE c.textID IS NOT NULL
ORDER BY c.ID ASC
LIMIT 9999999;
";

$text = db::queryRaw( $sql );

// Erstellen des BasisArrays

$content = [];
foreach($res as $r){
    $r->tags = [];
    $r->descr = NULL;
    $r->type = ($r->type == 'i') ? 1 : (($r->type == 'v') ? 2 : 3);
    $r->link = 'https://creezi.com/ressources/images/full/' . $r->thumbnailLink . '.' . $r->mime;
    $content[ base_convert($r->contentID,36,10) ] = $r;
}

foreach($tags as $tag){
    $ID = base_convert($tag->contentID, 36, 10);
    if(isset($content[$ID])){
        $content[$ID]->tags[] = $tag->label;
    }
}
unset($tags);

foreach($text as $tag){
    $ID = base_convert($tag->contentID, 36, 10);
    if(isset($content[$ID])){
        $content[$ID]->descr = $tag->text;
    }
}
unset($text);

for($i = ($page - 1) * $startOffset + 1; $i <= $startOffset; $i++){
    if(!isset($content[$i]))
        $content[$i] = NULL;
}

echo json_encode($content);

exit;
