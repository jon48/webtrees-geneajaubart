<?php
/**
 * Certificates firewall
 * Is called when user tries to access a certificate through its public URL
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$debug_certfirewall    = 0; // set to 1 if you want to see certificate firewall values displayed instead of images
$debug_watermark       = 0; // set to 1 if you want to see error messages from the watermark module instead of broken images
$debug_forceImageRegen = 0; // set to 1 if you want to force an image to be regenerated (for debugging only)
$debug_verboseLogging  = 0; // set to 1 for extra logging details

/**
 * Displays an error message according to the error entered.
 * Also displays the requested certificate path to admins. 
 * Similar to the the media firewall function.
 * 
 * @param string $type File extention for the returned image
 * @param string $line1 Error message
 * @param string $line2 Path of the certificate file which causes the errror 
 */
function sendErrorAndExit($type, $line1, $line2 = false) {

	// line2 contains the information that only an admin/editor should see, such as the full path to a file
	if (!WT_USER_CAN_EDIT) {
		$line2 = false;
	}

	// arbitrary maxlen to keep images from getting too wide
	$maxlen = 100;
	$numchars = utf8_strlen($line1);
	if ($numchars > $maxlen) {
		$line1 = utf8_substr($line1, 0, $maxlen);
		$numchars = $maxlen;
	}
	$line1 = reverseText($line1);
	if ($line2) {
		$numchars2 = utf8_strlen($line2);
		if ($numchars2 > $maxlen) {
			$line2 = utf8_substr($line2, $maxlen);
			$numchars2 = $maxlen;
		}
		if ($numchars2 > $numchars) {
			$numchars = $numchars2;
		}
		$line2 = reverseText($line2);
	}

	$type = isImageTypeSupported($type);
	if ($type) {
		// width of image is based on the number of characters
		$width = ($numchars+1) * 6.5;
		$height = 60;

		$im  = imagecreatetruecolor($width, $height);  /* Create a black image */
		$bgc = imagecolorallocate($im, 255, 255, 255); /* set background color */
		$tc  = imagecolorallocate($im, 0, 0, 0);       /* set text color */
		imagefilledrectangle($im, 2, 2, $width-4, $height-4, $bgc); /* create a rectangle, leaving 2 px border */
		imagestring($im, 2, 5, 5, $line1, $tc);
		if ($line2) {
			imagestring($im, 2, 5, 30, $line2, $tc);
		}

		// if we are using mod rewrite, there will be no error status.  be sure to set it
		header('HTTP/1.0 404 Not Found');
		header('Status: 404 Not Found');
		header('Content-Type: image/'.$type);
		$imSendFunc = 'image'.$type;
		$imSendFunc($im);
		imagedestroy($im);
	} else {
		// output a standard html string
		// if we are using mod rewrite, there will be no error status.  be sure to set it
		header('HTTP/1.0 404 Not Found');
		header('Status: 404 Not Found');
		echo "<html ", WT_I18N::html_markup(), "><body>\n";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "<!-- filler space so IE will display the custom 404 error -->";
		echo "\n<div align=\"center\">", $line1, "</div>\n";
		if ($line2) {
			// line2 comes from url
			echo "<div align=\"center\">", htmlspecialchars($line2), "</div>\n";
		}
		echo "</body></html>\n";
	}
	exit;
}


/**
 * Returns the entered image with a watermark printed.
 * Similar to the the media firewall function.
 * 
 * @param Image $im Certificate image to watermark
 * @return Image Watermarked image
 */
