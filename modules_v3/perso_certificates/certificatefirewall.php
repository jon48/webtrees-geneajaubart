<?php
/**
 * Certificates firewall
 * Is called when user tries to access a certificate through its public URL
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

use WT\Log;

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $controller, $useTTF;

Zend_Session::writeClose();

$cid   = WT_Filter::get('cid');
$certificate = null;
if($cid) $certificate = WT_Perso_Certificate::getInstance($cid);

/**
 * Displays a 404 error message
 * Similar to the the media firewall function.
 * 
 */
function send404AndExit() {
	$error = WT_I18N::translate('The certificate file was not found in this family tree');

	$width = (mb_strlen($error)) * 6.5 + 50;
	$height = 60;
	$im  = imagecreatetruecolor($width, $height);  /* Create a black image */
	$bgc = imagecolorallocate($im, 255, 255, 255); /* set background color */
	imagefilledrectangle($im, 2, 2, $width-4, $height-4, $bgc); /* create a rectangle, leaving 2 px border */

	embedText($im, $error, 100, 8, "255, 0, 0", "", "top", "left");

	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
	header('Content-Type: image/png');
	imagepng($im);
	imagedestroy($im);
	exit;
}

/**
 * Returns the entered image with a watermark printed.
 * Similar to the the media firewall function.
 * 
 * @param Image $im Certificate image to watermark
 * @param WT_Perso_Certificate $certificate Certificate to watermark
 * @return Image Watermarked image
 */
function applyWatermark($im, WT_Perso_Certificate $certificate) {
	global $useTTF;

	$module = new perso_certificates_WT_Module();
	
	// text to watermark with
	$word1_text   = $certificate->getWatermarkText();
	// minimum font size for "word1" ;
	$word1_minsize = $module->getSetting('PC_WM_FONT_MINSIZE', 8);
	// maximum font size for "word1" ; will be automaticaly reduced to fit in the image
	$word1_maxsize = $module->getSetting('PC_WM_FONT_MAXSIZE', 18);
	// rgb color codes for text
	$word1_color  = $module->getSetting('PC_WM_FONT_COLOR', '77,109,243');
	// ttf font file to use. must exist in the includes/fonts/ folder
	$word1_font   = "";
	// vertical position for the text to past; possible values are: top, middle or bottom, across
	$word1_vpos   = "top";
	// horizontal position for the text to past in certificate file; possible values are: left, right, top2bottom, bottom2top
	// this value is used only if $word1_vpos=across
	$word1_hpos   = "left";

	embedText($im, $word1_text, $word1_maxsize, $word1_minsize, $word1_color, $word1_font, $word1_vpos, $word1_hpos);

	return ($im);
}

/**
 * Embed a text in an image. 
 * Similar to the the media firewall function.
 * 
 * @param Image $im Image to watermark
 * @param string $text Text to display
 * @param int $maxsize Maximum size for the font
 * @param int $minsize Minimum size for the font
 * @param string $color Font color
 * @param string $font Font to be used
 * @param string $vpos Description of the vertical position (top, middle, bottom, accross)
 * @param string $hpos Description of the horizontal position (right, left, top2bottom, bottom2top)
 */
