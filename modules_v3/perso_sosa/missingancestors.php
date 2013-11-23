<?php
/**
 * Display missing ancestors page.
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

/**
* print a sortable table of missing sosa individuals
*
* @param array $sosalist contain Sosa individuals.
* @param int $gen Generation
* @param string $legend Optional legend
* @return string HTML code for the missing ancestors table
*/
function format_missing_table($sosalistG, $sosalistG1, $gen, $legend='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $controller;
	$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if (count($sosalistG)<1) return;
	
	$sumMissingDifferent = 0;
	$sumMissingDifferentWithoutHidden = 0;
	$areMissing = false;
	$n = 0;
	$unique_indis=array(); // Don't double-count indis with multiple names.
	$html = '';
	foreach($sosalistG as $sosa=>$pid){
		$miss = array('father' => false, 'mother' => false);
		if(!isset($sosalistG1[2*$sosa])) $miss['father'] = true;
		if(!isset($sosalistG1[2*$sosa + 1])) $miss['mother'] = true;
		if(!$miss['father'] && !$miss['mother']) continue;
		$areMissing = true;
		if(isset($unique_indis[$pid])) continue;
		$sumMissingDifferent += $miss['father'] + $miss['mother'];
		/* @var $person WT_Individual */
		$person = WT_Individual::getInstance($pid);
		if (is_null($person)) continue;
		if (!$person->canShowName()) continue;
		$dperson = new WT_Perso_Individual($person);
		$sumMissingDifferentWithoutHidden += $miss['father'] + $miss['mother'];
		if ($person->isNew()) {
			$class = ' class="new"';
		} elseif ($person->isOld()) {
			$class = ' class="old"';
		} else {
			$class = '';
		}
		$html .= '<tr' . $class . '>';
		//-- Indi Sosa
		$html .= '<td class="transparent">'.$sosa.'</td>';
		//-- Indi ID
		$html .=  '<td class="transparent">'.$dperson->getXrefLink().'</td>';
		//-- Indi name(s)
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
			$html .= '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller'). '<br/>';
			//END PERSO
		}// Indi parents
		$html .= $person->getPrimaryParentsNames('parents_indi_list_table_'.$table_id.' details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- GIVN/SURN
		// Use "AAAA" as a separator (instead of ",") as JavaScript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$isSourced = $dperson->isSourced();
			$html .= '<td>'.WT_Perso_Functions_Print::formatIsSourcedIcon('R', $isSourced, 'INDI', 1, 'medium').'</td>'.
								'<td>'.$isSourced.'</td>';
		} else {
			$html .= '<td></td>'.
								'<td></td>';
		}
		//END PERSO
		//-- Father missing
		$html .=	'<td>';
		$html .=	$miss['father'] ? 'X' : '&nbsp;';
		$html .=	'</td>';
		//-- Mother missing
		$html .=	'<td>';
		$html .=	$miss['mother'] ? 'X' : '&nbsp;';
		$html .=	'</td>';
		//-- Birth date
		$html .= '<td>';
		if ($birth_dates=$person->getAllBirthDates()) {
			foreach ($birth_dates as $num=>$birth_date) {
				if ($num) {
					$html .= '<br/>';
				}
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			}
		} else {
			$birth_date=$person->getEstimatedBirthDate();
			$birth_jd=$birth_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			} else {
				$html .= '&nbsp;';
			}
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
		//-- Sorting by gender
		$html .= '<td>';
		$html .= $person->getSex();
		$html .= '</td>';
		++$n;
	}
	
	$html2 = '';
	
	$percSosa = WT_Perso_Functions::getPercentage(count($sosalistG1), pow(2, $gen-1));
	if($areMissing){
		$controller
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				/* Initialise datatables */
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
				oTable'.$table_id.' = jQuery("#'.$table_id.'").dataTable( {
					"sDom": \'<"H"<"filtersH_'.$table_id.'"><"dt-clear">pf<"dt-clear">irl>t<"F"pl>\',
					'.WT_I18N::datatablesI18N().',
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
		                /* PERSO Modify table to include IsSourced module */
		                /* 6-INDI_SOUR */	{ "iDataSort" : 7, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
	                	/* 7-SORT_INDISC */	{ "bVisible" : false},
		                /* 8-Father */		{ "sClass": "center"},
		                /* 9-Mother */		{ "sClass": "center"},
		                /* 10-Birth */		{ "iDataSort" : 11 , "sClass": "center"},
		                /* 11-SORT_BIRT */	{ "bVisible" : false},
		                /* 12-BIRT_PLAC */	{ "sType": "unicode", "sClass": "center"},
		                /* 13-BIRT_SOUR */	{ "iDataSort" : 14, "sClass": "center", "bVisible": '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
	                	/* 14-SORT_BIRTSC */{ "bVisible" : false},
		                /* 15-SEX */		{ "bVisible" : false}
		                /* END PERSO */
					],			
		            "aaSorting": [[0,"asc"]],
					"iDisplayLength": 20,
					"sPaginationType": "full_numbers"
			   });
			   
				jQuery("div.filtersH_'.$table_id.'").html("'.WT_Filter::escapeJs(
					'<button type="button" id="SEX_M_'.    $table_id.'" class="ui-state-default SEX_M" title="'.    WT_I18N::translate('Show only males.').'">&nbsp;'.WT_Individual::sexImage('M', 'small').'&nbsp;</button>'.
					'<button type="button" id="SEX_F_'.    $table_id.'" class="ui-state-default SEX_F" title="'.    WT_I18N::translate('Show only females.').'">&nbsp;'.WT_Individual::sexImage('F', 'small').'&nbsp;</button>'.
					'<button type="button" id="SEX_U_'.    $table_id.'" class="ui-state-default SEX_U" title="'.    WT_I18N::translate('Show only individuals of whom the gender is not known.').'">&nbsp;'.WT_Individual::sexImage('U', 'small').'&nbsp;</button>'.
					'<button type="button" id="RESET_'.    $table_id.'" class="ui-state-default RESET" title="'.    WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>'
				).'");
		
			   /* Add event listeners for filtering inputs */
			   /* PERSO Modify table to include IsSourced module */
				jQuery("#SEX_M_'.$table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("M", 15 );
					jQuery("#SEX_M_'.$table_id.'").addClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#SEX_F_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("F", 15 );
					jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").addClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").removeClass("ui-state-active");
				});
				jQuery("#SEX_U_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("U", 15 );
					jQuery("#SEX_M_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_F_'.$table_id.'").removeClass("ui-state-active");
					jQuery("#SEX_U_'.$table_id.'").addClass("ui-state-active");
				});			
				jQuery("#RESET_'.    $table_id.'").click( function() {
					oTable'.$table_id.'.fnFilter("", 15 );
					jQuery("div.filtersH_'.$table_id.' button").removeClass("ui-state-active");
				});
				/* END PERSO */									
				
				/* This code is a temporary fix for Datatables bug http://www.datatables.net/forums/discussion/4730/datatables_sort_wrapper-being-added-to-columns-with-bsortable-false/p1*/
				jQuery("th div span:eq(5)").css("display", "none");
				jQuery("th div:eq(5)").css("margin", "auto").css("text-align", "center");
				
				jQuery(".smissing-list").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
		
		//--table wrapper
		$html2 .= '<div class="loading-image">&nbsp;</div>';
		$html2 .= '<div class="smissing-list">';
		//-- table header
		$html2 .= '<table id="'.$table_id.'"><thead><tr>';
		$html2 .= '<th>'.WT_I18N::translate('Sosa').'</th>';
		$html2 .= '<th>'.WT_Gedcom_Tag::getLabel('INDI').'</th>';	
		$html2 .= '<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>';
		$html2 .= '<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>';
		$html2 .= '<th>GIVN</th>';
		$html2 .= '<th>SURN</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html2 .= '<th><i class="icon-source" title="'.WT_I18N::translate('Sourced individual').'"></i></th>'.
				'<th>SORT_INDISC</th>';
		} else {
			$html2 .= '<th></th><th></th>';
		}
		//END PERSO
		$html2 .= '<th>'.WT_I18N::translate('Father').'</th>';
		$html2 .= '<th>'.WT_I18N::translate('Mother').'</th>';
		$html2 .= '<th>'.WT_Gedcom_Tag::getLabel('BIRT').'</th>';
		$html2 .= '<th>SORT_BIRT</th>';
		$html2 .= '<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html2 .= '<th><i class="icon-source" title="'.WT_I18N::translate('Sourced birth').'"></i></th>'.
				'<th>SORT_BIRTSC</th>';
		} else {
			$html2 .= '<th></th><th></th>';
		}
		//END PERSO
		$html2 .= '<th>SEX</th>';
		$html2 .= '</tr></thead>';
		//-- table body
		$html2 .= '<tbody>';
		$html2 .= $html;
		$html2 .= '</tbody>';
		
		//Prepare footer
		//PERSO Modify table to include IsSourced module
		$html2 .= '<tfoot>'.
				'<tr><td class="ui-state-default" colspan="16">'.WT_I18N::translate('Number of different missing ancestors: %d',$sumMissingDifferent);
		//END PERSO
		if($sumMissingDifferent != $sumMissingDifferentWithoutHidden) $html2 .= ' ['.WT_I18N::translate('%d hidden', $sumMissingDifferent - $sumMissingDifferentWithoutHidden).']';
		$percPotentialSosa = WT_Perso_Functions::getPercentage(count($sosalistG), pow(2, $gen-2));
		$html2 .= ' - '.WT_I18N::translate('Generation complete at %.2f %%', $percSosa);
		$html2 .= ' ['.WT_I18N::translate('Potential %.2f %%', $percPotentialSosa).']';
		$html2 .= '</td></tr></tfoot>';
		$html2 .= '</table>';
		$html2 .= '</div>'; // Close "smissing-list"
	}
	else{
		$html2 .=  WT_I18N::translate('No ancestors are missing for this generation. Generation complete at %.2f %%.', $percSosa);
	}		
	
	return $html2;

}

