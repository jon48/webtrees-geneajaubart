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
require_once WT_ROOT.'includes/functions/functions_print_lists.php';

global $WT_IMAGES;

/**
* print a sortable table of missing sosa individuals
*
* @param array $sosalist contain Sosa individuals.
* @param int $gen Generation
* @param string $legend Optional legend
*/
function print_missing_table($sosalistG, $sosalistG1, $gen, $legend='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $TEXT_DIRECTION, $WT_IMAGES, $SEARCH_SPIDER;
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if (count($sosalistG)<1) return;
	
	$sumMissingDifferent = 0;
	$sumMissingDifferentWithoutHidden = 0;
	$areMissing = false;$n = 0;
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
		/* @var $person Person */
		$person = WT_Person::getInstance($pid);
		if (is_null($person)) continue;
		if ($person->getType() !== 'INDI') continue;
		if (!$person->canDisplayName()) continue;
		$dperson = new WT_Perso_Person($person);
		$sumMissingDifferentWithoutHidden += $miss['father'] + $miss['mother'];
		$html .= '<tr>';
		//-- Indi Sosa
		$html .= '<td class="transparent">'.$sosa.'</td>';
		//-- Indi ID
		$html .=  '<td class="transparent">'.$person->getXrefLink().'</td>';
		//-- Indi name(s)
		$tdclass = '';
		if (!$person->isDead()) $tdclass .= ' alive';
		if (!$person->getChildFamilies()) $tdclass .= ' patriarch';
		$html .= '<td class="'.$tdclass.'" align="'.get_align($person->getFullName()).'">';
		list($surn, $givn)=explode(',', $person->getSortName());
		// If we're showing search results, then the highlighted name is not
		// necessarily the person's primary name.
		$primary=$person->getPrimaryName();
		$names=$person->getAllNames();
		foreach ($names as $num=>$name) {
			// Exclude duplicate names, which can occur when individuals have
			// multiple surnames, such as in Spain/Portugal
			$dupe_found=false;
			foreach ($names as $dupe_num=>$dupe_name) {
				if ($dupe_num>$num && $dupe_name['type']==$name['type'] && $dupe_name['full']==$name['full']) {
					// Take care not to skip the "primary" name
					if ($num==$primary) {
						$primary=$dupe_num;
					}
					$dupe_found=true;
					break;
				}
			}
			if ($dupe_found) {
				continue;
			}
			if ($name['type']!='NAME') {
				$title='title="'.WT_Gedcom_Tag::getLabel($name['type'], $person).'"';
			} else {
				$title='';
			}
			if ($num==$primary) {
				$class='list_item name2';
				$sex_image=$person->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='list_item';
				$sex_image='';
			}
			//PERSO Add Sosa Image
			$html .=  '<a '.$title.' href="'.$person->getHtmlUrl().'" class="'.$class.'">'.highlight_search_hits($name['full']).'</a>'.$sex_image.WT_Perso_Functions_Print::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller')."<br/>";
			//END PERSO
		}
		// Indi parents
		$html .=  $person->getPrimaryParentsNames("parents_sosa_list_table_".$table_id." details1", 'none');
		$html .=  '</td>';
		//-- GIVN/SURN
		$html .=  '<td>'.htmlspecialchars($givn).','.htmlspecialchars($surn).'</td>';
		$html .=  '<td>'.htmlspecialchars($surn).','.htmlspecialchars($givn).'</td>';
		//-- Father missing
		$html .=	'<td>';
		$html .=	$miss['father'] ? 'X' : '&nbsp;';
		$html .=	'</td>';
		//-- Mother missing
		$html .=	'<td>';
		$html .=	$miss['mother'] ? 'X' : '&nbsp;';
		$html .=	'</td>';
		//-- Birth date
		$html .=  '<td>';
		if ($birth_dates=$person->getAllBirthDates()) {
			foreach ($birth_dates as $num=>$birth_date) {
				if ($num) {
					$html .=  '<div>'.$birth_date->Display(!$SEARCH_SPIDER).'</div>';
				} else {
					$html .=  '<div>'.str_replace('<a', '<a name="'.$birth_date->MinJD().'"', $birth_date->Display(!$SEARCH_SPIDER)).'</div>';
				}
			}
		} else {
			$birth_date=$person->getEstimatedBirthDate();
			$birth_jd=$birth_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				$html .=  '<div>'.str_replace('<a', '<a name="'.$birth_jd.'"', $birth_date->Display(!$SEARCH_SPIDER)).'</div>';
			} else {
				$html .=  '<span class="date"><a name="'.$birth_jd.'"/>&nbsp;</span>'; // span needed for alive-in-year filter
			}
		}
		$html .=  '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .=  '<td>'.$birth_date->JD().'</td>';
		//-- Birth place
		$html .=  '<td>';
		$birth_place = '';
		if (list($birth_place, $corr)=$dperson->getEstimatedBirthPlace(true)) {
			if ($SEARCH_SPIDER) {
				$html .=  get_place_short($birth_place).' ';
			} else {
				$html .=  '<div align="'.get_align($birth_place).'">';
				$html .=  '<a href="'.get_place_url($birth_place).'" class="list_item" title="'.$birth_place.'">';
				$html .=  highlight_search_hits(get_place_short($birth_place));
				if($corr < 1) $html .=  ' '.WT_I18N::translate('(%.0f %%)',$corr * 100);
				$html .=  '</a>';
				$html .=  '</div>';
			}
		} else {
			$html .=  '&nbsp;';
		}
		$html .=  '</td>';
		//-- Birth place (sortable)hidden by datatables code
		$html .=  '<td>'.$birth_place.'</td>';
		//-- Sorting by gender
		$html .= '<td>'. $person->getSex(). '</td>';
		$html .= '</tr>'."\n";
		$unique_indis[$person->getXref()]=true;
		++$n;
	}
	
	$percSosa = WT_Perso_Functions::getPercentage(count($sosalistG1), pow(2, $gen-1));
	if($areMissing){	
		echo WT_JS_START;?>
		var oTable<?php echo $table_id; ?>;
		jQuery(document).ready(function(){
			/* Initialise datatables */
			oTable<?php echo $table_id; ?> = jQuery('#<?php echo $table_id; ?>').dataTable( {
				"sDom": '<"H"<"filtersH"><"dt-clear">pf<"dt-clear">irl>t<"F"pl>',
				"oLanguage": {
					"sLengthMenu": '<?php echo /* I18N: Display %s [records per page], %s is a placeholder for listbox containing numeric options */ WT_I18N::translate('Display %s', '<select><option value="10">10<option value="20">20</option><option value="30">30</option><option value="50">50</option><option value="100">100</option><option value="-1">'.WT_I18N::translate('All').'</option></select>'); ?>',
					"sZeroRecords": '<?php echo WT_I18N::translate('No records to display');?>',
					"sInfo": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '_START_', '_END_', '_TOTAL_'); ?>',
					"sInfoEmpty": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '0', '0', '0'); ?>',
					"sInfoFiltered": '<?php echo /* I18N: %s is a placeholder for a number */ WT_I18N::translate('(filtered from %s total entries)', '_MAX_'); ?>',
					"sProcessing": '<?php echo WT_I18N::translate('Loading...');?>',
					"sSearch": '<?php echo WT_I18N::translate('Filter');?>',				"oPaginate": {
						"sFirst":    '<?php echo /* I18N: button label, first page    */ WT_I18N::translate('first');    ?>',
						"sLast":     '<?php echo /* I18N: button label, last page     */ WT_I18N::translate('last');     ?>',
						"sNext":     '<?php echo /* I18N: button label, next page     */ WT_I18N::translate('next');     ?>',
						"sPrevious": '<?php echo /* I18N: button label, previous page */ WT_I18N::translate('previous'); ?>'
					}
				},
				"bJQueryUI": true,
				"bAutoWidth":false,
				"bProcessing": true,
				"bRetrieve": true,
				"bStateSave": true,
				"aoColumns": [
					/* 0-Sosa */  		{ "sType": "numeric", "sClass": "center" },
	                /* 1-ID */ 			null,
	                /* 2-Name */ 		{ "iDataSort" : 4 },
	                /* 3-GVN */     	{ "bVisible" : false},
	                /* 4-SURN */		{ "bVisible" : false},
	                /* 5-Father */		{ "sClass": "center"},
	                /* 6-Mother */		{ "sClass": "center"},
	                /* 7-Birth */		{ "iDataSort" : 8 , "sClass": "center"},
	                /* 8-SORT_BIRT */	{ "bVisible" : false},
	                /* 9-BIRT_PLAC */	{ "iDataSort" : 10 },
	                /* 10-SORT_BIRTPL */{ "bVisible" : false},
	                /* 11-SEX */		{ "bVisible" : false}
				],			
	            "aaSorting": [[0,'asc']],
				"iDisplayLength": 20,
				"sPaginationType": "full_numbers"
		   });
		   
			jQuery("div.filtersH").html('<?php echo addcslashes(
				'<button type="button" id="SEX_M_'.$table_id.'" class="ui-state-default SEX_M" title="'.WT_I18N::translate('Show only males.').'">'.WT_Person::sexImage('M', 'small').'</button>'.
				'<button type="button" id="SEX_F_'.$table_id.'" class="ui-state-default SEX_F" title="'.WT_I18N::translate('Show only females.').'">'.WT_Person::sexImage('F', 'small').'</button>'.
				'<button type="button" id="SEX_U_'.$table_id.'" class="ui-state-default SEX_U" title="'.WT_I18N::translate('Show only persons of whom the gender is not known.').'">'.WT_Person::sexImage('U', 'small').'</button>'.
				'<button type="button" id="RESET_'.$table_id.'" class="ui-state-default RESET" title="'.WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>',
				"'");
			?>');
	
		   /* Add event listeners for filtering inputs */
			jQuery('#SEX_M_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'M', 11 );});
			jQuery('#SEX_F_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'F', 11 );});
			jQuery('#SEX_U_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'U', 11 );});		
			jQuery('#RESET_<?php echo $table_id; ?>').click( function() {
				for(i = 0; i < 12; i++){oTable<?php echo $table_id; ?>.fnFilter( '', i );};
			});
							
			jQuery(".smissing-list").css('visibility', 'visible');
			jQuery(".loading-image").css('display', 'none');
		});
		<?php echo WT_JS_END;
		
		//--table wrapper
		echo '<div class="loading-image">&nbsp;</div>';
		echo '<div class="smissing-list">';
		//-- fieldset
		if ($legend == '') {
			$indi_root = WT_Person::getInstance(get_gedcom_setting(WT_GED_ID, 'PERSO_PS_ROOT_INDI'));
			if($indi_root) $legend = WT_I18N::translate('%s\'s missing ancestors', $indi_root->getFullName());
		}
		if (isset($WT_IMAGES['smissing-list'])) $legend = '<img src="'.$WT_IMAGES['smissing-list'].'" alt="" align="middle" /> '.$legend;
		echo '<fieldset id="fieldset_indi"><legend>', $legend, '</legend>';
		//-- table header
		echo '<table id="', $table_id, '"><thead><tr>';
		echo '<th>', WT_I18N::translate('Sosa'), '</th>';
		echo '<th>', WT_Gedcom_Tag::getLabel('INDI'), '</th>';	
		echo '<th>', WT_Gedcom_Tag::getLabel('NAME'), '</th>';
		echo '<th>GIVN</th>';
		echo '<th>SURN</th>';
		echo '<th>',  WT_I18N::translate('Father'), '</th>';
		echo '<th>',  WT_I18N::translate('Mother'), '</th>';
		echo '<th>', WT_Gedcom_Tag::getLabel('BIRT'), '</th>';
		echo '<th>SORT_BIRT</th>';
		echo '<th>', WT_Gedcom_Tag::getLabel('PLAC'), '</th>';
		echo '<th>BIRT_PLAC_SORT</th>';
		echo '<th>SEX</th>';
		echo '</tr></thead>';
		//-- table body
		echo '<tbody>';
		echo $html;
		echo '</tbody>';
		
		//Prepare footer
		echo '<tfoot>',
				'<tr><td class="ui-state-default" colspan="12">'.WT_I18N::translate('Number of different missing ancestors: %d',$sumMissingDifferent);
		if($sumMissingDifferent != $sumMissingDifferentWithoutHidden) echo ' ['.WT_I18N::translate('%d hidden', $sumMissingDifferent - $sumMissingDifferentWithoutHidden).']';
		$percPotentialSosa = WT_Perso_Functions::getPercentage(count($sosalistG), pow(2, $gen-2));
		echo ' - '.WT_I18N::translate('Generation complete at %.2f %%', $percSosa);
		echo ' ['.WT_I18N::translate('Potential %.2f %%', $percPotentialSosa).']';
		echo '</td></tr></tfoot>';
		echo '</table>';
		echo '</div>'; // Close "smissing-list"
		echo "</fieldset>";
	}
	else{
		echo WT_I18N::translate('No ancestors are missing for this generation. Generation complete at %.2f %%.', $percSosa);
	}		

}