function embedText($im, $text, $maxsize, $minsize, $color, $font, $vpos, $hpos) {
	global $useTTF;

	// there are two ways to embed text with PHP
	// (preferred) using GD and FreeType you can embed text using any True Type font
	// (fall back) if that is not available, you can insert basic monospaced text
	if ($useTTF) {
		// imagettftext is available, make sure the requested font exists
		if (!isset($font)||($font=='')||!file_exists(WT_ROOT.'includes/fonts/'.$font)) {
			$font = 'DejaVuSans.ttf'; // this font ships with webtrees
			if (!file_exists(WT_ROOT.'includes/fonts/'.$font)) {
				$useTTF = false;
			}
		}
	}

	# no errors if an invalid color string was passed in, just strange colors
	$col=explode(",", $color);
	$textcolor = @imagecolorallocate($im, $col[0], $col[1], $col[2]);

	// paranoia is good!  make sure all variables have a value
	if (!isset($vpos) || ($vpos!="top" && $vpos!="middle" && $vpos!="bottom" && $vpos!="across")) $vpos = "middle";
	if (($vpos=="across") && (!isset($hpos) || ($hpos!="left" && $hpos!="right" && $hpos!="top2bottom" && $hpos!="bottom2top"))) $hpos = "left";

	// make adjustments to settings that imagestring and imagestringup can't handle
	if (!$useTTF) {
		// imagestringup only writes up, can't use top2bottom
		if ($hpos=="top2bottom") $hpos = "bottom2top";
	}

	$text = WT_I18N::reverseText($text);
	$height = imagesy($im);
	$width  = imagesx($im);
	$calc_angle=rad2deg(atan($height/$width));
	$hypoth=$height/sin(deg2rad($calc_angle));

	// vertical and horizontal position of the text
	switch ($vpos) {
		case "top":
			$taille=textlength($maxsize, $minsize, $width, $text);
			$pos_y=$taille*2;
			$pos_x=$taille*1.5;
			$rotation=0;
			break;
		case "middle":
			$taille=textlength($maxsize, $minsize, $width, $text);
			$pos_y=($height+$taille)/2;
			$pos_x=$width*0.15;
			$rotation=0;
			break;
		case "bottom":
			$taille=textlength($maxsize, $minsize, $width, $text);
			$pos_y=($height*.85-$taille);
			$pos_x=$width*0.15;
			$rotation=0;
			break;
		case "across":
			switch ($hpos) {
				case "left":
				$taille=textlength($maxsize, $minsize, $hypoth, $text);
				$pos_y=($height*.85-$taille);
				$taille_text=($taille-2)*(utf8_strlen($text));
				$pos_x=$width*0.15;
				$rotation=$calc_angle;
				break;
				case "right":
				$taille=textlength($maxsize, $minsize, $hypoth, $text);
				$pos_y=($height*.15-$taille);
				$pos_x=$width*0.85;
				$rotation=$calc_angle+180;
				break;
				case "top2bottom":
				$taille=textlength($maxsize, $minsize, $height, $text);
				$pos_y=($height*.15-$taille);
				$pos_x=($width*.90-$taille);
				$rotation=-90;
				break;
				case "bottom2top":
				$taille=textlength($maxsize, $minsize, $height, $text);
				$pos_y = $height*0.85;
				$pos_x = $width*0.15;
				$rotation=90;
				break;
			}
			break;
		default:
	}	
	
	// apply the text
	if ($useTTF) {
		// if imagettftext throws errors, catch them with a custom error handler
		set_error_handler("imagettftextErrorHandler");
		imagettftext($im, $taille, $rotation, $pos_x, $pos_y, $textcolor, 'includes/fonts/'.$font, $text);
		restore_error_handler();
	}
	// don't use an 'else' here since imagettftextErrorHandler may have changed the value of $useTTF from true to false
	if (!$useTTF) {
		if ($rotation!=90) {
			imagestring($im, 5, $pos_x, $pos_y, $text, $textcolor);
		} else {
			imagestringup($im, 5, $pos_x, $pos_y, $text, $textcolor);
		}
	}

}

/**
 * Return a calculated font size to avoid the watermark to be longer than the width of the image.
 * A minimum size is possible however.
 * Similar to the the media firewall function.
 * 
 * @param int $max Maximum (and default) font size
 * @param int $min Minimum font size
 * @param int $mxl Image width
 * @param string $text Text to display
 * @return int Text font size
 */
function textlength($max, $min, $mxl, $text) {
	$taille_c = $max;
	$len = mb_strlen($text);
	while (($taille_c-2)*($len) > $mxl) {
		$taille_c--;
		if ($taille_c == 2) break;
	}
	return max($min, $taille_c);
}

/**
 * Handler for error during generation of the image
 * imagettftext is the function that is most likely to throw an error
 *  
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile Error file
 * @param int $errline Error line
 */
function imagettftextErrorHandler($errno, $errstr) {
	global $useTTF, $serverFilename;
	// log the error
	Log::addErrorLog('Certificate Firewall error: >'.$errstr.'< in file >'.$serverFilename.'< ('.getImageInfoForLog($serverFilename).')');
	
	// change value of useTTF to false so the fallback watermarking can be used.
	$useTTF = false;
	return true;
}

////////////////////////////////////////////////////////////////////////////////

// this needs to be a global variable so imagettftextErrorHandler can set it
global $useTTF;
$useTTF = function_exists('imagettftext');

