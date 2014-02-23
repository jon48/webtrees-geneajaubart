<?php
/**
 * Module Perso Hooks help text
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'admin_config':
	$title=WT_I18N::translate('Hooks (Perso)');
	$text=WT_I18N::translate('<p>Hooks are predefined functions that can be called and executed from the core code, on a subscription base.</p><p>This page allows you to manage the different identified hooks within the module directory. You can enable (resp. disable) each of them by ticking (resp. unticking) the checkbox in front of each of them. A custom priority can also be defined, in order to execute the different hooks in a specific order (1 being the highest priority).</p><p>By default, the hooks are enabled when first created. If a module is removed, linked hooks will be removed. If a module is disabled, linked hooks will also be. However, if the module is enabled again, hook need to be enabled manually (if required).</p>');
	break;
}
?>