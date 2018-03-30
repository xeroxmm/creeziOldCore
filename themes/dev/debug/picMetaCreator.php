<?php

require_once SERVER_ROOT.'/'.VH_ROOT.'/intern/'.CORE_VERSION.'/classes/class.process.picture.php';

$ID = (int)($_POST['id']);

$pic = dbQueries::get()->imageInformationByImageID($ID);
if(!isset($pic[0]->linkStored))
	exit("2");

$picDIR = '/usr/local/lsws/CREEZI/urkraft/public_html/userUploads/images/'.$pic[0]->linkStored.'.'.$pic[0]->mime;
if(!file_exists($picDIR))
	exit("3");

$pic = new picture($picDIR);

$pic->buildThumbnail(320,240);
$palette = $pic->getFingerPrintColours();
$pic->buildThumbnail(4,4);
$fPrint = $pic->getFingerPrintExactHEX();
$sum = $pic->getFingerPrintSum();

dbQueries::add()->imageMetaFingerPrint($ID, $sum, $fPrint,$palette);
dbQueries::add()->imageMetaStats($ID, $pic->getResX(), $pic->getResY(), $pic->getSize());
//echo '<img src="" />';

exit('d');
?>