// certificate object missing/private?
if (!$certificate || !$certificate->canShow()) {
	Log::addMediaLog('Certificate Firewall error: >'.WT_I18N::translate('Missing or private certificate object.').'< with CID >'.$cid.'<');
	send404AndExit();
}

$serverFilename = $certificate->getServerFilename();

if (!file_exists($serverFilename)) {
	Log::addMediaLog('Certificate Firewall error: >'.WT_I18N::translate('The certificate file does not exist.').'< for path >'.$serverFilename.'<');
	send404AndExit();
}

$mimetype = $certificate->mimeType();

$imgsize = $certificate->getImageAttributes();
$protocol = $_SERVER["SERVER_PROTOCOL"];  // determine if we are using HTTP/1.0 or HTTP/1.1
$filetime = $certificate->getFiletime();
$filetimeHeader = gmdate("D, d M Y H:i:s", $filetime).' GMT';
$expireOffset = 3600 * 24;  // tell browser to cache this image for 24 hours
if (WT_Filter::get('cb')) $expireOffset = $expireOffset * 7; // if cb parameter was sent, cache for 7 days 
$expireHeader = gmdate("D, d M Y H:i:s", WT_TIMESTAMP + $expireOffset) . " GMT";

$type = WT_Perso_Functions_Certificates::isImageTypeSupported($imgsize['ext']);
$usewatermark = false;
$pc_show_no_watermark = $this->getSetting('PC_SHOW_NO_WATERMARK', WT_PRIV_HIDE);
// if this image supports watermarks and the watermark module is intalled...
if ($type) {
	if (WT_USER_ACCESS_LEVEL > $pc_show_no_watermark ) {
		// add a watermark
		$usewatermark = true;
	}
}

// determine whether we have enough memory to watermark this image
if ($usewatermark) {
	if (!hasMemoryForImage($serverFilename)) {
		// not enough memory to watermark this file
		$usewatermark = false;
	}
}

$etag = $certificate->getEtag();

// parse IF_MODIFIED_SINCE header from client
$if_modified_since = 'x';
if (@$_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);
}

// parse IF_NONE_MATCH header from client
$if_none_match = 'x';
if (@$_SERVER["HTTP_IF_NONE_MATCH"]) {
	$if_none_match = str_replace("\"", "", $_SERVER["HTTP_IF_NONE_MATCH"]);
}

// add caching headers.  allow browser to cache file, but not proxy
header("Last-Modified: " . $filetimeHeader);
header('ETag: "'.$etag.'"');
header("Expires: ".$expireHeader);
header("Cache-Control: max-age=".$expireOffset.", s-maxage=0, proxy-revalidate");

// if this file is already in the user’s cache, don’t resend it
// first check if the if_modified_since param matches
if (($if_modified_since == $filetimeHeader)) {
	// then check if the etag matches
	if ($if_none_match == $etag) {
		header($protocol." 304 Not Modified");
		exit;
	}
}

// send headers for the image
header('Content-Type: ' . $mimetype);
header('Content-Disposition: filename="' . addslashes(basename($certificate->file)) . '"');

if ($usewatermark) {
	// generate the watermarked image
	$imCreateFunc = 'imagecreatefrom'.$type;
	$im = @$imCreateFunc($serverFilename);

	if ($im) {
		$im = applyWatermark($im, $certificate);

		$imSendFunc = 'image'.$type;
		// send the image
		$imSendFunc($im);
		imagedestroy($im);

		exit;

	} else {
		// this image is defective.  log it
		Log::addErrorLog('Certificate Firewall error: >'.WT_I18N::translate('This certificate file is broken and cannot be watermarked.').'< in file >'.$serverFilename.'< memory used: '.memory_get_usage());
	}
}

// determine filesize of image (could be original or watermarked version)
$filesize = filesize($serverFilename);

// set content-length header, send file
header("Content-Length: " . $filesize);

// Some servers disable fpassthru() and readfile()
if (function_exists('readfile')) {
	readfile($serverFilename);
} else {
	$fp=fopen($serverFilename, 'rb');
	if (function_exists('fpassthru')) {
		fpassthru($fp);
	} else {
		while (!feof($fp)) {
			echo fread($fp, 65536);
		}
	}
	fclose($fp);
}
