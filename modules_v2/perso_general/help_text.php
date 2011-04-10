<?php
/**
 * Module Perso General help text
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

switch ($help) {
case 'config_title_prefix':
	$title=WT_I18N::translate('Title prefixes');
	$text=WT_I18N::translate('<p>Set possible aristocratic particules to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.</p><p>An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particules).</p>');
	break;
}

?>