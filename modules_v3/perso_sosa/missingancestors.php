<?php
/**
 * Display missing ancestors page.
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
	global $WT_TREE, $GEDCOM, $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $controller;
	$table_id = 'table-sosa-missing-' . Uuid::uuid4(); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=$WT_TREE->getPreference('SHOW_EST_LIST_DATES');
	if (count($sosalistG)<1) return;
	
	$sumMissingDifferent = 0;
	$sumMissingDifferentWithoutHidden = 0;
	$areMissing = false;
	$nbDisplayed = 0;
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
		if (!$person || !$person->canShowName()) {
			continue;
		}
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
		++$nbDisplayed;
	}
	
	$percSosa = WT_Perso_Functions::safeDivision(count($sosalistG1), pow(2, $gen-1));
	if($areMissing){
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
					'.WT_I18N::datatablesI18N().',
					jQueryUI: true,
					autoWidth:false,
					processing: true,
					retrieve: true,
					columns: [
						/* 0-Sosa */  		{ type: "num", class: "center" },
		                /* 1-ID */ 			{ class: "center" },
		                /* 2-givn */ 		{ dataSort: 4,  class: "left"},
						/* 3-surn */ 		{ dataSort: 5},
						/* 4-GIVN,SURN */ 	{ type: "unicode", visible: false},
						/* 5-SURN,GIVN */ 	{ type: "unicode", visible: false},
		                /* PERSO Modify table to include IsSourced module */
		                /* 6-INDI_SOUR */	{ dataSort : 7, class: "center", visible: '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
	                	/* 7-SORT_INDISC */	{ visible : false},
		                /* 8-Father */		{ class: "center"},
		                /* 9-Mother */		{ class: "center"},
		                /* 10-Birth */		{ dataSort : 11 , class: "center"},
		                /* 11-SORT_BIRT */	{ visible : false},
		                /* 12-BIRT_PLAC */	{ type: "unicode", class: "center"},
		                /* 13-BIRT_SOUR */	{ dataSort : 14, class: "center", visible: '.(WT_Perso_Functions::isIsSourcedModuleOperational() ? 'true' : 'false').' },
	                	/* 14-SORT_BIRTSC */{ visible : false},
		                /* 15-SEX */		{ visible : false}
		                /* END PERSO */
					],			
		            sorting: [[0,"asc"]],
					displayLength: 20,
					pagingType: "full_numbers"
			   });
					
				jQuery("#' . $table_id . '")
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
			   		
				jQuery(".smissing-list").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
				
		$html2 = '
			<div class="loading-image">&nbsp;</div>
			<div class="smissing-list">
				<table id="'.$table_id.'">
					<thead>
						<tr>
							<th colspan="16">
								<div class="btn-toolbar">
									<div class="btn-group">
										<button
											class="ui-state-default"
											data-filter-column="15"
											data-filter-value="M"
											title="' . WT_I18N::translate('Show only males.') . '"
											type="button"
										>
										 	' . WT_Individual::sexImage('M', 'large') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="15"
											data-filter-value="F"
											title="' . WT_I18N::translate('Show only females.') . '"
											type="button"
										>
											' . WT_Individual::sexImage('F', 'large') . '
										</button>
										<button
											class="ui-state-default"
											data-filter-column="15"
											data-filter-value="U"
											title="' . WT_I18N::translate('Show only individuals for whom the gender is not known.') . '"
											type="button"
										>
											' . WT_Individual::sexImage('U', 'large') . '
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
							<th>SURN</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html2 .= 		'<th><i class="icon-source" title="'.WT_I18N::translate('Sourced individual').'"></i></th>
							<th>SORT_INDISC</th>';
		} else {
			$html2 .= 		'<th></th><th></th>';
		}
		//END PERSO
		$html2 .= 			'<th>'.WT_I18N::translate('Father').'</th>
							<th>'.WT_I18N::translate('Mother').'</th>
							<th>'.WT_Gedcom_Tag::getLabel('BIRT').'</th>
							<th>SORT_BIRT</th>
							<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		//PERSO Modify table to include IsSourced module
		if (WT_Perso_Functions::isIsSourcedModuleOperational()) {
			$html2 .= 		'<th><i class="icon-source" title="'.WT_I18N::translate('Sourced birth').'"></i></th>
							<th>SORT_BIRTSC</th>';
		} else {
			$html2 .= 		'<th></th><th></th>';
		}
		//END PERSO
		$html2 .= 			'<th>SEX</th>
						</tr>
					</thead>
					<tbody>
						'.$html.'
					</tbody>
					<tfoot>
						<tr>
							<td class="ui-state-default" colspan="16">
								<div class="center">
									'.WT_I18N::translate('Number of different missing ancestors: %s',WT_I18N::number($sumMissingDifferent));
		//END PERSO
		if($sumMissingDifferent != $sumMissingDifferentWithoutHidden) $html2 .= ' ['.WT_I18N::translate('%s hidden', WT_I18N::number($sumMissingDifferent - $sumMissingDifferentWithoutHidden)).']';
		$percPotentialSosa = WT_Perso_Functions::safeDivision(count($sosalistG), pow(2, $gen-2));
		$html2 .= ' - '.WT_I18N::translate('Generation complete at %s', WT_I18N::percentage($percSosa, 2));
		$html2 .= ' ['.WT_I18N::translate('Potential %s', WT_I18N::percentage($percPotentialSosa,2)).']
							</td>
						</tr>
					</tfoot>
				</table>
			</div>';
	}
	else{
		$html2 =  WT_I18N::translate('No ancestors are missing for this generation. Generation complete at %s.', WT_I18N::percentage($percSosa, 2));
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