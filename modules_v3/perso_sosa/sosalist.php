<?php
/**
 * Display the list of Sosa ancestors page
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

global $controller;

require_once WT_ROOT.'includes/functions/functions_places.php';

/**
* print a sortable table of sosa individuals
*
* @param array $sosalist contain Sosa individuals.
* @param int $gen Generation
* @param string $legend Optional legend
* @return string HTML code for the sosa table
*/
function format_sosa_table($sosalist, $gen, $legend='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if (count($sosalist)<1) return;
	$html = '';
	$controller
		->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
		->addInlineJavaScript('
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
	                /* 1-ID */ 			{ "sClass": "center" },
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
				'<button type="button" id="TREE_R_'   .$table_id.'" class="ui-state-default TREE_R" title="'.   WT_I18N::translate('Show «roots» couples or individuals.  These people may also be called «patriarchs».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
				'<button type="button" id="TREE_L_'.   $table_id.'" class="ui-state-default TREE_L" title="'.   WT_I18N::translate('Show «leaves» couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'">'.WT_I18N::translate('Leaves').'</button>'.
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
			
			jQuery(".sosa-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
			
			 /* PERSO Modify table to include IsSourced module */
			jQuery("#charts_indi_list_table").click();
			/* END PERSO */
		');

	$stats = new WT_Stats($GEDCOM);

	// Bad data can cause "longest life" to be huge, blowing memory limits
	$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;

	//-- init chart data
	for ($age=0; $age<=$max_age; $age++) $deat_by_age[$age]="";
	for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]="";
	for ($year=1550; $year<2030; $year+=10) $deat_by_decade[$year]="";
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="sosa-list">';
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
				$birt_by_decade[floor($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
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
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= get_place_short($birth_place);
			} else {
				$html .= '<a href="'. get_place_url($birth_place). '" title="'. $birth_place. '">';
				$html .= highlight_search_hits(get_place_short($birth_place)). '</a>';
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
				$deat_by_decade[floor($death_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
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
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= get_place_short($death_place);
			} else {
				$html .= '<a href="'. get_place_url($death_place). '" title="'. $death_place. '">';
				$html .= highlight_search_hits(get_place_short($death_place)). '</a>';
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
		if (!$person->canDisplayDetails() || WT_Date::Compare($birth_dates[0], $d100y)>0) {
			$html .= 'Y100';
		} else {
			$html .= 'YES';
		}
		$html .= '</td>';
		//-- Filtering by death date
		$html .= '<td>';
		if ($person->isDead()) {
			if (WT_Date::Compare($death_dates[0], $d100y)>0) {
				$html .= 'Y100';
			} else {
				$html .= 'YES';
			}
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
		<table class="list-charts"><tr><td>'.
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

$controller=new WT_Controller_Base();
$controller
	->setPageTitle(WT_I18N::translate('Sosa Ancestors'))
	->pageHeader();

echo '<div class="center"><h2>', WT_I18N::translate('Sosa Ancestors'), '</h2>';

$maxGen = WT_Perso_Functions_Sosa::getLastGeneration();

if($maxGen>0){
	$selectedgen = safe_REQUEST($_REQUEST, 'gen', WT_REGEX_INTEGER, null);
	
	echo '<form method="get" name="setgen" action="module.php">',
		'<input type="hidden" name="mod" value="perso_sosa">',
		'<input type="hidden" name="mod_action" value="sosalist">',
		'<table class="list_table">',
		'<td colspan="2" class="topbottombar center">',WT_I18N::translate('Choose generation'),'</td>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Generation'),'</td>',
		'<td class="optionbox vmiddle"><select name="gen">';
	for($i=1;$i<=$maxGen;$i++){
		echo  '<option value="',$i,'"';
		if($selectedgen && $selectedgen==$i) echo ' selected="true"';
		echo '>',WT_I18N::translate('Generation %d', $i), '</option>';
	}
	echo '</select></td></tr></table>',
		'<input type="submit" value="', WT_I18N::translate('Show'), '" /><br />',
		'</form>';

	if($selectedgen){
		echo '<h4>',
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen-1,'"><i class="icon-ldarrow" title="',WT_I18N::translate('Previous generation'),'" ></i>&nbsp;&nbsp;</a>',
			WT_I18N::translate('Generation %d', $selectedgen),
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen+1,'">&nbsp;&nbsp;<i class="icon-rdarrow" title="',WT_I18N::translate('Next generation'),'" ></i></a>',
			'</h4>';
		$listSosa=WT_Perso_Functions_Sosa::getSosaListAtGeneration($selectedgen);
		if($listSosa){
			echo format_sosa_table($listSosa, $selectedgen);
		}
		else{
			echo '<p class="warning">'.WT_I18N::translate('No individuals has been found for generation %d', $selectedgen).'</p>';
		}			
	}

}
else {
	echo '<p class="warning">'.WT_I18N::translate('The list could not be displayed. Reasons might be:').'<br/><ul><li>'.
		WT_I18N::translate('No Sosa root individual has been defined.').'</li><li>'.
		WT_I18N::translate('The Sosa ancestors have not been computed yet.').'</li></p>';
}

echo '</div>';
?>