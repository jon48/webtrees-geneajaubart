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

global $WT_IMAGES;

print_header(WT_I18N::translate('Missing Ancestors'));

require_once WT_ROOT.'js/sorttable.js.htm';

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
			$indiAlreadyDisplayed = array();
			$sumMissingDifferent = 0;
			$sumMissingDifferentWithoutHidden = 0;
			echo '<h4>';
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen-1).'"><img src="'.$WT_IMAGES['ldarrow'].'" title="'.WT_I18N::translate('Previous generation').'" alt="'.WT_I18N::translate('Previous generation').'" />&nbsp;&nbsp;</a>';
			echo WT_I18N::translate('Generation %d', $gen);
			if($selectedgen > 1) echo '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.($gen+1).'">&nbsp;&nbsp;<img src="'.$WT_IMAGES['rdarrow'].'" title="'.WT_I18N::translate('Next generation').'" alt="'.WT_I18N::translate('Next generation').'" /></a>';
			echo '</h4>';
			$listGenG=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen-1);
			$listGenG1=WT_Perso_Functions_Sosa::getSosaListAtGeneration($gen);
			if($listGenG){
				ksort($listGenG);
				$areMissing = false;
				$table_id = 'ID'.floor(microtime()*1000000); // sorttable requires a unique ID
				$tableMissing = 	'<table id="'.$table_id.'" class="sortable list_table">';
				$tableMissing .= 	'<thead><tr>';
				$tableMissing .= 	'<th class="list_label transparent">'.WT_I18N::translate('Sosa').'</th>';
				$tableMissing .= 	'<th class="list_label transparent">'.WT_I18N::translate('INDI').'</th>';
				$tableMissing .= 	'<th class="list_label">'.WT_Gedcom_Tag::getLabel('NAME').'</th>';
				$tableMissing .= 	'<th class="list_label">'.WT_Gedcom_Tag::getLabel('BIRT').'</th>';
				$tableMissing .= 	'<th class="list_label sorttable_nosort">'.WT_I18N::translate('Father').'</th>';
				$tableMissing .= 	'<th class="list_label sorttable_nosort">'.WT_I18N::translate('Mother').'</th>';
				$tableMissing .= 	'<th class="list_label sorttable_nosort">'.WT_I18N::translate('Birth place (known or supposed)').'</th>';
				$tableMissing .= 	'</tr></thead>';
				$tableMissing .=	'<tbody>';
				foreach($listGenG as $sosa=>$pid){
					$miss = array('father' => false, 'mother' => false);
					if(!isset($listGenG1[2*$sosa])) $miss['father'] = true;
					if(!isset($listGenG1[2*$sosa + 1])) $miss['mother'] = true;
					if($miss['father'] || $miss['mother']){
						$areMissing = true;
						if(!isset($indiAlreadyDisplayed[$pid])){
							$sumMissingDifferent += $miss['father'] + $miss['mother'];
							$indiAlreadyDisplayed[$pid] = true;
							$indi=WT_Person::getInstance($pid);
							if($indi && $indi->canDisplayDetails()){
								$sumMissingDifferentWithoutHidden += $miss['father'] + $miss['mother'];
								$dindi = new WT_Perso_Person($indi);
								$tableMissing .= 	'<tr>';
								$tableMissing .= 	'<td class="list_value_wrap transparent">'.$sosa.'</td>';
								$tableMissing .= 	'<td class="list_value_wrap transparent">'.$indi->getXrefLink().'</td>';
								$tableMissing .= 	'<td class="list_value_wrap left"><a href="'.$indi->getHtmlUrl().'" class="list_item name2">'.PrintReady($indi->getListName()).'</a></td>';
								$estimatedDate = 	$indi->getEstimatedBirthDate();
								$tableMissing .=	'<td class="list_value_wrap" sorttable_customkey="'.$estimatedDate->JD().'">&nbsp;'.$estimatedDate->Display(true, "%d %M %Y").'&nbsp;</td>';
								$tableMissing .=	'<td class="list_value_wrap">';
								$tableMissing .=	$miss['father'] ? 'X' : '&nbsp;';	
								$tableMissing .=	'</td><td class="list_value_wrap">';
								$tableMissing .=	$miss['mother'] ? 'X' : '&nbsp;';	
								$tableMissing .=	'</td>';
								$place	=	$dindi->getEstimatedBirthPlace();
								$tableMissing .= 	'<td class="list_value_wrap center">'.$place.'</td></tr>';
							}
						}
					}
				}
				$tableMissing .=	'</tbody>';
				$tableMissing .= 	'<tfoot><tr class="sortbottom"><td class="list_label" colspan="7">'.WT_I18N::translate('Number of different missing ancestors: %d',$sumMissingDifferent);
				if($sumMissingDifferent != $sumMissingDifferentWithoutHidden) $tableMissing .= ' ['.WT_I18N::translate('%d hidden', $sumMissingDifferent - $sumMissingDifferentWithoutHidden).']';
				$percSosa = WT_Perso_Functions::getPercentage(count($listGenG1), pow(2, $gen-1));
				$percPotentialSosa = WT_Perso_Functions::getPercentage(count($listGenG), pow(2, $gen-2));
				$tableMissing .=	' - '.WT_I18N::translate('Generation complete at %.2f %%', $percSosa);
				$tableMissing .=	' ['.WT_I18N::translate('Potential %.2f %%', $percPotentialSosa).']';
				$tableMissing .=	'</td></tr></tfoot>';
				$tableMissing .= 	'</table>';
				
				if($areMissing){
					echo $tableMissing;
				}
				else{
					echo WT_I18N::translate('No ancestors are missing for this generation. Generation complete at %.2f %%.', $percSosa);
				}
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
	