<?php
/**
 * Class for Perso Sosa module.
 * This module is used for calculating and displaying Sosa ancestors.
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

use Rhumsaa\Uuid\Uuid;
use WT\Auth;

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
			if(Auth::isManager($tree)){
				echo '<dt>', WT_I18N::translate('Root individual for <em>%s</em>', $tree->tree_title), help_link('config_root_indi', $this->getName()), '</dt>',
					'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('gedcom_setting-PERSO_PS_ROOT_INDI-'.$tree->tree_id, $tree->getPreference('PERSO_PS_ROOT_INDI'), $controller),'</dd>',
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
			$dindi = new WT_Perso_Individual($ctrlIndi->getSignificantIndividual());
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
			$dindi = new WT_Perso_Individual($ctrlIndi->getSignificantIndividual());
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
		if($grec instanceof WT_Individual){ // Only apply to individuals
			$dindi = new WT_Perso_Individual($grec);
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
		
		$ged_id = WT_Filter::getInteger('gid', 0, PHP_INT_MAX, WT_GED_ID);
		if($ged_id && array_key_exists($ged_id, WT_Tree::getAll()) && $tree = WT_Tree::get($ged_id)){
			if(Auth::isManager($tree)) {		
				//TODO still required?
				//$old_gedcom = $GEDCOM;
				//$GEDCOM = get_gedcom_from_id($ged_id);
				$pid = $tree->getPreference('PERSO_PS_ROOT_INDI');
				if($pid){
					WT_Perso_Functions_Sosa::deleteAllSosas($ged_id);
					$dindi = WT_Perso_Individual::getIntance($pid);
					if($dindi){
						$tmp_sosatable = array();		
						$dindi->addAndComputeSosa(1);
						WT_Perso_Functions_Sosa::flushTmpSosaTable(true);
						$html = '<i class="icon-perso-success" title="'.WT_I18N::translate('Success').'"></i>';
					}
				}
				//$GEDCOM = $old_gedcom;	
			}		
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
		
		$pid = WT_Filter::get('pid', WT_REGEX_XREF);
		if(WT_USER_CAN_EDIT && $pid){
			$indi = WT_Individual::getInstance($pid);
			if($indi){
				$tmp_removeSosaTab = array();
				$tmp_sosatable = array();
				$dindi = new WT_Perso_Individual($indi);
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
		
		$gen = WT_Filter::getInteger('gen');
		$type = WT_Filter::get('type', 'indi|fam', null);
		
		if(is_null($gen) || is_null($type)){
			header('HTTP/1.0 404 Not Found');
			exit;
		}
		
		$controller = new WT_Controller_Ajax();
		$html = '<p class="warning">'.WT_I18N::translate('An error occurred while retrieving data...').'<p>';
				
		if($gen > 0){			
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
		global $WT_TREE ,$SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;
		$table_id = 'table-sosa-indi-' . Uuid::uuid4(); // lists requires a unique ID in case there are multiple lists per page
		$SHOW_EST_LIST_DATES= $WT_TREE->getPreference('SHOW_EST_LIST_DATES');
		if (count($sosalist)<1) return; 
		$controller
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				/* Initialise datatables */
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
				
				jQuery("#'.$table_id.'").dataTable( {
					dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
					'.WT_I18N::datatablesI18N(array(16, 32, 64, 128, -1)).',
					jQueryUI: true,
					autoWidth: false,
					processing: true,
					retrieve: true,
					columns: [
						/* 0-Sosa */  		{ type: "num", class: "center" },
		                /* 1-ID */ 			{ visible: false },
		                /* 2-givn */ 		{ dataSort: 4,  class: "left"},
						/* 3-surn */ 		{ datasort: 5},
						/* 4-GIVN,SURN */ 	{ type: "unicode", visible: false},
						/* 5-SURN,GIVN */ 	{ type: "unicode", visible: false},
		                /* 6-Birth */		{ datasort : 7 , class: "center"},
		                /* 7-SORT_BIRT */	{ visible : false},
		                /* 8-BIRT_PLAC */	{ type: "unicode", class: "center"},
		                /* PERSO Modify table to include IsSourced module */
		                /* 9-BIRT_SOUR */	{ datasort : 10, class: "center", visible: '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
		                /* 10-SORT_BIRTSC */{ visible : false},
		                /* 11-Death */		{ datasort : 12 , class: "center"},
		                /* 12-SORT_DEAT */	{ visible : false},
		                /* 13-Age */		{ datasort : 14 , class: "center"},
		                /* 14-AGE */		{ type: "num", visible: false},
		                /* 15-DEAT_PLAC */	{ type: "unicode", class: "center" },
		                /* 16-DEAT_SOUR */	{ datasort : 17, class: "center", visible: '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
		                /* 17-SORT_DEATSC */{ visible : false},
		                /* 18-SEX */		{ visible : false},
		                /* 19-BIRT */		{ visible : false},
		                /* 20-DEAT */		{ visible : false},
		                /* 21-TREE */		{ visible : false}
		                /* END PERSO */
					],
		            sorting: [[0,"asc"]],
					displayLength: 16,
					pagingType: "full_numbers"
			   });
			 
				jQuery("#' . $table_id . '")
				/* Hide/show parents */
				.on("click", ".btn-toggle-parents", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
				})
				/* Hide/show statistics */
				.on("click", ".btn-toggle-statistics", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery("#indi_list_table-charts_' . $table_id . '").slideToggle();
				})
				/* Filter buttons in table header */
				.on("click", "button[data-filter-column]", function() {
					var btn = jQuery(this);
					// De-activate the other buttons in this button group
					btn.siblings().removeClass("ui-state-active");
					// Apply (or clear) this filter
					var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
					if (btn.hasClass("ui-state-active")) {
						btn.removeClass("ui-state-active");
						col.search("").draw();
					} else {
						btn.addClass("ui-state-active");
						col.search(btn.data("filter-value")).draw();
					}
				});
		
				jQuery("#sosa-indi-list").css("visibility", "visible");
			
				jQuery("#btn-toggle-statistics-'.$table_id.'").click();
			');
	
		$stats = new WT_Stats(WT_GEDCOM);
	
		// Bad data can cause "longest life" to be huge, blowing memory limits
		$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;
	
		//-- init chart data
		$deat_by_age = array();
		for ($age = 0; $age <= $max_age; $age++) {
			$deat_by_age[$age] = '';
		}
		for ($year=1550; $year<2030; $year+=10) {
			$birt_by_decade[$year] = '';
			$deat_by_decade[$year] = '';
		}
		//--table wrapper
		$html = '
			<div id="sosa-indi-list" class="sosa-list">
				<table id="'.$table_id.'">
					<thead>
						<tr>
							<th colspan="22">
								<div class="btn-toolbar">
									<div class="btn-group">
										<button
											class="ui-state-default"
											data-filter-column="18"
											data-filter-value="M"
											title="' . WT_I18N::translate('Show only males.') . '"
											type="button"
										>
										 	' . WT_Individual::sexImage('M', 'large') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="18"
											data-filter-value="F"
											title="' . WT_I18N::translate('Show only females.') . '"
											type="button"
										>
											' . WT_Individual::sexImage('F', 'large') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="18"
											data-filter-value="U"
											title="' . WT_I18N::translate('Show only individuals for whom the gender is not known.') . '"
											type="button"
										>
											' . WT_Individual::sexImage('U', 'large') . '
										</button>
									</div>
									<div class="btn-group">
										<button
											class="ui-state-default"
											data-filter-column="20"
											data-filter-value="N"
											title="' . WT_I18N::translate('Show individuals who are alive or couples where both partners are alive.').'"
											type="button"
										>
											' . WT_I18N::translate('Alive') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="20"
											data-filter-value="Y"
											title="' . WT_I18N::translate('Show individuals who are dead or couples where both partners are deceased.').'"
											type="button"
										>
											' . WT_I18N::translate('Dead') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="20"
											data-filter-value="YES"
											title="' . WT_I18N::translate('Show individuals who died more than 100 years ago.') . '"
											type="button"
										>
											' . WT_Gedcom_Tag::getLabel('DEAT') . '&gt;100
										</button>
										<button
											class="ui-state-default"
											data-filter-column="20"
											data-filter-value="Y100"
											title="' . WT_I18N::translate('Show individuals who died within the last 100 years.') . '"
											type="button"
										>
											' . WT_Gedcom_Tag::getLabel('DEAT') . '&lt;=100
										</button>
									</div>
									<div class="btn-group">
										<button
											class="ui-state-default"
											data-filter-column="19"
											data-filter-value="YES"
											title="' . WT_I18N::translate('Show individuals born more than 100 years ago.') . '"
											type="button"
										>
											' . WT_Gedcom_Tag::getLabel('BIRT') . '&gt;100
										</button>
										<button
											class="ui-state-default"
											data-filter-column="19"
											data-filter-value="Y100"
											title="' . WT_I18N::translate('Show individuals born within the last 100 years.') . '"
											type="button"
										>
											'.WT_Gedcom_Tag::getLabel('BIRT') . '&lt;=100
										</button>
									</div>
									<div class="btn-group">
										<button
											class="ui-state-default"
											data-filter-column="21"
											data-filter-value="R"
											title="' . WT_I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.') . '"
											type="button"
										>
											' . WT_I18N::translate('Roots') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="21"
											data-filter-value="L"
											title="' . WT_I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.') . '"
											type="button"
										>
											' . WT_I18N::translate('Leaves') . '
										</button>
									</div>
								</div>
							</th>
						</tr>
						<tr>
							<th>'.WT_I18N::translate('Sosa').'</th>
							<th>'.WT_Gedcom_Tag::getLabel('INDI').'</th>
							<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>
							<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>
							<th>GIVN</th>
							<th>SURN</th>
							<th>'.WT_Gedcom_Tag::getLabel('BIRT').'</th>
							<th>SORT_BIRT</th>
							<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= 		'<th><i class="icon-source" title="'.WT_I18N::translate('Sourced birth').'" /></th>
							<th>SORT_BIRTSC</th>';
		} else {
			$html .= 		'<th></th><th></th>';
		}
		//END PERSO
		$html .= 			'<th>'.WT_Gedcom_Tag::getLabel('DEAT').'</th>
							<th>SORT_DEAT</th>
							<th>'.WT_Gedcom_Tag::getLabel('AGE').'</th>
							<th>AGE</th>
							<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= 		'<th><i class="icon-source" title="'.WT_I18N::translate('Sourced death').'"></i></th>
							<th>SORT_DEATSC</th>';
		} else {
			$html .= 		'<th></th><th></th>';
		}
		//END PERSO
		$html .= 			'<th>SEX</th>
							<th>BIRT</th>
							<th>DEAT</th>
							<th>TREE</th>
						</tr>
					</thead>
				<tbody>';
		$nbDisplayed = 0;
		$d100y=new WT_Date(date('Y')-100);  // 100 years ago
		$dateY = date('Y');
		$unique_indis=array(); // Don't double-count indis with multiple names.
		foreach ($sosalist as $sosa=>$pid) {
			$person = WT_Individual::getInstance($pid);
			/* @var $person WT_Individual */
			if (!$person || !$person->canShowName()) {
				continue;
			}
			$dperson = new WT_Perso_Individual($person);
			if ($person->isPendingAddtion()) {
				$class = ' class="new"';
			} elseif ($person->isPendingDeletion()) {
				$class = ' class="old"';
			} else {
				$class = '';
			}
			$html .= '<tr' . $class . '>';
			//-- Indi Sosa
			$html .= '<td class="transparent">'.$sosa.'</td>';
			//-- Indi ID
			$html .= '<td class="transparent">'.$dperson->getXrefLink().'</td>';
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
			$html .= $person->getPrimaryParentsNames('parents details1', 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- GIVN/SURN
			// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
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
				// Estimated death dates are a fixed number of years after the birth date.
				// Don't show estimates in the future.
				if ($SHOW_EST_LIST_DATES && $death_date->MinJD() < WT_CLIENT_JD) {
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
			if (!$person->canShow() || WT_Date::Compare($birth_date, $d100y)>0) {
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
			$nbDisplayed++;
		}
		$html .= '</tbody>';
		//Prepare footer
		$nbSosa = count($sosalist);
		$thSosa = pow(2, $gen-1);
		$perc = WT_Perso_Functions::safeDivision($nbSosa, $thSosa);
		//PERSO Modify table to include IsSourced module
		$html .= '
				<tfoot>
					<tr>
						<th class="ui-state-default" colspan="22">
							<div class="center">
								'.WT_I18N::translate('Number of Sosa ancestors: %1$s known / %2$s theoretical (%3$s)',WT_I18N::number($nbSosa), WT_I18N::number($thSosa), WT_I18N::percentage($perc,2));
								//END PERSO
								if($nbDisplayed != $nbSosa) $html .= ' ['.WT_I18N::translate('%s hidden', WT_I18N::number($nbSosa - $nbDisplayed)).']';
		$html .= '			</div>
						</th>
					</tr>
					<tr>
						<th colspan="22">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button type="button" class="ui-state-default btn-toggle-parents">
										' . WT_I18N::translate('Show parents') . '
									</button>
									<button id="btn-toggle-statistics-'.$table_id.'" type="button" class="ui-state-default btn-toggle-statistics">
										' . WT_I18N::translate('Show statistics charts') . '
									</button>
								</div>
							</div>
						</th>
					</tr>
				</tfoot>
			</table>
			<div id="indi_list_table-charts_'. $table_id. '" style="display:none">
				<table class="list-charts center">
					<tr>
						<td>
							'.print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).'
						</td>
						<td>
							'.print_chart_by_decade($deat_by_decade, WT_I18N::translate('Decade of death')).'
						</td>
					</tr>
					<tr>
						<td colspan="2">
							'.print_chart_by_age($deat_by_age, WT_I18N::translate('Age related to death year')).'
						</td>
					</tr>
				</table>
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
		global $WT_TREE, $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;
		$table_id = 'table-sosa-fam-' . Uuid::uuid4(); // lists requires a unique ID in case there are multiple lists per page
		$SHOW_EST_LIST_DATES=$WT_TREE->getPreference('SHOW_EST_LIST_DATES');
		if (count($sosalist)<1)
			return '<p class="warning">'.WT_I18N::translate('No family has been found for generation %d', $gen).'</p>';
		$html = '';
		$controller
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript(
				'jQuery("#'.$table_id.'").dataTable( {
					dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
					'.WT_I18N::datatablesI18N(array(16, 32, 64, 128, -1)).',
					jQueryUI: true,
					autoWidth: false,
					processing: true,
					retrieve: true,
					columns: [
						/* 0-Sosa */  	   { dataSort: 1, class: "center"},
		                /* 1-SOSA */ 	   { type: "num", visible: false },
						/* 2-Husb Givn */  { dataSort: 4},
						/* 3-Husb Surn */  { dataSort: 5},
						/* 4-GIVN,SURN */  { type: "unicode", visible: false},
						/* 5-SURN,GIVN */  { type: "unicode", visible: false},
						/* 6-Husb Age  */  { dataSort: 7, class: "center"},
						/* 7-AGE       */  { type: "num", visible: false},
						/* 8-Wife Givn */  { dataSort: 10},
						/* 9-Wife Surn */  { dataSort: 11},
						/* 10-GIVN,SURN */ { type: "unicode", visible: false},
						/* 11-SURN,GIVN */ { type: "unicode", visible: false},
						/* 12-Wife Age  */ { dataSort: 13, class: "center"},
						/* 13-AGE       */ { type: "num", visible: false},
						/* 14-Marr Date */ { dataSort: 15, class: "center"},
						/* 15-MARR:DATE */ { visible: false},
						/* 16-Marr Plac */ { type: "unicode", class: "center"},
						/* 17-Marr Sour */ { dataSort : 18, class: "center", visible: '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
						/* 18-Sort Sour */ { visible: false},
						/* 19-Children  */ { dataSort: 20, class: "center"},
						/* 20-NCHI      */ { type: "num", visible: false},
						/* 21-MARR      */ { visible: false},
						/* 22-DEAT      */ { visible: false},
						/* 23-TREE      */ { visible: false}
						/* END PERSO */
					],
					sorting: [[0, "asc"]],
					displayLength: 16,
					pagingType: "full_numbers"
			   });
					
				jQuery("#' . $table_id . '")
				/* Hide/show parents */
				.on("click", ".btn-toggle-parents", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
				})
				/* Hide/show statistics */
				.on("click",  ".btn-toggle-statistics", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery("#fam_list_table-charts_' . $table_id . '").slideToggle();
				})
				/* Filter buttons in table header */
				.on("click", "button[data-filter-column]", function() {
					var btn = $(this);
					// De-activate the other buttons in this button group
					btn.siblings().removeClass("ui-state-active");
					// Apply (or clear) this filter
					var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
					if (btn.hasClass("ui-state-active")) {
						btn.removeClass("ui-state-active");
						col.search("").draw();
					} else {
						btn.addClass("ui-state-active");
						col.search(btn.data("filter-value")).draw();
					}
				});					
				
				jQuery("#sosa-fam-list").css("visibility", "visible");
				
				jQuery("#btn-toggle-statistics-'.$table_id.'").click();
			');
	
		$stats = new WT_Stats(WT_GEDCOM);
		
		// Bad data can cause "longest life" to be huge, blowing memory limits
		$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;
		$max_age_marr = max($stats->oldestMarriageMaleAge(), $stats->oldestMarriageFemaleAge())+1;
			
		//-- init chart data
		$marr_by_age = array();
		for ($age=0; $age<=$max_age; $age++) {
			$marr_by_age[$age] = '';
		}
		$birt_by_decade = array();
		$marr_by_decade = array();
		for ($year=1550; $year<2030; $year+=10) {
			$birt_by_decade[$year] = '';
			$marr_by_decade[$year] = '';
		}
		
		//--table wrapper
		$html .= '
			<div id="sosa-fam-list" class="sosa-list">
				<table id="'. $table_id. '">
					<thead>
						<tr>
							<th colspan="24">
								<div class="btn-toolbar">
									<div class="btn-group">
										<button
											type="button"
											data-filter-column="22"
											data-filter-value="N"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show individuals who are alive or couples where both partners are alive.').'"
										>
											' . WT_I18N::translate('Both alive').'
										</button>
										<button
											type="button"
											data-filter-column="22"
											data-filter-value="W"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples where only the female partner is deceased.').'"
										>
											' . WT_I18N::translate('Widower') . '
										</button>
										<button
											type="button"
											data-filter-column="22"
											data-filter-value="H"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples where only the male partner is deceased.').'"
										>
											' . WT_I18N::translate('Widow') . '
										</button>
										<button
											type="button"
											data-filter-column="22"
											data-filter-value="Y"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show individuals who are dead or couples where both partners are deceased.').'"
										>
											' . WT_I18N::translate('Both dead') . '
										</button>
									</div>
									<div class="btn-group">
										<button
											type="button"
											data-filter-column="23"
											data-filter-value="R"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.') . '"
										>
											' . WT_I18N::translate('Roots') . '
										</button>
										<button
											type="button"
											data-filter-column="23"
											data-filter-value="L"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'"
										>
											' . WT_I18N::translate('Leaves') . '
										</button>
									</div>
									<div class="btn-group">
										<button
											type="button"
											data-filter-column="21"
											data-filter-value="U"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples with an unknown marriage date.').'"
										>
											' . WT_Gedcom_Tag::getLabel('MARR').'
										</button>
										<button
											type="button"
											data-filter-column="21"
											data-filter-value="YES"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples who married more than 100 years ago.').'"
										>
											'.WT_Gedcom_Tag::getLabel('MARR') . '&gt;100
										</button>
										<button
											type="button"
											data-filter-column="21"
											data-filter-value="Y100"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples who married within the last 100 years.').'"
										>
											' . WT_Gedcom_Tag::getLabel('MARR') . '&lt;=100
										</button>
										<button
											type="button"
											data-filter-column="21"
											data-filter-value="D"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show divorced couples.').'"
										>
											' . WT_Gedcom_Tag::getLabel('DIV') . '
										</button>
										<button
											type="button"
											data-filter-column="21"
											data-filter-value="M"
											class="ui-state-default"
											title="' . WT_I18N::translate('Show couples where either partner married more than once.').'"
										>
											' . WT_I18N::translate('Multiple marriages') . '
										</button>
									</div>
								</div>
							</th>
						</tr>
						<tr>
							<th>'.WT_I18N::translate('Sosa').'</th>
							<th>SOSA</th>
							<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>
							<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>
							<th>HUSB:GIVN_SURN</th>
							<th>HUSB:SURN_GIVN</th>
							<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>
							<th>AGE</th>
							<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>
							<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>
							<th>WIFE:GIVN_SURN</th>
							<th>WIFE:SURN_GIVN</th>
							<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>
							<th>AGE</th>
							<th>'. WT_Gedcom_Tag::getLabel('MARR'). '</th>
							<th>MARR:DATE</th>
							<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html .= 		'<th><i class="icon-source" title="'.WT_I18N::translate('Sourced marriage').'" border="0"></i></th>
							<th>SORT_MARRSC</th>';
		} else {
			$html .= 		'<th>&nbsp;</th><th></th>';
		}
		//END PERSO
		$html .= 			'<th><i class="icon-children" title="'. WT_I18N::translate('Children'). '"></i></th>
							<th>NCHI</th>
							<th>MARR</th>
							<th>DEAT</th>
							<th>TREE</th>
						</tr>
					</thead>
				<tbody>';
		$d100y=new WT_Date(date('Y')-100);  // 100 years ago
		$dateY = date('Y');
		$unique_indis=array(); // Don't double-count indis with multiple names.
		foreach ($sosalist as $sosa=>$fid) {
			$sfamily = WT_Family::getInstance($fid);
			/* @var $sfamily WT_Family */
			if (is_null($sfamily)) continue;
			if (!$sfamily->canShowName()) continue;
			$dfamily = new WT_Perso_Family($sfamily);
			//END PERSO
			//-- Retrieve husband and wife
			$husb = $sfamily->getHusband();
			if (is_null($husb)) $husb = new WT_Individual('H', '0 @H@ INDI', null, WT_GED_ID);
			$wife = $sfamily->getWife();
			if (is_null($wife)) $wife = new WT_Individual('W', '0 @W@ INDI', null, WT_GED_ID);
			if (!$sfamily->canShow()) {
				continue;
			}
			if ($sfamily->isPendingAddtion()) {
				$class = ' class="new"';
			} elseif ($sfamily->isPendingDeletion()) {
				$class = ' class="old"';
			} else {
				$class = '';
			}
			$html .= '<tr' . $class . '>';
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
					$dhusb = new WT_Perso_Individual($husb);
					$html .= '<a '. $title. ' href="'. $sfamily->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dhusb->getSosaNumbers(), 1, 'smaller'). '<br>';
					//END PERSO
				}
			}
			// Husband parents
			$html .= $husb->getPrimaryParentsNames('parents details1', 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- Husb GIVN
			// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
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
					$dwife = new WT_Perso_Individual($wife);
					$html .= '<a '. $title. ' href="'. $sfamily->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dwife->getSosaNumbers(), 1, 'smaller'). '<br>';
					//END PERSO
				}
			}
			// Wife parents
			$html .= $wife->getPrimaryParentsNames('parents details1', 'none');
			$html .= '</td>';
			// Dummy column to match colspan in header
			$html .= '<td style="display:none;"></td>';
			//-- Wife GIVN
			//-- Husb GIVN
			// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
			// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
			// Similarly, @N.N. would sort as NN.
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
			$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
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
			} elseif ($sfamily->getFacts('_NMR')) {
				$html .= WT_I18N::translate('no');
			} elseif ($sfamily->getFacts('MARR')) {
				$html .= WT_I18N::translate('yes');
			} else {
				$html .= '&nbsp;';
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
			if (!$sfamily->canShow() || !$mdate->isOK()) {
				$html .= 'U';
			} else {
				if (WT_Date::Compare($mdate, $d100y)>0) {
					$html .= 'Y100';
				} else {
					$html .= 'YES';
				}
			}
			if ($sfamily->getFacts(WT_EVENTS_DIV)) {
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
		$html .= '
				</tbody>
				<tfoot>
					<tr>
						<th colspan="24">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button type="button" class="ui-state-default btn-toggle-parents">
										' . WT_I18N::translate('Show parents') . '
									</button>
									<button id="btn-toggle-statistics-'.$table_id.'" type="button" class="ui-state-default btn-toggle-statistics">
										' . WT_I18N::translate('Show statistics charts') . '
									</button>
								</div>
							</div>
						</th>
					</tr>
				</tfoot>
			</table>
			<div id="fam_list_table-charts_'. $table_id. '" style="display:none">
				<table class="list-charts center">
					<tr>
						<td>
							'.print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')).'
						</td>
						<td>
							'.print_chart_by_decade($marr_by_decade, WT_I18N::translate('Decade of marriage')).'
						</td>
					</tr>
					<tr>
						<td colspan="2">
							'.print_chart_by_age($marr_by_age, WT_I18N::translate('Age in year of marriage')).'
						</td>
					</tr>
				</table>
			</div>
		</div>'; // Close "sosa-fam-list"
	
		return $html;
	}
	
	
}

?>