function applyWatermark($im) {

	// text to watermark with
	$word1_text   = getWatermarkText();
	// minimum font size for "word1" ;
	$word1_minsize = get_module_setting('perso_certificates', 'PC_WM_FONT_MINSIZE', 8);
	// maximum font size for "word1" ; will be automaticaly reduced to fit in the image
	$word1_maxsize = get_module_setting('perso_certificates', 'PC_WM_FONT_MAXSIZE', 18);
	// rgb color codes for text
	$word1_color  = get_module_setting('perso_certificates', 'PC_WM_FONT_COLOR', '77,109,243');
	// ttf font file to use. must exist in the includes/fonts/ directory
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
 * Returns the watermark text to be displayed.
 * If a source ID has been provided with the certificate, use this image,
 * otherwise try to find a linked source within the GEDCOM (the first occurence found is used).
 * Else a default text is used.
 * 
 * @return string Watermark text
 */
function getWatermarkText(){
	global $certfilenameshort;
	
	$wmtext = get_module_setting('perso_certificates', 'PC_WM_DEFAULT', WT_I18N::translate('This image is protected under copyright law.'));

	// If the parameter sid has been defined, retrieve it.
	$sid = null;
	if (isset($_SERVER['REQUEST_URI'])) {
		$requestedfile = rawurldecode($_SERVER['REQUEST_URI']);
		if (strpos($requestedfile, '?sid=') !== false) {
			$possid = strpos($requestedfile, '?sid=');
			$requestedfile = substr($requestedfile, $possid + 5);
			if(strpos($requestedfile, '&') !== false){
				$requestedfile = substr($requestedfile, 0, strpos($requestedfile, '&'));
			}
			if(preg_match('~^'.addcslashes(WT_REGEX_XREF, '~').'$~', $requestedfile, $match)){
				$sid = $requestedfile;
			}
		}
	}
	
	// Else try to find the source the certificate refers to.
	if(!$sid){
		// Try to find in individual, then families, then other types of records. We are interested in the first available value.
		$ged = WT_DB::prepare("SELECT i_gedcom AS gedrec FROM `##individuals` WHERE i_gedcom LIKE \"%".$certfilenameshort."%\"")
			->fetchOne();
		if(!$ged){
			$ged = WT_DB::prepare("SELECT f_gedcom AS gedrec FROM `##families` WHERE f_gedcom LIKE \"%".$certfilenameshort."%\"")
			->fetchOne(); 
			if(!$ged){
				$ged = WT_DB::prepare("SELECT o_gedcom AS gedrec FROM `##other` WHERE o_gedcom LIKE \"%".$certfilenameshort."%\"")
				->fetchOne(); 
			}
		}	
		//If a record has been found, parse it to find the source reference.	
		if($ged){		
			$gedlines = explode("\n", $ged); 
			$level = 0;
			$levelsource = -1;
			$sid_tmp=null;		
			$sourcefound = false;
			foreach($gedlines as $gedline){
				// Get the level
				if (!$sourcefound && preg_match('~^('.WT_REGEX_INTEGER.') ~', $gedline, $match)) {
					$level = $match[1];
					//If we are not any more within the context of a source, reset
					if($level <= $levelsource){
						$levelsource = -1;
						$sid_tmp = null;
					}
					// If a source, get the level and the reference
					if (preg_match('~^'.$level.' SOUR @('.WT_REGEX_XREF.')@$~', $gedline, $match2)) {
						$levelsource = $level;
						$sid_tmp=$match2[1];
					}
					// If the image has be found, get the source reference and exit.
					if($levelsource>=0 && $sid_tmp && preg_match('~^'.$level.' _ACT '.$certfilenameshort.'~', $gedline, $match3)){
						$sid = $sid_tmp;
						$sourcefound = true;
					}
				}
			}
		}		
	}
	
	// Get the watermark text from the source and repository details.
	if($sid){
		$source = WT_Source::getInstance($sid);
		$wmtext = '&copy;';
		$rid = get_gedcom_value('REPO', 0, $source->getGedcomRecord());
		if($rid){
			$repo = WT_Repository::getInstance($rid);
			$wmtext .= ' '.$repo->getFullName().' - ';
		}
		$wmtext .= $source->getFullName();;
	}
	
	return $wmtext;
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

	$text = reverseText($text);
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
 */
function textlength($max, $min, $mxl, $text) {
	$taille_c = $max;
	$len = utf8_strlen($text);
	while (($taille_c-2)*($len) > $mxl) {
		$taille_c--;
		if ($taille_c == 2) break;
	}
	return max($min, $taille_c);
}



// imagettftext is the function that is most likely to throw an error
/**
 * Handler for error during generation of the image
 * 
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile Error file
 * @param int $errline Error line
 */
function imagettftextErrorHandler($errno, $errstr, $errfile, $errline) {
	global $useTTF, $certfilename;
	// log the error
	AddToLog("Certificate Firewall error: >".$errstr."< in file >".$certfilename."< (".getImageInfoForLog($certfilename).")", 'error');

	// change value of useTTF to false so the fallback watermarking can be used.
	$useTTF = false;
	return true;
}

/**
 * Return the paths of the certicate, based on the URL received.
 * A short path (relative to the certificates directory) and a absolute path in the firewall directory are available
 * 
 * @return array Paths of the certificate
 */
function getCertFileName(){	
	global $cert_fw_rootdir, $cert_rootdir;
	
	$filenamelong = '';
	$filename = '';
	if (isset($_SERVER['REQUEST_URI'])) {
		$cert_rootdir2 = '/'.$cert_rootdir;
		$requestedfile = $_SERVER['REQUEST_URI'];
		// urldecode the request
		$requestedfile = rawurldecode($requestedfile);
		// make sure the requested file is in the certificate directory
		if (strpos($requestedfile, $cert_rootdir2) !== false) {
			// strip off the wt directory and certificate directory from the requested url so just the image information is left
			$filename = substr($requestedfile, strpos($requestedfile, $cert_rootdir2) + strlen($cert_rootdir2));
			// strip the ged param if it was passed on the querystring
			// would be better if this could remove any querystring, but '?' are valid in unix filenames
			if (strpos($filename, '?sid=') !== false) {
				$filename = substr($filename, 0, strpos($filename, '?sid='));
			}
		} else {
			// the certificate directory is not in the file ame
			// or the Certificate Firewall is being called from outside the Certificate directory
				// this condition can be detected by the certificate firewall by calling controller->getServerFilename()
		}
	}
	if($filename!='') {
		$filenamelong = $cert_fw_rootdir.$cert_rootdir.$filename;
	}
	return array($filename, utf8_decode($filenamelong));
}

// ******************************************************
// start processing here

// to allow watermarking of large images, attempt to disable or raise memory limits
// @ini_set("memory_limit", "-1");
// @ini_set("memory_limit", "64M");

// this needs to be a global variable so imagettftextErrorHandler can set it
global $useTTF;
$useTTF = (function_exists("imagettftext")) ? true : false;

global $certfilename, $certfilenameshort, $cert_fw_rootdir, $cert_rootdir;

$cert_fw_rootdir = get_module_setting('perso_certificates', 'PC_CERT_FW_ROOTDIR', 'data/');
$cert_rootdir = get_module_setting('perso_certificates', 'PC_CERT_ROOTDIR', 'certificates/');

// get certfilename from the certificate controller
list($certfilenameshort, $certfilename) = getCertFileName();

if (!$certfilename) {
	// either the server is not setting the REQUEST_URI variable as we expect,
	// or the certificate firewall is being used from outside the certificate directory
	// or the certificate file requested is in a different GEDCOM
	$requestedfile = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : "REQUEST_URI NOT SET";
	$exp = explode("?", $requestedfile);
	$pathinfo = pathinfo($exp[0]);
	$ext = @strtolower($pathinfo['extension']);
	// have to exit
	sendErrorAndExit($ext, WT_I18N::translate('The certificate reference was not found.'), $requestedfile);
}

if (!file_exists($certfilename)) {
	// the requested file MAY  NOT exist on the server.  bail.
	// Note: the 404 error status is still in effect.
	if (!$debug_certfirewall) sendErrorAndExit('jpg', WT_I18N::translate('The certificate file does not exist.'), $certfilename);
}

$imgsize=@getimagesize($certfilename); // [0]=width [1]=height [2]=filetype ['mime']=mimetype
if (is_array($imgsize)) {
	// this is an image
	$certwidth =0+$imgsize[0];
	$certheight=0+$imgsize[1];
	$imageTypes  =array('','GIF','JPG','PNG','SWF','PSD','BMP','TIFF','TIFF','JPC','JP2','JPX','JB2','SWC','IFF','WBMP','XBM');
	$certext   =$imageTypes[0+$imgsize[2]];
	$certmime  =$imgsize['mime'];
}

// check if the image can be displayed
if(get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE) < WT_USER_ACCESS_LEVEL){
	// if no permissions, bail
	// Note: the 404 error status is still in effect
	if (!$debug_certfirewall) sendErrorAndExit($certext, WT_I18N::translate('You are not allowed to access this certificate.'));
}

$protocol = $_SERVER["SERVER_PROTOCOL"];  // determine if we are using HTTP/1.0 or HTTP/1.1
$filetime = @filemtime($certfilename);
$filetimeHeader = gmdate("D, d M Y H:i:s", $filetime).' GMT';
$expireOffset = 3600 * 24;  // tell browser to cache this image for 24 hours
$expireHeader = gmdate("D, d M Y H:i:s", time() + $expireOffset) . " GMT";

$type = isImageTypeSupported($certext);
$usewatermark = false;
// if this image supports watermarks and the watermark module is intalled...
$pc_show_no_watermark = get_module_setting($this->getName(), 'PC_SHOW_NO_WATERMARK', WT_PRIV_HIDE);
if ($type && function_exists("applyWatermark")) {
	if (WT_USER_ACCESS_LEVEL >= $pc_show_no_watermark ) {
		// add a watermark
		$usewatermark = true;
	}

}

// determine whether we have enough memory to watermark this image
if ($usewatermark) {
	if (!hasMemoryForImage($certfilename, $debug_verboseLogging)) {
		// not enough memory to watermark this file
		$usewatermark = false;
	}
}

// setup the etag.  use enough info so that if anything important changes, the etag won't match
$etag_string = basename($certfilename).$filetime.WT_USER_ACCESS_LEVEL.$pc_show_no_watermark;
$etag = dechex(crc32($etag_string));

// parse IF_MODIFIED_SINCE header from client
$if_modified_since = 'x';
if (@$_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);
}