$controller=new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate('Missing Ancestors'))
	->pageHeader();

echo '<div class="psosa-missing-page center">',
	'<h2>', $controller->getPageTitle(), '</h2>';

$maxGen = WT_Perso_Functions_Sosa::getLastGeneration();

if($maxGen>0){
	$selectedgen = WT_Filter::post('gen', WT_REGEX_INTEGER, WT_Filter::getInteger('gen'));
	
	echo '<form method="get" name="setgen" action="module.php">',
		'<input type="hidden" name="mod" value="perso_sosa">',
		'<input type="hidden" name="mod_action" value="missingancestors">',
		'<table class="list_table">',
		'<td colspan="2" class="topbottombar center">',WT_I18N::translate('Choose generation'),'</td>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Generation'),'</td>',
		'<td class="optionbox vmiddle"><select name="gen">',
		'<option value="1">',WT_I18N::translate('All'),'</option>';
	for($i=2;$i<=$maxGen;$i++){
		echo  '<option value="',$i,'"';
		if($selectedgen && $selectedgen==$i) echo ' selected="true"';
		echo '>',WT_I18N::translate('Generation %d', $i), '</option>';
	}
	echo '</select></td></tr></table>',
		'<input type="submit" value="', WT_I18N::translate('Show'), '" /><br />',
		'</form>';
	
	if($selectedgen > 0){
		$mingen = $selectedgen;
		$maxgen = $selectedgen;
		if($selectedgen == 1){
			$mingen = 2;
			$maxgen = $maxGen;
		}
		for ($gen = $mingen; $gen <= $maxgen; $gen++) {
			echo '<h4>';
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen-1).'"><i class="icon-ldarrow" title="',WT_I18N::translate('Previous generation'),'" ></i>&nbsp;&nbsp;</a>';
			echo WT_I18N::translate('Generation %d', $gen);
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen+1).'">&nbsp;&nbsp;<i class="icon-rdarrow" title="',WT_I18N::translate('Next generation'),'" ></i></a>';
			echo '</h4>';
			$listGenG=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen-1);
			$listGenG1=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen);
			if($listGenG){
				echo format_missing_table($listGenG, $listGenG1, $gen);
			}
			else{
				echo '<p class="warning">'.WT_I18N::translate('No individual has been found for generation %d', $gen - 1).'</p>';
			}
			
		}
		
	}
}
else{
	echo '<p class="warning">'.WT_I18N::translate('The list could not be displayed. Reasons might be:').'<br/><ul><li>'.
			WT_I18N::translate('No Sosa root individual has been defined.').'</li><li>'.
			WT_I18N::translate('The Sosa ancestors have not been computed yet.').'</li><li>'.
			WT_I18N::translate('No generation were found.').'</li></p>';
}

echo '</div>';

?>