print_header(WT_I18N::translate('Missing Ancestors'));

echo '<div class="center"><h2>', WT_I18N::translate('Missing Ancestors'), '</h2>';

$maxGen = WT_Perso_Functions_Sosa::getLastGeneration();

if($maxGen>0){
	$selectedgen = safe_REQUEST($_REQUEST, 'gen', WT_REGEX_INTEGER, null);
	
	echo '<form method="post" name="setgen" action="module.php?mod=perso_sosa&mod_action=missingancestors">',
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
	
	if($selectedgen){
		$mingen = $selectedgen;
		$maxgen = $selectedgen;
		if($selectedgen == 1){
			$mingen = 2;
			$maxgen = $maxGen;
		}
		for ($gen = $mingen; $gen <= $maxgen; $gen++) {
			echo '<h4>';
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen-1).'"><img src="'.$WT_IMAGES['ldarrow'].'" title="'.WT_I18N::translate('Previous generation').'" alt="'.WT_I18N::translate('Previous generation').'" />&nbsp;&nbsp;</a>';
			echo WT_I18N::translate('Generation %d', $gen);
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen+1).'">&nbsp;&nbsp;<img src="'.$WT_IMAGES['rdarrow'].'" title="'.WT_I18N::translate('Next generation').'" alt="'.WT_I18N::translate('Next generation').'" /></a>';
			echo '</h4>';
			$listGenG=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen-1);
			$listGenG1=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen);
			if($listGenG){
				print_missing_table($listGenG, $listGenG1, $gen);
			}
			else{
				echo '<p class="warning">'.WT_I18N::translate('No individuals has been found for generation %d', $gen - 1).'</p>';
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

print_footer();

?>