// parse IF_NONE_MATCH header from client
$if_none_match = 'x';
if (@$_SERVER["HTTP_IF_NONE_MATCH"]) {
	$if_none_match = str_replace('\"', '', $_SERVER["HTTP_IF_NONE_MATCH"]);
}

if ($debug_certfirewall) {
	// this is for debugging the certificate firewall
	header("Last-Modified: " . $filetimeHeader);
	header('ETag: "'.$etag.'"');

	echo  '<table border="1">';
	echo  '<tr><td>Requested URL</td><td>', urldecode($_SERVER['REQUEST_URI']), '</td></tr>';
	echo  '<tr><td>filename</td><td>', $certfilename, '</td></tr>';
	echo  '<tr><td>Extension</td><td>', $certext, '</td></tr>';
	echo  '<tr><td>mimetype</td><td>', $certmime, '</td></tr>';
	echo  '<tr><td>basename($certfilename)</td><td>', basename($certfilename), '</td></tr>';
	echo  '<tr><td>filetime</td><td>', $filetime, '</td></tr>';
	echo  '<tr><td>filetimeHeader</td><td>', $filetimeHeader, '</td></tr>';
	echo  '<tr><td>if_modified_since</td><td>', $if_modified_since, '</td></tr>';
	echo  '<tr><td>if_none_match</td><td>', $if_none_match, '</td></tr>';
	echo  '<tr><td>etag</td><td>', $etag, '</td></tr>';
	echo  '<tr><td>etag_string</td><td>', $etag_string, '</td></tr>';
	echo  '<tr><td>expireHeader</td><td>', $expireHeader, '</td></tr>';
	echo  '<tr><td>protocol</td><td>', $protocol, '</td></tr>';
	echo  '<tr><td>PC_SHOW_NO_WATERMARK</td><td>', $pc_show_no_watermark, '</td></tr>';
	echo  '<tr><td>WT_USER_ACCESS_LEVEL</td><td>', WT_USER_ACCESS_LEVEL, '</td></tr>';
	echo  '<tr><td>usewatermark</td><td>', $usewatermark, '</td></tr>';
	echo  '<tr><td>type</td><td>', $type, '</td></tr>';
	echo  '</table>';
	exit;
}
// do the real work here

