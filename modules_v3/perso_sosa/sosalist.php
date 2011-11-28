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

global $WT_IMAGES;

/**
* print a sortable table of sosa individuals
*
* @param array $sosalist contain Sosa individuals.
* @param int $gen Generation
* @param string $legend Optional legend
*/
function print_sosa_table($sosalist, $gen, $legend='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $TEXT_DIRECTION, $WT_IMAGES, $SEARCH_SPIDER, $MAX_ALIVE_AGE;
	$table_id = 'ID'.floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if (count($sosalist)<1) return;
	echo WT_JS_START;?>
	var oTable<?php echo $table_id; ?>;
	jQuery(document).ready(function(){
		/* Initialise datatables */
		oTable<?php echo $table_id; ?> = jQuery('#<?php echo $table_id; ?>').dataTable( {
			"sDom": '<"H"<"filtersH"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF">>',
			"oLanguage": {
				"sLengthMenu": '<?php echo /* I18N: Display %s [records per page], %s is a placeholder for listbox containing numeric options */ WT_I18N::translate('Display %s', '<select><option value="16">16<option value="32">32</option><option value="64">64</option><option value="128">128</option><option value="-1">'.WT_I18N::translate('All').'</option></select>'); ?>',
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
                /* 5-Birth */		{ "iDataSort" : 6 , "sClass": "center"},
                /* 6-SORT_BIRT */	{ "bVisible" : false},
                /* 7-BIRT_PLAC */	{ "iDataSort" : 8 },
                /* 8-SORT_BIRTPL */	{ "bVisible" : false},
                /* 9-Death */		{ "iDataSort" : 10 , "sClass": "center"},
                /* 10-SORT_DEAT */	{ "bVisible" : false},
                /* 11-Age */		{ "iDataSort" : 12 , "sClass": "center"},
                /* 12-AGE */		{ "sType": "numeric", "bVisible": false},
                /* 13-DEAT_PLAC */	{ "iDataSort" : 14 },
                /* 14-SORT_DEATPL */{ "bVisible" : false},
                /* 15-SEX */		{ "bVisible" : false},
                /* 16-BIRT */		{ "bVisible" : false},
                /* 17-DEAT */		{ "bVisible" : false},
                /* 18-TREE */		{ "bVisible" : false}
			],			
            "aaSorting": [[0,'asc']],
			"iDisplayLength": 16,
			"sPaginationType": "full_numbers"
	   });
	   
		jQuery("div.filtersH").html('<?php echo addcslashes(
			'<button type="button" id="SEX_M_'.$table_id.'" class="ui-state-default SEX_M" title="'.WT_I18N::translate('Show only males.').'">'.WT_Person::sexImage('M', 'small').'</button>'.
			'<button type="button" id="SEX_F_'.$table_id.'" class="ui-state-default SEX_F" title="'.WT_I18N::translate('Show only females.').'">'.WT_Person::sexImage('F', 'small').'</button>'.
			'<button type="button" id="SEX_U_'.$table_id.'" class="ui-state-default SEX_U" title="'.WT_I18N::translate('Show only persons of whom the gender is not known.').'">'.WT_Person::sexImage('U', 'small').'</button>'.
			'<button type="button" id="DEAT_N_'.$table_id.'" class="ui-state-default DEAT_N" title="'.WT_I18N::translate('Show people who are alive or couples where both partners are alive.').'">'.WT_I18N::translate('Alive').'</button>'.
			'<button type="button" id="DEAT_Y_'.$table_id.'" class="ui-state-default DEAT_Y" title="'.WT_I18N::translate('Show people who are dead or couples where both partners are deceased.').'">'.WT_I18N::translate('Dead').'</button>'.
			'<button type="button" id="DEAT_YES_'.$table_id.'" class="ui-state-default DEAT_YES" title="'.WT_I18N::translate('Show people who died more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&gt;100</button>'.
			'<button type="button" id="DEAT_Y100_'.$table_id.'" class="ui-state-default DEAT_Y100" title="'.WT_I18N::translate('Show people who died within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('DEAT').'&lt;=100</button>'.
			'<button type="button" id="BIRT_YES_'.$table_id.'" class="ui-state-default BIRT_YES" title="'.WT_I18N::translate('Show persons born more than 100 years ago.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&gt;100</button>'.
			'<button type="button" id="BIRT_Y100_'.$table_id.'" class="ui-state-default BIRT_Y100" title="'.WT_I18N::translate('Show persons born within the last 100 years.').'">'.WT_Gedcom_Tag::getLabel('BIRT').'&lt;=100</button>'.
			'<button type="button" id="TREE_R_'.$table_id.'" class="ui-state-default TREE_R" title="'.WT_I18N::translate('Show «roots» couples or individuals.  These people may also be called «patriarchs».  They are individuals who have no parents recorded in the database.').'">'.WT_I18N::translate('Roots').'</button>'.
			'<button type="button" id="RESET_'.$table_id.'" class="ui-state-default RESET" title="'.WT_I18N::translate('Reset to the list defaults.').'">'.WT_I18N::translate('Reset').'</button>',
			"'");
		?>');

		jQuery("div.filtersF").html('<?php echo addcslashes(
			'<input type="button" class="ui-state-default" id="cb_parents_sosa_list_table" onclick="toggleByClassName(\'DIV\', \'parents_sosa_list_table_'.$table_id.'\');" value="'.WT_I18N::translate('Show parents').'" title="'.WT_I18N::translate('Show parents').'" />'.
			'<input type="button" class="ui-state-default" id="charts_sosa_list_table" onclick="toggleByClassName(\'DIV\', \'sosa_list_table-charts_'.$table_id.'\');" value="'.WT_I18N::translate('Show statistics charts').'" title="'.WT_I18N::translate('Show statistics charts').'" />',
			"'");
		?>');

	   /* Add event listeners for filtering inputs */
		jQuery('#SEX_M_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'M', 15 );});
		jQuery('#SEX_F_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'F', 15 );});
		jQuery('#SEX_U_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'U', 15 );});
		jQuery('#BIRT_YES_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'YES', 16 );});
		jQuery('#BIRT_Y100_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'Y100', 16 );});
		jQuery('#DEAT_N_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'N', 17 );});
		jQuery('#DEAT_Y_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( '^Y', 17, true, false );});
		jQuery('#DEAT_YES_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'YES', 17 );});
		jQuery('#DEAT_Y100_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'Y100', 17 );});
		jQuery('#TREE_R_<?php echo $table_id; ?>').click( function() { oTable<?php echo $table_id; ?>.fnFilter( 'R', 18 );});
		
		jQuery('#RESET_<?php echo $table_id; ?>').click( function() {
			for(i = 0; i < 19; i++){oTable<?php echo $table_id; ?>.fnFilter( '', i );};
		});
		
		/* Add event listeners for toogling inputs */
		jQuery('#cb_parents_sosa_list_table<?php echo $table_id; ?>').click( function() { toggleByClassName('DIV', 'parents_sosa_list_table_<?php echo $table_id; ?>'); });
		jQuery('#charts_sosa_list_table<?php echo $table_id; ?>').click( function() { toggleByClassName('DIV', 'sosa_list_table-charts_<?php echo $table_id; ?>'); });
						
		jQuery(".sosa-list").css('visibility', 'visible');
		jQuery(".loading-image").css('display', 'none');
	});
	<?php echo WT_JS_END;

	$stats = new WT_Stats($GEDCOM);

	// Bad data can cause "longest life" to be huge, blowing memory limits
	$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;

	//-- init chart data
	for ($age=0; $age<=$max_age; $age++) $deat_by_age[$age]="";
	for ($year=1550; $year<2030; $year+=10) $birt_by_decade[$year]="";
	for ($year=1550; $year<2030; $year+=10) $deat_by_decade[$year]="";
	//--table wrapper
	echo '<div class="loading-image">&nbsp;</div>';
	echo '<div class="sosa-list">';
	//-- fieldset
	if ($legend == '') {
		$indi_root = WT_Person::getInstance(get_gedcom_setting(WT_GED_ID, 'PERSO_PS_ROOT_INDI'));
		if($indi_root) $legend = WT_I18N::translate('%s\'s ancestors', $indi_root->getFullName());
	}
	if (isset($WT_IMAGES['sosa-list'])) $legend = '<img src="'.$WT_IMAGES['sosa-list'].'" alt="" align="middle" /> '.$legend;
	echo '<fieldset id="fieldset_indi"><legend>', $legend, '</legend>';
	//-- table header
	echo '<table id="', $table_id, '"><thead><tr>';
	echo '<th>', WT_I18N::translate('Sosa'), '</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('INDI'), '</th>';	
	echo '<th>', WT_Gedcom_Tag::getLabel('NAME'), '</th>';
	echo '<th>GIVN</th>';
	echo '<th>SURN</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('BIRT'), '</th>';
	echo '<th>SORT_BIRT</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('PLAC'), '</th>';
	echo '<th>BIRT_PLAC_SORT</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('DEAT'), '</th>';
	echo '<th>SORT_DEAT</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('AGE'), '</th>';
	echo '<th>AGE</th>';
	echo '<th>', WT_Gedcom_Tag::getLabel('PLAC'), '</th>';
	echo '<th>DEAT_PLAC_SORT</th>';
	echo '<th>SEX</th>';
	echo '<th>BIRT</th>';
	echo '<th>DEAT</th>';
	echo '<th>TREE</th>';
	echo '</tr></thead>';
	//-- table body
	echo '<tbody>';
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
		echo '<tr>';
		//-- Indi Sosa
		echo '<td class="transparent">'.$sosa.'</td>';
		//-- Indi ID
		echo '<td class="transparent">'.$person->getXrefLink().'</td>';
		//-- Indi name(s)
		$tdclass = '';
		if (!$person->isDead()) $tdclass .= ' alive';
		if (!$person->getChildFamilies()) $tdclass .= ' patriarch';
		echo '<td class="', $tdclass, '" align="', get_align($person->getFullName()), '">';
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
			echo '<a ', $title, ' href="', $person->getHtmlUrl(), '" class="', $class, '">', highlight_search_hits($name['full']), '</a>', $sex_image, WT_Perso_Functions_Print::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller') ,"<br/>";
			//END PERSO
		}
		// Indi parents
		echo $person->getPrimaryParentsNames("parents_sosa_list_table_".$table_id." details1", 'none');
		echo '</td>';
		//-- GIVN/SURN
		echo '<td>', htmlspecialchars($givn), ',', htmlspecialchars($surn), '</td>';
		echo '<td>', htmlspecialchars($surn), ',', htmlspecialchars($givn), '</td>';
		//-- Birth date
		echo '<td>';
		if ($birth_dates=$person->getAllBirthDates()) {
			foreach ($birth_dates as $num=>$birth_date) {
				if ($num) {
					echo '<div>', $birth_date->Display(!$SEARCH_SPIDER), '</div>';
				} else {
					echo '<div>', str_replace('<a', '<a name="'.$birth_date->MinJD().'"', $birth_date->Display(!$SEARCH_SPIDER)), '</div>';
				}
			}
			if ($birth_dates[0]->gregorianYear()>=1550 && $birth_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$birt_by_decade[floor($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$birth_date=$person->getEstimatedBirthDate();
			$birth_jd=$birth_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				echo '<div>', str_replace('<a', '<a name="'.$birth_jd.'"', $birth_date->Display(!$SEARCH_SPIDER)), '</div>';
			} else {
				echo '<span class="date"><a name="', $birth_jd, '"/>&nbsp;</span>'; // span needed for alive-in-year filter
			}
			$birth_dates[0]=new WT_Date('');
		}
		echo '</td>';
		//-- Event date (sortable)hidden by datatables code
		echo '<td>', $birth_date->JD(), '</td>';
		//-- Birth place
		echo '<td>';
		$birth_place = '';
		if (list($birth_place, $corr)=$dperson->getEstimatedBirthPlace(true)) {
			if ($SEARCH_SPIDER) {
				echo get_place_short($birth_place), ' ';
			} else {
				echo '<div align="', get_align($birth_place), '">';
				echo '<a href="', get_place_url($birth_place), '" class="list_item" title="', $birth_place, '">';
				echo highlight_search_hits(get_place_short($birth_place));
				if($corr < 1) echo ' '.WT_I18N::translate('(%.0f %%)',$corr * 100);
				echo '</a>';
				echo '</div>';
			}
		} else {
			echo '&nbsp;';
		}
		echo '</td>';
		//-- Birth place (sortable)hidden by datatables code
		echo '<td>', $birth_place, '</td>';
		//-- Death date
		echo '<td>';
		if ($death_dates=$person->getAllDeathDates()) {
			foreach ($death_dates as $num=>$death_date) {
				if ($num) {
					echo '<div>', $death_date->Display(!$SEARCH_SPIDER), '</div>';
				} else if ($death_date->MinJD()!=0) {
					echo '<div>', str_replace('<a', '<a name="'.$death_date->MinJD().'"', $death_date->Display(!$SEARCH_SPIDER)), '</div>';
				}
			}
			if ($death_dates[0]->gregorianYear()>=1550 && $death_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$deat_by_decade[floor($death_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$death_date=$person->getEstimatedDeathDate();
			$death_jd=$death_date->JD();
			if ($SHOW_EST_LIST_DATES) {
				echo '<div>', str_replace('<a', '<a name="'.$death_jd.'"', $death_date->Display(!$SEARCH_SPIDER)), '</div>';
			} else if ($person->isDead()) {
				echo '<div>', WT_I18N::translate('yes'), '<a name="9d', $death_jd, '"></a></div>';
			} else {
				echo '<span class="date"><a name="', $death_jd, '">&nbsp;</span>'; // span needed for alive-in-year filter
			}
			$death_dates[0]=new WT_Date('');
		}
		echo '</td>';
		//-- Event date (sortable)hidden by datatables code
		echo '<td>', $death_date->JD(), '</td>';
		//-- Age at death
		if ($birth_dates[0]->isOK() && $death_dates[0]->isOK()) {
			$age = WT_Date::GetAgeYears($birth_dates[0], $death_dates[0]);
			$age_jd = $death_dates[0]->MinJD()-$birth_dates[0]->MinJD();
			if (!isset($unique_indis[$person->getXref()])) {
				$deat_by_age[max(0, min($max_age, $age))] .= $person->getSex();
			}
		} else {
			$age='';
		}
		echo '<td>'. WT_I18N::number($age). '</td><td>'. $age. '</td>';
		//-- Death place
		echo '<td>';
		$death_place = '';
		if ($death_places=$person->getAllDeathPlaces()) {
			foreach ($death_places as $death_place) {
				if ($SEARCH_SPIDER) {
					echo get_place_short($death_place), ' ';
				} else {
					echo '<div align="', get_align($death_place), '">';
					echo '<a href="', get_place_url($death_place), '" class="list_item" title="', $death_place, '">';
					echo highlight_search_hits(get_place_short($death_place)), '</a>';
					echo '</div>';
				}
			}
		} else {
			echo '&nbsp;';
		}
		echo '</td>';
		//-- Death place (sortable)hidden by datatables code
		echo '<td>', $death_place, '</td>';
		//-- Sorting by gender
		echo '<td>', $person->getSex(), '</td>';
		//-- Filtering by birth date
		echo '<td>';
		if (!$person->canDisplayDetails() || WT_Date::Compare($birth_dates[0], $d100y)>0) {
			echo 'Y100';
		} else {
			echo 'YES';
		}
		echo '</td>';
		//-- Filtering by death date
		echo '<td>';
		if ($person->isDead()) {
			if (WT_Date::Compare($death_dates[0], $d100y)>0) {
				echo 'Y100';
			} else {
				echo 'YES';
			}
		} else {
			echo 'N';
		}
		echo '</td>';
		//-- Roots or Leaves ?
		echo '<td>';
		if (!$person->getChildFamilies()) {
			echo 'R'; // roots
		} elseif (!$person->isDead() && $person->getNumberOfChildren()<1) {
			echo 'L'; // leaves
		}
		echo '</td>';
		echo '</tr>', "\n";
		$unique_indis[$person->getXref()]=true;
		++$n;
	}
	echo '</tbody>';
	//Prepare footer
	$nbSosa = count($sosalist);
	$thSosa = pow(2, $gen-1);
	$perc = WT_Perso_Functions::getPercentage($nbSosa, $thSosa);
	echo '<tfoot>',
		'<tr><td class="ui-state-default" colspan="19">'.WT_I18N::translate('Number of Sosa ancestors: %1$d known / %2$d theoretical (%3$0.2f %%)',$nbSosa, $thSosa, $perc);
	if($n != $nbSosa) echo ' ['.WT_I18N::translate('%d hidden', $nbSosa - $n).']';
	echo '</td></tr></tfoot>';
	echo '</table>';
	echo '</div>'; // Close "sosa-list"
	//-- charts
	echo "<div class=\"sosa_list_table-charts_".$table_id."\">";
	echo "<table class=\"list_table center\">";
	echo "<tr><td class=\"list_value_wrap\">";
	print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth'));
	echo "</td><td class=\"list_value_wrap\">";
	print_chart_by_decade($deat_by_decade, WT_I18N::translate('Decade of death'));
	echo "</td></tr><tr><td colspan=\"2\" class=\"list_value_wrap\">";
	print_chart_by_age($deat_by_age, WT_I18N::translate('Age related to death year'));
	echo "</td></tr></table>";
	echo "</div>";
	echo "</fieldset>";
}

print_header(WT_I18N::translate('Sosa Ancestors'));

echo '<div class="center"><h2>', WT_I18N::translate('Sosa Ancestors'), '</h2>';

$maxGen = WT_Perso_Functions_Sosa::getLastGeneration();

if($maxGen>0){
	$selectedgen = safe_REQUEST($_REQUEST, 'gen', WT_REGEX_INTEGER, null);
	
	echo '<form method="post" name="setgen" action="module.php?mod=perso_sosa&mod_action=sosalist">',
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
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen-1,'"><img src="',$WT_IMAGES['ldarrow'],'" title="',WT_I18N::translate('Previous generation'),'" alt="',WT_I18N::translate('Previous generation'),'" />&nbsp;&nbsp;</a>',
			WT_I18N::translate('Generation %d', $selectedgen),
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen+1,'">&nbsp;&nbsp;<img src="',$WT_IMAGES['rdarrow'],'" title="',WT_I18N::translate('Next generation'),'" alt="',WT_I18N::translate('Next generation'),'" /></a>',
			'</h4>';
		$listSosa=WT_Perso_Functions_Sosa::getSosaListAtGeneration($selectedgen);
		if($listSosa){
			print_sosa_table($listSosa, $selectedgen);
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

print_footer();

?>