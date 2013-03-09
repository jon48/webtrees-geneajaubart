<?php
/**
 * Class for Perso Sosa module.
 * This module is used for calculating and displaying Sosa ancestors.
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

// Create tables, if not already present
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_sosa/db_schema/', 'PSOSA_SCHEMA_VERSION', 1);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class perso_sosa_WT_Module extends WT_Module implements WT_Module_Menu, WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable, WT_Perso_Module_IndividualHeaderExtender, WT_Perso_Module_RecordNameTextExtender {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Sosa');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Calculate and display Sosa ancestors of the root person.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'computesosa':
				$this->computeSosaAjax();
				break;
			case 'computesosaindi':
				$this->computeSosaFromIndiAjax();
				break;
			case 'computesosainterface':
				$this->computeSosaEditInterface();
				break;
			case 'statistics':
			case 'sosalist':
			case 'missingancestors':
				require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
				break;
			case 'ajaxsosalistdata':
				$this->getSosaListDataAjax();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement WT_Module_Menu
	public function defaultMenuOrder(){
		return 5;
	}
	
	// Implement WT_Module_Menu
	public function getMenu(){	
		global $controller;
		
		$menu = null;
		if(WT_Perso_Functions_Sosa::isModuleOperational()){
			//-- main menu
			$menu = new WT_Menu(WT_I18N::translate('Sosa Statistics'), 'module.php?mod=perso_sosa&mod_action=statistics', 'menu-perso-sosa', 'down');
	
			//-- Sosa ancestors list
			$submenu = new WT_Menu(WT_I18N::translate('Sosa Ancestors'), 'module.php?mod=perso_sosa&mod_action=sosalist', 'menu-perso-sosa-list');
			$menu->addSubMenu($submenu);
			
			//-- Missing ancestors list
			$submenu = new WT_Menu(WT_I18N::translate('Missing Ancestors'), 'module.php?mod=perso_sosa&mod_action=missingancestors', 'menu-perso-sosa-missing');
			$menu->addSubMenu($submenu);
			
			//-- Sosa statistics
			$submenu = new WT_Menu(WT_I18N::translate('Sosa Statistics'), 'module.php?mod=perso_sosa&mod_action=statistics', 'menu-perso-sosa-stats');
			$menu->addSubMenu($submenu);
			
			// Add Geographical Dispersion, if active
			if (array_key_exists('perso_geodispersion', WT_Module::getActiveModules()) && count(WT_Perso_Functions_Map::getEnabledGeoDispersionMaps())>0) {
				$submenu = new WT_Menu(WT_I18N::translate('Geographical Dispersion'), 'module.php?mod=perso_geodispersion&mod_action=geodispersion', 'menu-perso-sosa-geodispersion');
				// Add a submenu showing all available geodispersion maps
				foreach (WT_Perso_Functions_Map::getEnabledGeoDispersionMaps() as $map) {
					$subsubmenu = new WT_Menu($map['title'], 'module.php?mod=perso_geodispersion&mod_action=geodispersion&geoid='.$map['id'],
												'menu-perso-sosa-geodispersion-'.$map['id'] // We don't use these, but a custom theme might
					);
					$submenu->addSubmenu($subsubmenu);
				}
				$menu->addSubMenu($submenu);
			}
			
			//-- recompute Sosa submenu
			if (WT_USER_CAN_EDIT && !empty($controller) && $controller instanceof WT_Controller_Individual ) {
				$controller
					->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/computesosaindi.js')
					->addInlineJavascript('var PS_Dialog_Title = "'.WT_I18N::translate('Sosas computation').'";');
				;
					
				$submenu = new WT_Menu(WT_I18N::translate('Complete Sosas'), '#', 'menu-sosa-recompute');
				$submenu->addOnclick('return compute_sosa(\''.$controller->getSignificantIndividual()->getXref().'\');');
				$menu->addSubMenu($submenu);
			}
		}
		return $menu;
	}
	
	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
			'h_config_tab_name' => 30,
			'h_config_tab_content' => 30,
			'h_extend_indi_header_icons' => 20,
			'h_extend_indi_header_right' => 20,
			'h_rn_append' => 20
		);
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('Sosa'), '</span></a></li>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		global $controller;
		
		$controller->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/computesosa.js');
		
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		foreach(WT_Tree::getAll() as $tree){
			if(userGedcomAdmin(WT_USER_ID, $tree->tree_id)){
				echo '<dt>', WT_I18N::translate('Root individual for <em>%s</em>', $tree->tree_title), help_link('config_root_indi', $this->getName()), '</dt>',
					'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('gedcom_setting-PERSO_PS_ROOT_INDI-'.$tree->tree_id, get_gedcom_setting($tree->tree_id, 'PERSO_PS_ROOT_INDI'), $controller),'</dd>',
					'<dt>', WT_I18N::translate('Compute all Sosas for <em>%s</em>', $tree->tree_title), help_link('config_computesosa', $this->getName()), '</dt>',
					'<dd><button id="bt_'.$tree->tree_id.'" class="progressbutton" onClick="calculateSosa(\''.$tree->tree_id.'\');"><div id="btsosa_'.$tree->tree_id.'">'.WT_I18N::translate('Compute').'</div></button></dd>';
			}
		}
		echo '</dl></td></tr></table></div>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		return $value;
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_icons(WT_Controller_Individual $ctrlIndi) {
		if($ctrlIndi){
			$dindi = new WT_Perso_Person($ctrlIndi->getSignificantIndividual());
			return WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'large');
		}
		return '';
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_left(WT_Controller_Individual $ctrlIndi) {
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_right(WT_Controller_Individual $ctrlIndi) {
		if($ctrlIndi){
			$dindi = new WT_Perso_Person($ctrlIndi->getSignificantIndividual());
			return array('indi-header-sosa',  WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 2, 'normal'));
		}
		return '';
	}
	
	//Implement WT_Perso_Module_RecordNameTextExtender
	public function h_rn_prepend(WT_GedcomRecord $grec){
	}
	
	//Implement WT_Perso_Module_RecordNameTextExtender
	public function h_rn_append(WT_GedcomRecord $grec){
		$html = '';
		if($grec instanceof WT_Person){ // Only apply to individuals
			$dindi = new WT_Perso_Person($grec);
			$html .= WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'small');
		}
		return $html;
	}
	
	/**
	 * Compute asynchronously the complete Sosa list from the root individual, and return the result.
	 *
	 * @return string HTML code result to display
	 */
	private function computeSosaAjax(){
		global $GEDCOM, $tmp_sosatable;
				
		$controller=new WT_Controller_Ajax();
		
		$html = '<i class="icon-perso-error" title="'.WT_I18N::translate('Error').'"></i>';
		
		$ged_id = safe_GET_integer('gid', 0, 1000000, WT_GED_ID);
		if($ged_id && userGedcomAdmin(WT_USER_ID, $ged_id)){		
			$old_gedcom = $GEDCOM;
			$GEDCOM = get_gedcom_from_id($ged_id);
			$pid = get_gedcom_setting($ged_id, 'PERSO_PS_ROOT_INDI');
			if($pid){
				WT_Perso_Functions_Sosa::deleteAllSosas($ged_id);
				$dindi = WT_Perso_Person::getIntance($pid);
				if($dindi){
					$tmp_sosatable = array();		
					$dindi->addAndComputeSosa(1);
					WT_Perso_Functions_Sosa::flushTmpSosaTable(true);
					$html = '<i class="icon-perso-success" title="'.WT_I18N::translate('Success').'"></i>';
				}
			}
			$GEDCOM = $old_gedcom;			
		}
		
		$html .= '&nbsp;'.WT_I18N::translate('Recompute');
		
		$controller->pageHeader();
		echo $html;		
	}
	
	/**
	 * Compute asynchronously the Sosa list from the individual enters in parameter, and return the result.
	 *
	 * @return string HTML code result to display
	 */
	private function computeSosaFromIndiAjax(){
		global $tmp_removeSosaTab, $tmp_sosatable;
		
		$controller=new WT_Controller_Ajax();
		
		$html = '<i class="icon-perso-error" title="'.WT_I18N::translate('Error').'"></i>';
		
		$pid = safe_GET_xref('pid');
		if(WT_USER_CAN_EDIT && $pid){
			$indi = WT_Person::getInstance($pid);
			if($indi){
				$tmp_removeSosaTab = array();
				$tmp_sosatable = array();
				$dindi = new WT_Perso_Person($indi);
				$current_sosa = $dindi->getSosaNumbers();
				if($current_sosa && count($current_sosa)>0){
					$dindi->removeSosas();
					WT_Perso_Functions_Sosa::flushTmpRemoveTable(true);
					foreach($current_sosa as $sosa => $gen){
						$dindi->addAndComputeSosa($sosa);
					}
					WT_Perso_Functions_Sosa::flushTmpSosaTable(true);
					$html = '<i class="icon-perso-success" title="'.WT_I18N::translate('Success').'"></i>&nbsp;'.WT_I18N::translate('Computed');
				}
				else{
					$html .= '&nbsp;'.WT_I18N::translate('Individual is not a Sosa');	
				}
			}
			else{
				$html .= '&nbsp;'.WT_I18N::translate('Non existing individual');		
			}			
		}
		else{
			$html .= '&nbsp;'.WT_I18N::translate('You are not allowed to perform this operation.');	
		}
		
		$controller->pageHeader();		
		echo $html;		
	}
	
	/**
	 * Returns asynchronously the HTML formatted table of Sosa individual/families
	 * 
	 *@return string HTML code for the individual or family table
	 */
	private function getSosaListDataAjax(){
		global $controller;
		
		$gen = safe_GET('gen', WT_REGEX_INTEGER, null);
		$type = safe_GET('type', '(indi|fam)', null);
		
		if(is_null($gen) || is_null($type)){
			header('HTTP/1.0 404 Not Found');
			exit;
		}
		
		$controller = new WT_Controller_Ajax();
		$html = '<p class="warning">'.WT_I18N::translate('An error occurred while retrieving data...').'<p>';
				
		if($gen){			
			switch ($type){
				case 'indi':
					$listSosa=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen);
					$html = $this->format_sosa_indi_table($listSosa, $gen);
					break;
				case 'fam':
					$listFamSosa = WT_Perso_Functions_Sosa::getFamilySosaListAtGeneration($gen);
					$html = $this->format_sosa_fam_table($listFamSosa, $gen);
					break;
				default:
					break;
			}			
		}
		
		$controller->pageHeader();
		echo $html;		
	}
	

	/**
	 * print a sortable table of sosa individuals
	 *
	 * @param array $sosalist contain Sosa individuals.
	 * @param int $gen Generation
	 * @param string $legend Optional legend
	 * @return string HTML code for the sosa table
	 */
	function format_sosa_indi_table($sosalist, $gen, $legend='') {
		global $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;
		$table_id = 'IDindi'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
		$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
		if (count($sosalist)<1) return; 
		$html = '';
		$controller
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
			->addInlineJavascript('
				/* Initialise datatables */
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
				
				oTable'.$table_id.' = jQuery("#'.$table_id.'").dataTable( {
					"sDom": \'<"H"<"filtersH_'.$table_id.'"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_'.$table_id.'">>\',
					'.WT_I18N::datatablesI18N(array(16, 32, 64, 128, -1)).',
					"bJQueryUI": true,
					"bAutoWidth":false,
					"bProcessing": true,
					"bRetrieve": true,
					"aoColumns": [
						/* 0-Sosa */  		{ "sType": "numeric", "sClass": "center" },
		                /* 1-ID */ 			{ "bVisible": false },
		                /* 2-givn */ 		{"iDataSort": 4,  "sClass": "left"},
						/* 3-surn */ 		{"iDataSort": 5},
						/* 4-GIVN,SURN */ 	{"sType": "unicode", "bVisible": false},
						/* 5-SURN,GIVN */ 	{"sType": "unicode", "bVisible": false},
		                /* 6-Birth */		{ "iDataSort" : 7 , "sClass": "center"},
		                /* 7-SORT_BIRT */	{ "bVisible" : false},
		                /* 8-BIRT_PLAC */	{ "sType": "unicode", "sClass": "center"},
		                /* PERSO Modify table to include IsSourced module */
		                /* 9-BIRT_SOUR */	{ "iDataSort" : 10, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
		                /* 10-SORT_BIRTSC */{ "bVisible" : false},
		                /* 11-Death */		{ "iDataSort" : 12 , "sClass": "center"},
		                /* 12-SORT_DEAT */	{ "bVisible" : false},
		                /* 13-Age */		{ "iDataSort" : 14 , "sClass": "center"},
		                /* 14-AGE */		{ "sType": "numeric", "bVisible": false},
		                /* 15-DEAT_PLAC */	{ "sType": "unicode", "sClass": "center" },
		                /* 16-DEAT_SOUR */	{ "iDataSort" : 17, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
		                /* 17-SORT_DEATSC */{ "bVisible" : false},
		                /* 18-SEX */		{ "bVisible" : false},
		                /* 19-BIRT */		{ "bVisible" : false},
		                /* 20-DEAT */		{ "bVisible" : false},
		                /* 21-TREE */		{ "bVisible" : false}
		                /* END PERSO */
					],
		            "aaSorting": [[0,"asc"]],
					"iDisplayLength": 16,
					"sPaginationType": "full_numbers"
			   });
			 
				jQuery("div.filtersH_'.$table_id.'").html("'.addslashes(
							'<button type="button" id="SEX_M_'.    $table_id.'" class="ui-state-default SEX_M" title="'.    WT_I18N::translate('Show only males.').'">&nbsp;'.WT_Person::sexImage('M', 'small').'&nbsp;</button>'.
							'<button type="button" id="SEX_F_'.    $table_id.'" class="ui-state-default SEX_F" title="'.    WT_I18N::translate('Show only females.').'">&nbsp;'.WT_Person::sexImage('F', 'small').'&nbsp;</button>'.
							'<button type="button" id="SEX_U_'.    $table_id.'" class="ui-state-default SEX_U" title="'.    WT_I18N::translate('Show only persons of whom the gender is not known.').'">&nbsp;'.WT_Person::sexImage('U', 'small').'&nbsp;</button>'.
							'<button type="button" id="DEAT_N_'.   $table_id.'" class="ui-state-default DEAT_N" title="'.   WT_I18N::translate('Show people who are alive or couples where both partners are alive.').'">'.WT_I18N::translate('Alive').'</button>'.
							'<button type="button" id="DEAT_Y_'.   $table_id.'" class="ui-state-default DEAT_Y" title="'.   WT_I18N::translate('Show people who are dead or couples where both partners are deceased.').'">'.WT_I18N::translate('Dead').'</button>'.
							'<button type="button" id="DEAT_YES_'. $table_id.'" class="ui-state-default DEAT_YES" title="'. WT_I18N::translate('Show people who died more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&gt;100</button>'.
							'<button type="button" id="DEAT_Y100_'.$table_id.'" class="ui-state-default DEAT_Y100" title="'.WT_I18N::translate('Show people who died within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&lt;=100</button>'.
							'<button type="button" id="BIRT_YES_'. $table_id.'" class="ui-state-default BIRT_YES" title="'. WT_I18N::translate('Show persons born more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&gt;100</button>'.
							'<button type="button" id="BIRT_Y100_'.$table_id.'" class="ui-state-default BIRT_Y100" title="'.WT_I18N::translate('Show persons born within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&lt;=100</button>'.
							'<button type="button" id="TREE_R_'   .$table_id.'" class="ui-state-default TREE_R" title="'.   WT_I18N::translate('Show Â«rootsÂ» couples or individuals.  These people may also be called Â«patriarchsÂ».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
							'<button type="button" id="TREE_L_'.   $table_id.'" class="ui-state-default TREE_L" title="'.   WT_I18N::translate('Show Â«leavesÂ» couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'">'.WT_I18N::translate('Leaves').'</button>'.
							'<button type="button" id="RESET_'.    $table_id.'" class="ui-state-default RESET" title="'.    WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>'
					).'");
		
				jQuery("div.filtersF_'.$table_id.'").html("'.addslashes(
							'<button type="button" class="ui-state-default" id="cb_parents_indi_list_table" onclick="jQuery(\'div.parents_indi_list_table_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show parents').'</button>'.
							'<button type="button" class="ui-state-default" id="charts_indi_list_table" onclick="jQuery(\'div.indi_list_table-charts_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show statistics charts').'</button>'
					).'");
		
			   /* Add event listeners for filtering inputs */
			   /* PERSO Modify table to include IsSourced module */
				jQuery("#SEX_M_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("M", 18 );
					jQuery("#SEX_M_'.$table_id.'").addClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#SEX_F_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("F", 18 );
					jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").addClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#SEX_U_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("U", 18 );
					jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#BIRT_YES_'. $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("YES", 19 );
					jQuery("#BIRT_YES_'.$table_id.'").addClass("ui-state-active");
					jQuery("#BIRT_Y100_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#BIRT_Y100_'.$table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("Y100", 19 );
					jQuery("#BIRT_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#BIRT_Y100_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#DEAT_N_'.   $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("N", 20 );
					jQuery("#DEAT_N_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_Y_'.   $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("^Y", 20, true, false );
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_YES_'. $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("YES", 20 );
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_YES_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_Y100_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_Y100_'.$table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("Y100", 20 );
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y100_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#TREE_R_'.   $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("R", 21 );
					jQuery("#TREE_R_'.$table_id.'").addClass("ui-state-active");
					jQuery("#TREE_L_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#TREE_L_'.   $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("L", 21 );
					jQuery("#TREE_R_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#TREE_L_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#RESET_'.    $table_id.'").click( function() {
					for (i=18; i<=21; i++){
						oTable'.$table_id.'.fnFilter("", i );
					};
					jQuery("div.filtersH_'.$table_id.' button").removeClass("ui-state-active");
				});
				/* END PERSO */
		
				/* This code is a temporary fix for Datatables bug http://www.datatables.net/forums/discussion/4730/datatables_sort_wrapper-being-added-to-columns-with-bsortable-false/p1*/
				jQuery("th div span:eq(3)").css("display", "none");
				jQuery("th div:eq(3)").css("margin", "auto").css("text-align", "center");
				jQuery("th span:eq(8)").css("display", "none");
				jQuery("th div:eq(8)").css("margin", "auto").css("text-align", "center");
			
				jQuery("#sosa-indi-list").css("visibility", "visible");
			
				 /* PERSO Modify table to include IsSourced module */
				jQuery("#charts_indi_list_table").click();
				/* END PERSO */
			');
	
		$stats = new WT_Stats(WT_GEDCOM);
	
		// Bad data can cause "longest life" to be huge, blowing memory limits
		$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;
	
		//-- init chart data
		for ($age=0; $age<=$max_age; $age++) $deat_by_age[$age]="";
		for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]="";
		for ($year=1550; $year<2030; $year+=10) $deat_by_decade[$year]="";
		//--table wrapper
		$html .= '<div id="sosa-indi-list" class="sosa-list">';
		//-- table header
		$html .= '<table id="'.$table_id.'"><thead><tr>';
		$html .= '<th>'.WT_I18N::translate('Sosa').'</th>';
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('INDI').'</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
		$html .= '<th>GIVN</th>';
		$html .= '<th>SURN</th>';
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('BIRT').'</th>';
		$html .= '<th>SORT_BIRT</th>';
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= '<th><i class="icon-source" title="'.WT_I18N::translate('Sourced birth').'" /></th>'.
					'<th>SORT_BIRTSC</th>';
		} else {
			$html .= '<th></th><th></th>';
		}
		//END PERSO
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('DEAT').'</th>';
		$html .= '<th>SORT_DEAT</th>';
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('AGE').'</th>';
		$html .= '<th>AGE</th>';
		$html .= '<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= '<th><i class="icon-source" title="'.WT_I18N::translate('Sourced death').'"></i></th>'.
					'<th>SORT_DEATSC</th>';
		} else {
			$html .= '<th></th><th></th>';
		}
		//END PERSO
		$html .= '<th>SEX</th>';
		$html .= '<th>BIRT</th>';
		$html .= '<th>DEAT</th>';
		$html .= '<th>TREE</th>';
		$html .= '</tr></thead>';
		//-- table body
		$html .= '<tbody>';
		$n = 0;
		$d100y=new WT_Date(date('Y')-100);  // 100 years ago
		$dateY = date('Y');
		$unique_indis=array(); // Don't double-count indis with multiple names.
		foreach ($sosalist as $sosa=>$pid) {
			$person = WT_Person::getInstance($pid);
			/* @var $person Person */
			if (is_null($person)) continue;
			if ($person->getType() !== 'INDI') continue;
			if (!$person->canDisplayName()) {
				continue;
			}
			$dperson = new WT_Perso_Person($person);
			$html .= '<tr>';
			//-- Indi Sosa
			$html .= '<td class="transparent">'.$sosa.'</td>';
			//-- Indi ID
			$html .= '<td class="transparent">'.$person->getXrefLink().'</td>';
			//-- Indi name(s)
			$html .= '<td colspan="2">';
			foreach ($person->getAllNames() as $num=>$name) {
				if ($name['type']=='NAME') {
					$title='';
				} else {
					$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $person)).'"';
				}
				if ($num==$person->getPrimaryName()) {
					$class=' class="name2"';
					$sex_image=$person->getSexImage();
					list($surn, $givn)=explode(',', $name['sort']);
				} else {
					$class='';
					$sex_image='';
				}
				//PERSO Add Sosa Image
				$html .= '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller').'<br/>';
				//END PERSO
			}
			// Indi parents
			$html .= $person->getPrimaryParentsNames('parents_indi_list_table_'.$table_id.' details1', 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- GIVN/SURN
			// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
			//-- Birth date
			$html .= '<td>';
			if ($birth_dates=$person->getAllBirthDates()) {
				foreach ($birth_dates as $num=>$birth_date) {
					if ($num) {
						$html .= '<br/>';
					}
					$html .= $birth_date->Display(!$SEARCH_SPIDER);
				}
				if ($birth_dates[0]->gregorianYear()>=1550 && $birth_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
					$birt_by_decade[(int)($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
				}
			} else {
				$birth_date=$person->getEstimatedBirthDate();
				$birth_jd=$birth_date->JD();
				if ($SHOW_EST_LIST_DATES) {
					$html .= $birth_date->Display(!$SEARCH_SPIDER);
				} else {
					$html .= '&nbsp;';
				}
				$birth_dates[0]=new WT_Date('');
			}
			$html .= '</td>';
			//-- Event date (sortable)hidden by datatables code
			$html .= '<td>'. $birth_date->JD(). '</td>';
			//-- Birth place
			$html .= '<td>';
			foreach ($person->getAllBirthPlaces() as $n=>$birth_place) {
				$tmp=new WT_Place($birth_place, WT_GED_ID);
				if ($n) {
					$html .= '<br>';
				}
				if ($SEARCH_SPIDER) {
					$html .= $tmp->getShortName();
				} else {
					$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getShortName()) . '">';
					$html .= highlight_search_hits($tmp->getShortName()). '</a>';
				}
			}
			$html .= '</td>';
			//PERSO Modify table to include IsSourced module
			if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
				$isBSourced = $dperson->isBirthSourced();
				$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isBSourced, 'BIRT', 1, 'medium').'</td>'.
						'<td>'.$isBSourced.'</td>';
			} else {
				$html .= '<td></td>'.
						'<td></td>';
			}
			//END PERSO
			//-- Death date
			$html .= '<td>';
			if ($death_dates=$person->getAllDeathDates()) {
				foreach ($death_dates as $num=>$death_date) {
					if ($num) {
						$html .= '<br/>';
					}
					$html .= $death_date->Display(!$SEARCH_SPIDER);
				}
				if ($death_dates[0]->gregorianYear()>=1550 && $death_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
					$deat_by_decade[(int)($death_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
				}
			} else {
				$death_date=$person->getEstimatedDeathDate();
				$death_jd=$death_date->JD();
				if ($SHOW_EST_LIST_DATES) {
					$html .= $death_date->Display(!$SEARCH_SPIDER);
				} else if ($person->isDead()) {
					$html .= WT_I18N::translate('yes');
				} else {
					$html .= '&nbsp;';
				}
				$death_dates[0]=new WT_Date('');
			}
			$html .= '</td>';
			//-- Event date (sortable)hidden by datatables code
			$html .= '<td>'. $death_date->JD(). '</td>';
			//-- Age at death
			$age=WT_Date::getAge($birth_dates[0], $death_dates[0], 0);
			if (!isset($unique_indis[$person->getXref()]) && $age>=0 && $age<=$max_age) {
				$deat_by_age[$age].=$person->getSex();
			}
			// Need both display and sortable age
			$html .= '<td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 2) . '</td><td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 1) . '</td>';
			//-- Death place
			$html .= '<td>';
			foreach ($person->getAllDeathPlaces() as $n=>$death_place) {
				$tmp=new WT_Place($death_place, WT_GED_ID);
				if ($n) {
					$html .= '<br>';
				}
				if ($SEARCH_SPIDER) {
					$html .= $tmp->getShortName();
				} else {
					$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getShortName()) . '">';
					$html .= highlight_search_hits($tmp->getShortName()). '</a>';
				}
			}
			$html .= '</td>';
			//PERSO Modify table to include IsSourced module
			if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
				if($person->isDead()){
					$isDSourced = $dperson->isDeathSourced();
					$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isDSourced, 'DEAT', 1, 'medium').'</td>'.
							'<td>'.$isDSourced.'</td>';
				}
				else{
					$html .= '<td>&nbsp;</td>'.
							'<td>-99</td>';
				}
			} else {
				$html .= '<td></td>'.
						'<td></td>';
			}
			//END PERSO
			//-- Sorting by gender
			$html .= '<td>';
			$html .= $person->getSex();
			$html .= '</td>';
			//-- Filtering by birth date
			$html .= '<td>';
			if (!$person->canDisplayDetails() || WT_Date::Compare($birth_date, $d100y)>0) {
				$html .= 'Y100';
			} else {
				$html .= 'YES';
			}
			$html .= '</td>';
			//-- Filtering by death date
			$html .= '<td>';
			// Died in last 100 years?  Died?  Not dead?
			if (WT_Date::Compare($death_date, $d100y)>0) {
				$html .= 'Y100';
			} elseif ($death_date->minJD() || $person->isDead()) {
				$html .= 'YES';
			} else {
				$html .= 'N';
			}
			$html .= '</td>';
			//-- Roots or Leaves ?
			$html .= '<td>';
			if (!$person->getChildFamilies()) { $html .= 'R'; }  // roots
			elseif (!$person->isDead() && $person->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
			else { $html .= '&nbsp;'; }
			$html .= '</td>';
			$html .= '</tr>';
			$unique_indis[$person->getXref()]=true;
			++$n;
		}
		$html .= '</tbody>';
		//Prepare footer
		$nbSosa = count($sosalist);
		$thSosa = pow(2, $gen-1);
		$perc = WT_Perso_Functions::getPercentage($nbSosa, $thSosa);
		//PERSO Modify table to include IsSourced module
		$html .= '<tfoot>'.
				'<tr><td class="ui-state-default" colspan="23">'.WT_I18N::translate('Number of Sosa ancestors: %1$d known / %2$d theoretical (%3$0.2f %%)',$nbSosa, $thSosa, $perc);
		//END PERSO
		if($n != $nbSosa) $html .= ' ['.WT_I18N::translate('%d hidden', $nbSosa - $n).']';
		$html .= '</td></tr></tfoot>';
		$html .= '</table>';
		//-- charts
		$html .= '<div class="indi_list_table-charts_'. $table_id. '" style="display:none">
		<table class="list-charts center"><tr><td>'.
			print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).
			'</td><td>'.
			print_chart_by_decade($deat_by_decade, WT_I18N::translate('Decade of death')).
			'</td></tr><tr><td colspan="2">'.
			print_chart_by_age($deat_by_age, WT_I18N::translate('Age related to death year')).
			'</td></tr></table>
		</div>
		</div>'; // Close "sosa-list"
	
		return $html;
	}
	
	/**
	 * print a sortable table of sosa families
	 *
	 * @param array $sosalist contain Sosa individuals.
	 * @param int $gen Generation
	 * @param string $legend Optional legend
	 * @return string HTML code for the sosa table
	 */
	function format_sosa_fam_table($sosalist, $gen, $legend='') {
		global $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;
		$table_id = 'IDfam'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
		$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
		if (count($sosalist)<1)
			return '<p class="warning">'.WT_I18N::translate('No family has been found for generation %d', $gen).'</p>';
		$html = '';
		$controller
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
			->addInlineJavascript(
				'oTable'.$table_id.'=jQuery("#'.$table_id.'").dataTable( {
					"sDom": \'<"H"<"filtersH_'.$table_id.'"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_'.$table_id.'">>\',
					'.WT_I18N::datatablesI18N(array(16, 32, 64, 128, -1)).',
					"bJQueryUI": true,
					"bAutoWidth":false,
					"bProcessing": true,
					"bRetrieve": true,
					"aoColumns": [
						/* 0-Sosa */  		{ "iDataSort": 1, "sClass": "center"},
		                /* 1-SOSA */ 		{ "sType": "numeric", "bVisible": false },
						/* 2-Husb Givn */ {"iDataSort": 4},
						/* 3-Husb Surn */ {"iDataSort": 5},
						/* 4-GIVN,SURN */ {"sType": "unicode", "bVisible": false},
						/* 5-SURN,GIVN */ {"sType": "unicode", "bVisible": false},
						/* 6-Husb Age  */ {"iDataSort": 7, "sClass": "center"},
						/* 7-AGE       */ {"sType": "numeric", "bVisible": false},
						/* 8-Wife Givn */ {"iDataSort": 10},
						/* 9-Wife Surn */ {"iDataSort": 11},
						/* 10-GIVN,SURN */ {"sType": "unicode", "bVisible": false},
						/* 11-SURN,GIVN */ {"sType": "unicode", "bVisible": false},
						/* 12-Wife Age  */ {"iDataSort": 13, "sClass": "center"},
						/* 13-AGE       */ {"sType": "numeric", "bVisible": false},
						/* 14-Marr Date */ {"iDataSort": 15, "sClass": "center"},
						/* 15-MARR:DATE */ {"bVisible": false},
						/* 16-Marr Plac */ {"sType": "unicode", "sClass": "center"},
						/* 17-Marr Sour */ { "iDataSort" : 18, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
						/* 18-Sort Sour */ { "bVisible": false},
						/* 19-Children  */ {"iDataSort": 20, "sClass": "center"},
						/* 20-NCHI      */ {"sType": "numeric", "bVisible": false},
						/* 21-MARR      */ {"bVisible": false},
						/* 22-DEAT      */ {"bVisible": false},
						/* 23-TREE      */ {"bVisible": false}
						/* END PERSO */
					],
					"aaSorting": [[0, "asc"]],
					"iDisplayLength": 16,
					"sPaginationType": "full_numbers"
			   });
	
				jQuery("div.filtersH_'.$table_id.'").html("'.addslashes(
					'<button type="button" id="DEAT_N_'.    $table_id.'" class="ui-state-default DEAT_N" title="'.    WT_I18N::translate('Show people who are alive or couples where both partners are alive.').'">'.WT_I18N::translate('Both alive').'</button>'.
					'<button type="button" id="DEAT_W_'.    $table_id.'" class="ui-state-default DEAT_W" title="'.    WT_I18N::translate('Show couples where only the female partner is deceased.').'">'.WT_I18N::translate('Widower').'</button>'.
					'<button type="button" id="DEAT_H_'.    $table_id.'" class="ui-state-default DEAT_H" title="'.    WT_I18N::translate('Show couples where only the male partner is deceased.').'">'.WT_I18N::translate('Widow').'</button>'.
					'<button type="button" id="DEAT_Y_'.    $table_id.'" class="ui-state-default DEAT_Y" title="'.    WT_I18N::translate('Show people who are dead or couples where both partners are deceased.').'">'.WT_I18N::translate('Both dead').'</button>'.
					'<button type="button" id="TREE_R_'.    $table_id.'" class="ui-state-default TREE_R" title="'.    WT_I18N::translate('Show «roots» couples or individuals.  These people may also be called «patriarchs».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
					'<button type="button" id="TREE_L_'.    $table_id.'" class="ui-state-default TREE_L" title="'.    WT_I18N::translate('Show «leaves» couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'">'.WT_I18N::translate('Leaves').'</button>'.
					'<button type="button" id="MARR_U_'.    $table_id.'" class="ui-state-default MARR_U" title="'.    WT_I18N::translate('Show couples with an unknown marriage date.').'">'.WT_Gedcom_Tag::getLabel('MARR').'</button>'.
					'<button type="button" id="MARR_YES_'.  $table_id.'" class="ui-state-default MARR_YES" title="'.  WT_I18N::translate('Show couples who married more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('MARR').'&gt;100</button>'.
					'<button type="button" id="MARR_Y100_'. $table_id.'" class="ui-state-default MARR_Y100" title="'. WT_I18N::translate('Show couples who married within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('MARR').'&lt;=100</button>'.
					'<button type="button" id="MARR_DIV_'.  $table_id.'" class="ui-state-default MARR_DIV" title="'.  WT_I18N::translate('Show divorced couples.').'">'.WT_Gedcom_Tag::getLabel('DIV').'</button>'.
					'<button type="button" id="MULTI_MARR_'.$table_id.'" class="ui-state-default MULTI_MARR" title="'.WT_I18N::translate('Show couples where either partner married more than once.').'">'.WT_I18N::translate('Multiple marriages').'</button>'.
					'<button type="button" id="RESET_'.$table_id.'" class="ui-state-default RESET" title="'.WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>'
				).'");
	
				jQuery("div.filtersF_'.$table_id.'").html("'.addslashes(
					'<button type="button" class="ui-state-default" id="cb_parents_'.$table_id.'" onclick="jQuery(\'div.parents_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'.WT_I18N::translate('Show parents').'</button>'.
					'<button type="button" class="ui-state-default" id="charts_fam_list_table" onclick="jQuery(\'div.fam_list_table-charts_'.$table_id.'\').toggle(); jQuery(this).toggleClass(\'ui-state-active\');">'. WT_I18N::translate('Show statistics charts').'</button>'
				).'");
				
				/* Add event listeners for filtering inputs */
				/* PERSO Modify table to include IsSourced module */		
				jQuery("#MARR_U_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("U", 21);
					jQuery("#MARR_U_'.$table_id.'").addClass("ui-state-active");
					jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#MARR_YES_'.  $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("YES", 21);
					jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_YES_'.$table_id.'").addClass("ui-state-active");
					jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#MARR_Y100_'. $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("Y100", 21);
					jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_Y100_'.$table_id.'").addClass("ui-state-active");
					jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#MARR_DIV_'.  $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("D", 21);
					jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_DIV_'.$table_id.'").addClass("ui-state-active");
					jQuery("#MULTI_MARR_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#MULTI_MARR_'.$table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("M", 21);
					jQuery("#MARR_U_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_YES_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_Y100_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MARR_DIV_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#MULTI_MARR_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#DEAT_N_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("N", 22);
					jQuery("#DEAT_N_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_W_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("W", 22);
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_W_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_H_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("H", 22);
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_H_'.$table_id.'").addClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#DEAT_Y_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("Y", 22);
					jQuery("#DEAT_N_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_W_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_H_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#DEAT_Y_'.$table_id.'").addClass("ui-state-active");
				});
				jQuery("#TREE_R_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("R", 23);
					jQuery("#TREE_R_'.$table_id.'").addClass("ui-state-active");
					jQuery("#TREE_L_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#TREE_L_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("L", 23);
					jQuery("#TREE_R_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#TREE_L_'.$table_id.'").addClass("ui-state-active");
				});	
				jQuery("#RESET_'.     $table_id.'").click( function() {
					for (i=21; i<=23; i++) {
						oTable'.$table_id.'.fnFilter("", i );
					};
					jQuery("div.filtersH_'.$table_id.' button").removeClass("ui-state-active");
				});
	
				/* This code is a temporary fix for Datatables bug http://www.datatables.net/forums/discussion/4730/datatables_sort_wrapper-being-added-to-columns-with-bsortable-false/p1*/
				jQuery("th div span:eq(3)").css("display", "none");
				jQuery("th div:eq(3)").css("margin", "auto").css("text-align", "center");
				jQuery("th span:eq(8)").css("display", "none");
				jQuery("th div:eq(8)").css("margin", "auto").css("text-align", "center");
				
				jQuery("#sosa-fam-list").css("visibility", "visible");
				
				jQuery("#charts_fam_list_table").click();
				/* END PERSO */
			');
	
		$stats = new WT_Stats(WT_GEDCOM);
		
		// Bad data can cause "longest life" to be huge, blowing memory limits
		$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;
		$max_age_marr = max($stats->oldestMarriageMaleAge(), $stats->oldestMarriageFemaleAge())+1;
			
		//-- init chart data
		for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]="";
		for ($age=0; $age<=$max_age_marr; $age++) $marr_by_age[$age]='';
		for ($year=1550; $year<2030; $year+=10) $marr_by_decade[$year]='';
		
		//--table wrapper
		$html .= '<div id="sosa-fam-list" class="sosa-list">';
		//-- table header
		$html .= '<table id="'. $table_id. '"><thead><tr>';
		$html .= '<th>'.WT_I18N::translate('Sosa').'</th>';
		$html .= '<th>SOSA</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
		$html .= '<th>HUSB:GIVN_SURN</th>';
		$html .= '<th>HUSB:SURN_GIVN</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>';
		$html .= '<th>AGE</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
		$html .= '<th>WIFE:GIVN_SURN</th>';
		$html .= '<th>WIFE:SURN_GIVN</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>';
		$html .= '<th>AGE</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('MARR'). '</th>';
		$html .= '<th>MARR:DATE</th>';
		$html .= '<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= '<th><i class="icon-source" title="'.WT_I18N::translate('Sourced marriage').'" border="0"></i></th>'.
					'<th>SORT_MARRSC</th>';
		} else {
			$html .= '<th>&nbsp;</th><th></th>';
		}
		//END PERSO
		$html .= '<th><i class="icon-children" title="'. WT_I18N::translate('Children'). '"></i></th>';
		$html .= '<th>NCHI</th>';
		$html .= '<th>MARR</th>';
		$html .= '<th>DEAT</th>';
		$html .= '<th>TREE</th>';
		$html .= '</tr></thead>';
		//-- table body
		$html .= '<tbody>';
		$n = 0;
		$d100y=new WT_Date(date('Y')-100);  // 100 years ago
		$dateY = date('Y');
		$unique_indis=array(); // Don't double-count indis with multiple names.
		foreach ($sosalist as $sosa=>$fid) {
			$sfamily = WT_Family::getInstance($fid);
			/* @var $person Family */
			if (is_null($sfamily)) continue;
			if ($sfamily->getType() !== 'FAM') continue;
			if (!$sfamily->canDisplayName()) continue;
			$dfamily = new WT_Perso_Family($sfamily);
			//END PERSO
			//-- Retrieve husband and wife
			$husb = $sfamily->getHusband();
			if (is_null($husb)) $husb = new WT_Person('');
			$wife = $sfamily->getWife();
			if (is_null($wife)) $wife = new WT_Person('');
			if (!$sfamily->canDisplayDetails()) {
				continue;
			}
			$html .= '<tr>';
			//-- Indi Sosa
			$html .= '<td class="transparent">'.WT_I18N::translate('%1$d/%2$d', $sosa, ($sosa + 1) % 10).'</td>';
			//-- Indi ID
			$html .= '<td class="transparent">'.$sosa.'</td>';
			//-- Husband name(s)
			$html .= '<td colspan="2">';
			foreach ($husb->getAllNames() as $num=>$name) {
				if ($name['type']=='NAME') {
					$title='';
				} else {
					$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $husb)).'"';
				}
				if ($num==$husb->getPrimaryName()) {
					$class=' class="name2"';
					$sex_image=$husb->getSexImage();
					list($surn, $givn)=explode(',', $name['sort']);
				} else {
					$class='';
					$sex_image='';
				}
				// Only show married names if they are the name we are filtering by.
				if ($name['type']!='_MARNM' || $num==$husb->getPrimaryName()) {
					//PERSO Add Sosa Icon
					$dhusb = new WT_Perso_Person($husb);
					$html .= '<a '. $title. ' href="'. $sfamily->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dhusb->getSosaNumbers(), 1, 'smaller'). '<br>';
					//END PERSO
				}
			}
			// Husband parents
			$html .= $husb->getPrimaryParentsNames('parents_'.$table_id.' details1', 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- Husb GIVN
			// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
			$mdate=$sfamily->getMarriageDate();
			//-- Husband age
			$hdate=$husb->getBirthDate();
			if ($hdate->isOK() && $mdate->isOK()) {
				if ($hdate->gregorianYear()>=1550 && $hdate->gregorianYear()<2030) {
					$birt_by_decade[(int)($hdate->gregorianYear()/10)*10] .= $husb->getSex();
				}
				$hage=WT_Date::getAge($hdate, $mdate, 0);
				if ($hage>=0 && $hage<=$max_age) {
					$marr_by_age[$hage].=$husb->getSex();
				}
			}
			$html .= '<td>'.WT_Date::getAge($hdate, $mdate, 2).'</td><td>'.WT_Date::getAge($hdate, $mdate, 1).'</td>';
			//-- Wife name(s)
			$html .= '<td colspan="2">';
			foreach ($wife->getAllNames() as $num=>$name) {
				if ($name['type']=='NAME') {
					$title='';
				} else {
					$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $wife)).'"';
				}
				if ($num==$wife->getPrimaryName()) {
					$class=' class="name2"';
					$sex_image=$wife->getSexImage();
					list($surn, $givn)=explode(',', $name['sort']);
				} else {
					$class='';
					$sex_image='';
				}
				// Only show married names if they are the name we are filtering by.
				if ($name['type']!='_MARNM' || $num==$wife->getPrimaryName()) {
					//PERSO Add Sosa Icon
					$dwife = new WT_Perso_Person($wife);
					$html .= '<a '. $title. ' href="'. $sfamily->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dwife->getSosaNumbers(), 1, 'smaller'). '<br>';
					//END PERSO
				}
			}
			// Wife parents
			$html .= $wife->getPrimaryParentsNames("parents_".$table_id." details1", 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- Wife GIVN
			//-- Husb GIVN
			// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. htmlspecialchars(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. htmlspecialchars(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
			$mdate=$sfamily->getMarriageDate();
			//-- Wife age
			$wdate=$wife->getBirthDate();
			if ($wdate->isOK() && $mdate->isOK()) {
				if ($wdate->gregorianYear()>=1550 && $wdate->gregorianYear()<2030) {
					$birt_by_decade[(int)($wdate->gregorianYear()/10)*10] .= $wife->getSex();
				}
				$wage=WT_Date::getAge($wdate, $mdate, 0);
				if ($wage>=0 && $wage<=$max_age) {
					$marr_by_age[$wage].=$wife->getSex();
				}
			}
			$html .= '<td>'.WT_Date::getAge($wdate, $mdate, 2).'</td><td>'.WT_Date::getAge($wdate, $mdate, 1).'</td>';
			//-- Marriage date
			$html .= '<td>';
			if ($marriage_dates=$sfamily->getAllMarriageDates()) {
				foreach ($marriage_dates as $n=>$marriage_date) {
					if ($n) {
						$html .= '<br>';
					}
					$html .= '<div>'. $marriage_date->Display(!$SEARCH_SPIDER). '</div>';
				}
				if ($marriage_dates[0]->gregorianYear()>=1550 && $marriage_dates[0]->gregorianYear()<2030) {
					$marr_by_decade[(int)($marriage_dates[0]->gregorianYear()/10)*10] .= $husb->getSex().$wife->getSex();
				}
			} else if (get_sub_record(1, '1 _NMR', $sfamily->getGedcomRecord())) {
				$hus = $sfamily->getHusband();
				$wif = $sfamily->getWife();
				if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $hus);
				else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $wif);
				else $html .= WT_Gedcom_Tag::getLabel('_NMR');
			} else if (get_sub_record(1, '1 _NMAR', $sfamily->getGedcomRecord())) {
				$hus = $sfamily->getHusband();
				$wif = $sfamily->getWife();
				if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $hus);
				else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $wif);
				else $html .= WT_Gedcom_Tag::getLabel('_NMAR');
			} else {
				$factdetail = explode(' ', trim($sfamily->getMarriageRecord()));
				if (isset($factdetail)) {
					if (count($factdetail) >= 3) {
						if (strtoupper($factdetail[2]) != "N") {
							$html .= WT_I18N::translate('yes');
						} else {
							$html .= WT_I18N::translate('no');
						}
					} else {
						$html .= '&nbsp;';
					}
				}
			}
			$html .= '</td>';
			//-- Event date (sortable)hidden by datatables code
			$html .= '<td>';
			if ($marriage_dates) {
				$html .= $marriage_date->JD();
			} else {
				$html .= 0;
			}
			$html .= '</td>';
			//-- Marriage place
			$html .= '<td>';
			foreach ($sfamily->getAllMarriagePlaces() as $n=>$marriage_place) {
				$tmp=new WT_Place($marriage_place, WT_GED_ID);
				if ($n) {
					$html .= '<br>';
				}
				if ($SEARCH_SPIDER) {
					$html .= $tmp->getShortName();
				} else {
					$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
					$html .= highlight_search_hits($tmp->getShortName()). '</a>';
				}
			}
			$html .= '</td>';
			//PERSO Modify table to include IsSourced module
			if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
				$isMSourced = $dfamily->isMarriageSourced();
				$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('E', $isMSourced, 'MARR', 1, 'medium').'</td>'.
						'<td>'.$isMSourced.'</td>';
			} else {
				$html .= '<td>&nbsp;</td>'.
						'<td></td>';
			}
			//END PERSO
			//-- Number of children
			$nchi=$sfamily->getNumberOfChildren();
			$html .= '<td>'. WT_I18N::number($nchi). '</td><td>'. $nchi. '</td>';
			//-- Sorting by marriage date
			$html .= '<td>';
			if (!$sfamily->canDisplayDetails() || !$mdate->isOK()) {
				$html .= 'U';
			} else {
				if (WT_Date::Compare($mdate, $d100y)>0) {
					$html .= 'Y100';
				} else {
					$html .= 'YES';
				}
			}
			if ($sfamily->isDivorced()) {
				$html .= 'D';
			}
			if (count($husb->getSpouseFamilies())>1 || count($wife->getSpouseFamilies())>1) {
				$html .= 'M';
			}
			$html .= '</td>';
			//-- Sorting alive/dead
			$html .= '<td>';
			if ($husb->isDead() && $wife->isDead()) $html .= 'Y';
			if ($husb->isDead() && !$wife->isDead()) {
				if ($wife->getSex()=='F') $html .= 'H';
				if ($wife->getSex()=='M') $html .= 'W'; // male partners
			}
			if (!$husb->isDead() && $wife->isDead()) {
				if ($husb->getSex()=='M') $html .= 'W';
				if ($husb->getSex()=='F') $html .= 'H'; // female partners
			}
			if (!$husb->isDead() && !$wife->isDead()) $html .= 'N';
			$html .= '</td>';
			//-- Roots or Leaves
			$html .= '<td>';
			if (!$husb->getChildFamilies() && !$wife->getChildFamilies()) { $html .= 'R'; } // roots
			elseif (!$husb->isDead() && !$wife->isDead() && $sfamily->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
			else { $html .= '&nbsp;'; }
			$html .= '</td></tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		//-- charts
		$html .= '<div class="fam_list_table-charts_'. $table_id. '" style="display:none">
				<table class="list-charts center"><tr><td>'.
				print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).
				'</td><td>'.
				print_chart_by_decade($marr_by_decade, WT_I18N::translate('Decade of marriage')).
				'</td></tr><tr><td colspan="2">'.
				print_chart_by_age($marr_by_age, WT_I18N::translate('Age in year of marriage')).
				'</td></tr></table>
			</div>
		</div>'; // Close "sosa-fam-list"
	
		return $html;
	}
	
	
}

?>