<?php
/**
 * {Description}
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

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate'); 
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 

$city = safe_GET('city');
$contains = safe_GET('q');
$limit = safe_GET('limit');

if($city && $contains){
	WT_Perso_Functions_Certificates::printCertificateListBeginWith($city, $contains, $limit);
}


?>