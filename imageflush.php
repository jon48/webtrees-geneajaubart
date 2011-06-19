<?php
// Flush an image to the browser
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: imageflush.php 11753 2011-06-09 16:39:50Z greg $

define('WT_SCRIPT_NAME', 'imageflush.php');
require './includes/session.php';

/**
 * display any message as a PNG image
 *
 * @param string $txt message to be displayed
 */
function ImageFlushError($txt) {
	Header('Content-Type: image/png');
	$image = imagecreate (
		safe_GET_integer('width', 100, 1000, 400),
		safe_GET_integer('height', 40, 400, 100)
	);
	$bg = imagecolorallocate($image, 0xEE, 0xEE, 0xEE);
	$red= Imagecolorallocate($image, 0xFF, 0x00, 0x00);
	imagestring($image, 2, 10, 10, $txt, $red);
	imagepng($image);
}

// Get image_type
$image_type=safe_GET('image_type', WT_REGEX_ALPHA, 'png');
if ($image_type=='jpg') {
	$image_type='jpeg';
}

// Get name of SESSION variable containing an image filename
// These names are generated by WT
$tempVarName=safe_GET('image_name', WT_REGEX_ALPHANUM, 'graphFile');

// read image_data from SESSION variable or from file pointed to by SESSION variable
if (isset($_SESSION['image_data'])) {
	$image_data=@$_SESSION['image_data'];
	$image_data=@unserialize($image_data);
	unset($_SESSION['image_data']);
} else {
	if (isset($_SESSION[$tempVarName])) {
		$image_data=file_get_contents($_SESSION[$tempVarName]);
		unlink($_SESSION[$tempVarName]);
		unset($_SESSION[$tempVarName]);
	}
}
if (empty($image_data)) {
	ImageFlushError('Error: $_SESSION["image_data"] or $_SESSION["'.$tempVarName.'"] is empty');
} else {
	// send data to browser
	header('Content-Type: image/'.$image_type);
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
	header('Pragma: no-cache');
	echo $image_data;
}
?>
