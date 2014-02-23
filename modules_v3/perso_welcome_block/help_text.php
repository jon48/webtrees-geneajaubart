<?php
/**
 * Module Perso Welcome Block help text
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
	case 'piwik_enabled':
		$title=WT_I18N::translate('Enable Piwik Statistics');
		$text=
			'<p>'.
			WT_I18N::translate('Enable Piwik statistics, in order to display the number of visits on the related site.').
			'</p>';
		break;	
	case 'piwik_url':
		$title=WT_I18N::translate('Piwik URL');
		$text=
			'<p>'.
			WT_I18N::translate('URL of the Piwik API to request. This is usually the <b>index.php</b> at the root of the Piwik installation').
			'</p>';
		break;	
	case 'piwik_token':
		$title=WT_I18N::translate('Piwik Token');
		$text=
			'<p>'.
			WT_I18N::translate('Token provided by the Piwik installation, under the API tab.').
			'</p>';
		break;	
	case 'piwik_siteid':
		$title=WT_I18N::translate('Piwik Site ID');
		$text=
			'<p>'.
			WT_I18N::translate('Piwik Site ID of the website to follow.').
			'</p>';
		break;	
}

?>