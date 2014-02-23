<?php
/**
 * Module Perso General help text
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
	case 'config_title_prefix':
		$title=WT_I18N::translate('Title prefixes');
		$text=WT_I18N::translate('<p>Set possible aristocratic particles to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.</p><p>An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particles).</p>');
		break;
	case 'config_display_CNIL':
		$title=WT_I18N::translate('Display French <em>CNIL</em> disclaimer');
		$text=WT_I18N::translate('<p>Enable this option to display an information disclaimer in the footer required by the French <em>CNIL</em> for detaining personal information on users.</p>');
		break;
	case 'config_cnil_ref':
		$title=WT_I18N::translate('<em>CNIL</em> reference');
		$text=WT_I18N::translate('<p>If the website has been notified to the French <em>CNIL</em>, an authorisation number may have been delivered. Providing this reference will display a message in the footer visible to all users.</p>');
		break;
	case 'config_add_html_header':
		$title=WT_I18N::translate('Include additional HTML in header');
		$text=WT_I18N::translate('<p>Enable this option to include raw additional HTML in the header of the page.</p>');
		break;
	case 'config_show_html_header':
		$title=WT_I18N::translate('Hide additional header');
		$text=WT_I18N::translate('<p>Select the access level until which the additional header should be displayed. The <em>Hide from everyone</em> should be used to show the header to everybody.</p>');
		break;
	case 'config_html_header':
		$title=WT_I18N::translate('Additional HTML in header');
		$text=WT_I18N::translate('<p>If the option has been enabled, the saved HTML will be inserted in the header.</p><p>In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.</p>');
		break;
	case 'config_add_html_footer':
		$title=WT_I18N::translate('Include additional HTML in footer');
		$text=WT_I18N::translate('<p>Enable this option to include raw additional HTML in the footer of the page.</p>');
		break;
	case 'config_show_html_footer':
		$title=WT_I18N::translate('Hide additional footer');
		$text=WT_I18N::translate('<p>Select the access level until which the additional footer should be displayed. The <em>Hide from everyone</em> should be used to show the footer to everybody.</p>');
	case 'config_html_footer':
		$title=WT_I18N::translate('Additional HTML in footer');
		$text=WT_I18N::translate('<p>If the option has been enabled, the saved HTML will be inserted in the footer, before the logo.</p><p>In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.</p>');
		break;
		
}

?>