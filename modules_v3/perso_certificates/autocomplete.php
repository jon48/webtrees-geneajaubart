<?php
/**
 * Results for autocompletion of certificate names
 *
 * @package webtrees
 * @subpackage SubPackage
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$controller = new WT_Perso_Controller_Json();

$city = WT_Filter::get('city');
$contains = WT_Filter::get('term');

$controller->pageHeader();
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if($city && $contains){
	WT_Perso_Functions_Certificates::printCertificateListBeginWith($city, $contains);
}


?>