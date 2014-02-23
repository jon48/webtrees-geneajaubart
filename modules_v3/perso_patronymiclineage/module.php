<?php
/**
 * Class for Perso Patronymic Lineages module.
 * This module is used for displaying lineages of people having the same surname.
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_patronymiclineage_WT_Module extends WT_Module {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Patronymic Lineages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Display lineages of people holding the same surname.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'patronymiclineage':
				require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
}

?>