// add caching headers.  allow browser to cache file, but not proxy
if (!$debug_forceImageRegen) {
	header("Last-Modified: " . $filetimeHeader);
	header('ETag: "'.$etag.'"');
	header("Expires: ".$expireHeader);
	header("Cache-Control: max-age=".$expireOffset.", s-maxage=0, proxy-revalidate");
}

// if this file is already in the user's cache, don't resend it
// first check if the if_modified_since param matches
if (($if_modified_since == $filetimeHeader) && !$debug_forceImageRegen) {
	// then check if the etag matches
	if ($if_none_match == $etag) {
		header($protocol." 304 Not Modified");
		exit;
	}
}

// reset the 404 error
header($protocol." 200 OK");
header("Status: 200 OK");

// send headers for the image
if (!$debug_watermark) {
	header("Content-Type: " . $certmime);
	header('Content-Disposition: inline; filename="'.basename($certfilename).'"');
}

if ($usewatermark) {
	// generate the watermarked image
	$imCreateFunc = 'imagecreatefrom'.$type;
	$im = @$imCreateFunc($certfilename);

	if ($im) {
		if ($debug_verboseLogging) AddToLog("Certificate Firewall log: >about to watermark< file >".$certfilename."< (".getImageInfoForLog($certfilename).") memory used: ".memory_get_usage(), 'media');
		$im = applyWatermark($im);
		if ($debug_verboseLogging) AddToLog("Certificate Firewall log: >watermark complete< file >".$certfilename."< (".getImageInfoForLog($certfilename).") memory used: ".memory_get_usage(), 'media');

		$imSendFunc = 'image'.$type;

		// send the image
		$imSendFunc($im);
		imagedestroy($im);

		if ($debug_verboseLogging) AddToLog("Certificate Firewall log: >done with < file >".$certfilename."< (".getImageInfoForLog($certfilename).") memory used: ".memory_get_usage(), 'media');
		exit;

	} else {
		// this image is defective.  log it
		AddToLog("Certificate Firewall error: >".WT_I18N::translate('This certificate file is broken and cannot be watermarked.')."< in file >".$certfilename."< (".getImageInfoForLog($certfilename).") memory used: ".memory_get_usage(), 'error');

		// set usewatermark to false so image will simply be passed through below
		$usewatermark = false;
	}
}


// determine filesize of image (could be original or watermarked version)
$filesize = filesize($certfilename);

// set content-length header, send file
header("Content-Length: " . $filesize);

// Some servers disable fpassthru() and readfile()
if (function_exists('readfile')) {
	readfile($certfilename);
} else {
	$fp=fopen($certfilename, 'rb');
	if (function_exists('fpassthru')) {
		fpassthru($fp);
	} else {
		while (!feof($fp)) {
			echo fread($fp, 65536);
		}
	}
	fclose($fp);
}

?>