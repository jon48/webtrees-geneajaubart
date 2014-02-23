<?php
/**
 * Class for Perso IsSourced module.
 * This module is used for identifiying sources completion level.
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class perso_issourced_WT_Module extends WT_Module implements WT_Module_Sidebar, WT_Perso_Module_HookSubscriber, WT_Perso_Module_IndividualHeaderExtender, WT_Perso_Module_RecordNameTextExtender {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Sourced events');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Indicate if events related to an record are sourced.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
 			'h_extend_indi_header_icons' => 10,
			'h_rn_append' => 50
		);
	}

	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_icons(WT_Controller_Individual $ctrlIndi) {
		if($ctrlIndi){
			$dindi = new WT_Perso_Individual($ctrlIndi->getSignificantIndividual());
			if ($dindi->canDisplayIsSourced()) return WT_Perso_Functions_Print::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1, 'large');
		}
		return '';
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_left(WT_Controller_Individual $ctrlIndi) {
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_right(WT_Controller_Individual $ctrlIndi) {
	}
	
	//Implement WT_Perso_Module_RecordNameTextExtender
	public function h_rn_prepend(WT_GedcomRecord $grec){ }
	
	//Implement WT_Perso_Module_RecordNameTextExtender
	public function h_rn_append(WT_GedcomRecord $grec){
		$html = '';
		if($grec instanceof WT_Individual){
			$dindi = new WT_Perso_Individual($grec);
			$html .= WT_Perso_Functions_Print::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1, 'small');
			$html .= WT_Perso_Functions_Print::formatIsSourcedIcon('E', $dindi->isBirthSourced(), 'BIRT', 1, 'small');
			if($grec->isDead()) $html .= WT_Perso_Functions_Print::formatIsSourcedIcon('E', $dindi->isDeathSourced(), 'DEAT', 1, 'small');			
		}
		return $html;
	}
	
	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 15;
	}
	
	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}
	
	// Implement WT_Module_Sidebar
	public function getSidebarContent() {	
		global $controller;
				
		ob_start();
		$root = $controller->getSignificantIndividual();
		if ($root) {		
			$dindi = new WT_Perso_Individual($root);
			
			if (!$dindi->canDisplayIsSourced()) {
				print_privacy_error();
			} else {
				echo '<table class="issourcedtable">';
				echo '<tr><td class="slabel">'.WT_Gedcom_Tag::getLabel('INDI').'</td><td class="svalue">'.WT_Perso_Functions_Print::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1).'</td></tr>';
				echo '<tr><td class="slabel">'.WT_Gedcom_Tag::getLabel('BIRT').'</td><td class="svalue">'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $dindi->isBirthSourced(), 'BIRT', 1).'</td></tr>';
				$fams = $root->getSpouseFamilies();
				($ct = count($fams)) > 1 ? $nb=1 : $nb=' ';
				foreach($fams as $fam){
					$dfam = new WT_Perso_Family($fam);
					echo '<tr><td class="slabel right"><a href="'.$fam->getHtmlUrl().'"> '.WT_Gedcom_Tag::getLabel('MARR');
					if($ct > 1){
						echo ' ',$nb;
						$nb++;
					}
					echo '</a></td><td class="svalue">'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $dfam->isMarriageSourced(), 'MARR', 1).'</td></tr>';
				}			
				if($root->isDead()) echo '<tr><td class="slabel">'.WT_Gedcom_Tag::getLabel('DEAT').'</td><td class="svalue">'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $dindi->isDeathSourced(), 'DEAT', 1).'</td></tr>';	
				echo '</table>';
			}
		}		
		return ob_get_clean();
	}
	
	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}
	
	
}

?>