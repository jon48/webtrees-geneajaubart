<?php
/**
 * Module Perso Certificates help text
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
	case 'config_cert_rootdir':
		$title=WT_I18N::translate('Certificates directory');
		$text=
			'<p>'.
			WT_I18N::translate('This folder will be used to store the certificate files.').
			'</p><p>'.
			WT_I18N::translate('If you select a different folder, you must also move any certificate files from the existing folder to the new one.').
			'</p>';
		break;
	case 'config_show_cert':
		$title=WT_I18N::translate('Show certificates');
		$text=
			'<p>'.
			WT_I18N::translate('Define access level required to display certificates in facts sources. By default, nobody can see the certificates.').
			'</p>';
		break;
	case 'config_show_no_watermark':
		$title=WT_I18N::translate('Show non-watermarked certificates');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Define access level required to see certificate images without any watermark. By default, everybody will see the watermark.').
			'</p><p>'.
			$text=WT_I18N::translate('When displayed, the watermark is generated from the name of the repository and of the sources, if they exist. Otherwise, a default text is displayed.').
			'</p>';
		break;
	case 'config_wm_default':
		$title=WT_I18N::translate('Default watermark');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Text to be displayed by default if no source has been associated with the certificate.').
			'</p>';
		break;
	case 'config_wm_font_color':
		$title=WT_I18N::translate('Watermark font color');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Font color for the watermark. By default, <span style="color:#4d6df3;">the color (77,109,243)</span> is used.').
			'</p><p>'.
			$text=WT_I18N::translate('This parameter must be entered with the format <strong>RR,GG,BB</strong> with <strong>RR</strong>, <strong>GG</strong> and <strong>BB</strong> the respective <span style="color:#ff0000;">red</span>, <span style="color:#00ff00;">green</span> and <span style="color:#0000ff;">blue</span> components as decimal integers (between 0 and 255).').
			'</p>';
		break;
	case 'config_wm_font_minsize':
		$title=WT_I18N::translate('Watermark minimum font size');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Watermark minimum font size').
			'</p>';
		break;
	case 'config_wm_font_maxsize':
		$title=WT_I18N::translate('Watermark maximum font size');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Watermark maximum font size').
			'</p>';
		break;		
}

?>