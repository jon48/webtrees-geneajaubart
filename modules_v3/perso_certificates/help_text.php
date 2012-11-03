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
		$certif_directory = get_module_setting('perso_certificates', 'PC_CERT_ROOTDIR', 'certificates/');
		$text=
			'<p>'.
			WT_I18N::translate('The certificates directory is used to create URLs for your certificates. You will access the certificates by using URL of the form %2$s, if the certificate directory is %1$s.', '<tt style="white-space:nowrap; color:#0000ff; font-weight:bold;">'.$certif_directory.'</tt>', '<tt style="white-space:nowrap; font-weight:bold;">'.WT_SERVER_NAME.WT_SCRIPT_PATH.WT_MODULES_DIR.'perso_certificates/<span style="color:#0000ff;">'.$certif_directory.'</span>certif123.jpg</tt>').
			'</p><p>'.
			WT_I18N::translate('The certificates firewall changes the location of the certificates directory from the public directory %1$s to a private directory such as %2$s.  This allows webtrees to apply privacy filtering to certificates.', '<tt style="white-space:nowrap; font-weight:bold;">'.WT_ROOT.WT_MODULES_DIR.'perso_certificates/<span style="color:#0000ff;">'.$certif_directory.'</span></tt>', '<tt style="white-space:nowrap; font-weight:bold;">'.get_module_setting('perso_certificates', 'PC_CERT_FW_ROOTDIR', 'data/').'<span style="color:#0000ff;">'.$certif_directory.'</span></tt>').
			'</p><p>'.
			WT_I18N::translate('The certificates directory %s must exist, and the webserver must have read and write access to it.', '<tt style="white-space:nowrap; font-weight:bold;">'.WT_SERVER_NAME.WT_SCRIPT_PATH.WT_MODULES_DIR.'perso_certificates/<span style="color:#0000ff;">'.$certif_directory.'</span></tt>').
			'</p><p>'.
			WT_I18N::translate('The certificates directory is shared by all family trees.').
			'</p>';
		break;
	case 'config_show_cert':
		$title=WT_I18N::translate('Show certificates');
		$text=
			'<p>'.
			WT_I18N::translate('Define access level required to display certificates in facts sources. By default, nobody can see the certificates.').
			'</p>';
		break;
	case 'config_cert_fw_rootdir':
		$title=WT_I18N::translate('Certificates firewall root directory');
		$text=
			'<p>'.
			$text=WT_I18N::translate('Directory in which the protected certificates directory can be created.  When this field is empty, the <b>%s</b> directory will be used.', WT_Site::preference('INDEX_DIRECTORY')).
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