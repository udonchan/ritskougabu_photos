<?php
require_once 'rss_fetch.inc';

define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('DEFAULT_WIDTH', 200);
define('RSS_URL', 'http://ritskougabu.g.hatena.ne.jp/keyword/%e4%bd%9c%e5%93%81%e9%9b%86?mode=rss2');
define('IMAGE_URL_PATTERN', "/s?https?:\/\/[^\s]+(jpg|jpeg|gif|png|bmp)/");

$w = isset($_GET['w']) ? $_GET['w'] : DEFAULT_WIDTH;
$h = isset($_GET['h']) ? $_GET['h'] : $h = $w;

if(preg_match_all(IMAGE_URL_PATTERN, strip_tags(fetch_rss(RSS_URL)->items[0]['description']), $urls) == 0){
    echo 'no image';
    exit;
}
$im = new Imagick();
$im->readImageBlob(file_get_contents($urls[0][mt_rand(0, count($urls[0])-1)]));
if(isset($_GET['auto_rotate']) && $_GET['auto_rotate'] == 'true'){
    if( ($g = $im->getImageGeometry()) && ($g['width'] < $g['height']))
        list($w, $h) = array($h, $w);
}
$im->cropThumbnailImage($w, $h);

if(isset($_GET['mode']) && $_GET['mode'] == 'header')
    $im->compositeImage(a0nCRQ('ritskougabu', 20, 5, "#d0d0d045", "#00000080", 'white'), Imagick::COMPOSITE_OVER, 20, 60, Imagick::CHANNEL_ALL);
output($im, 'jpeg');

function a0nCRQ($msg, $padx, $pady, $bc, $fc, $tc){
    $im = new Imagick();
    $idraw = new ImagickDraw();
    $idraw->setFontSize(30);
    $idraw->setFont('MyriadPro-Regular.otf');
    $idraw->setGravity(Imagick::GRAVITY_CENTER);
    $metrics = $im->queryFontMetrics($idraw, $msg);
    $im->newPseudoImage($metrics["textWidth"]+$padx*2, $metrics["textHeight"]+$pady*2, "xc:none");
    $idraw->setFillColor($fc);
    $idraw->setStrokeColor($bc);
    $idraw->roundrectangle(0,0, $metrics["textWidth"]+$padx*2-1, $metrics["textHeight"]+$pady*2-1, 10, 10);
    $idraw->setFillColor($tc);
    $idraw->setStrokeColor($tc);
    $idraw->annotation(0, 0, $msg);
    $im->drawImage($idraw);
    return $im;
}

function output($im, $type){
    $im->setImageFormat($type);
    header("Content-Type: image/{$im->getImageFormat()}");
    echo $im->getImageBlob();
}
