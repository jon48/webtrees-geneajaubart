<?php
/**
 * Results for autocompletion of certificate names
 *
 * @package webtrees
 * @subpackage SubPackage
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$controller = new WT_Perso_Controller_Json();

$city = safe_GET('city');
$contains = safe_GET('term');

$controller->pageHeader();
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if($city && $contains){
	WT_Perso_Functions_Certificates::printCertificateListBeginWith($city, $contains);
}


?>