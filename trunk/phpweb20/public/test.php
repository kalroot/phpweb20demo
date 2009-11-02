<?php

/*
set_include_path(implode(PATH_SEPARATOR, array(
    realpath('D:/www/phpweb20/library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);*/


function watermask($targetfile, $logofile)
{
	$imagetype = array("1" => "gif", "2" => "jpeg", "3" => "png", "4" => "wbmp");

	$targetinfo = getimagesize($targetfile);
	$imagecreatefromfunc = "imagecreatefrom" . $imagetype[$targetinfo[2]];
	$imagefunc = "image" . $imagetype[$targetinfo[2]];
	list($img_w, $img_h) = $targetinfo;

	$watermarkinfo = getimagesize($logofile);
	$watermark = imageCreateFromPNG($logofile);
	list($logo_w, $logo_h) = $watermarkinfo;

	$x = $img_w - $logo_w - 5;
	$y = $img_h - $logo_h - 5;

	$dst_photo = imagecreatetruecolor($img_w, $img_h);
	$target_photo = @$imagecreatefromfunc($targetfile);
	imageCopy($dst_photo, $target_photo, 0, 0, 0, 0, $img_w, $img_h);

	imageCopy($dst_photo, $watermark, $x, $y, 0, 0, $logo_w, $logo_h);
	clearstatcache();
	$imagefunc($dst_photo, $targetfile, 80);
}

watermask("image.jpg", "watermark1.png");


/*
header ("Content-type: image/png");
$logoImage = ImageCreateFromPNG('watermark1.png');
$photoImage = ImageCreateFromJpeg('image.jpg');
ImageAlphaBlending($photoImage, true);
$logoW = ImageSX($logoImage);
$logoH = ImageSY($logoImage);
ImageCopy($photoImage, $logoImage, 0, 0, 0, 0, $logoW, $logoH);
ImageJPEG($photoImage); // output to browser
ImageDestroy($photoImage);
ImageDestroy($logoImage);*/


?>