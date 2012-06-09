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

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate'); 
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

$city = safe_GET('city');
$contains = safe_GET('term');

if($city && $contains){
	WT_Perso_Functions_Certificates::printCertificateListBeginWith($city, $contains);
}


?>