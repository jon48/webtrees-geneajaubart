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

print_header(WT_I18N::translate('Sosa Ancestors'));

require_once WT_ROOT.'js/sorttable.js.htm';

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
			ksort($listSosa);
			$table_id = 'ID'.floor(microtime()*1000000); // sorttable requires a unique ID
			$tableSosa = 	'<table id="'.$table_id.'" class="sortable list_table">';
			$tableSosa .= 	'<thead><tr>';
			$tableSosa .= 	'<th class="list_label transparent">'.WT_I18N::translate('Sosa').'</th>';
			$tableSosa .= 	'<th class="list_label transparent">'.WT_I18N::translate('INDI').'</th>';
			$tableSosa .= 	'<th class="list_label">'.WT_Gedcom_Tag::getLabel('NAME').'</th>';
			$tableSosa .= 	'<th class="list_label">'.WT_Gedcom_Tag::getLabel('BIRT').'</th>';
			$tableSosa .= 	'<th class="list_label">'.WT_Gedcom_Tag::getLabel('DEAT').'</th>';
			$tableSosa .= 	'<th class="list_label">'.WT_I18N::translate('Birth place (known or supposed)').'</th>';
			$tableSosa .= 	'</tr></thead>';
			$nbDisplayed = 0;
			$tableSosa .= 	'<tbody>';
			foreach($listSosa as $sosa=>$pid){
				$indi=WT_Person::getInstance($pid);
				if($indi && $indi->canDisplayDetails()){
					$dindi = new WT_Perso_Person($indi);
					$nbDisplayed++;
					$tableSosa .= 	'<tr>';
					$tableSosa .= 	'<td class="list_value_wrap transparent">'.$sosa.'</td>';
					$tableSosa .= 	'<td class="list_value_wrap transparent">'.$indi->getXrefLink().'</td>';
					$tableSosa .= 	'<td class="list_value_wrap left"><a href="'.$indi->getHtmlUrl().'" class="list_item name2">'.$indi->getFullName().'</a></td>';
					$estimatedDate = 	$indi->getEstimatedBirthDate();
					$tableSosa .=	'<td class="list_value_wrap" sorttable_customkey="'.$estimatedDate->JD().'">&nbsp;'.$estimatedDate->Display(true, "%d %M %Y").'&nbsp;</td>';
					$estimatedDate = 	$indi->getEstimatedDeathDate();
					$tableSosa .=	'<td class="list_value_wrap" sorttable_customkey="'.$estimatedDate->JD().'">&nbsp;'.$estimatedDate->Display(true, "%d %M %Y").'&nbsp;</td>';
					$place		=	$dindi->getEstimatedBirthPlace();
					$tableSosa .= 	'<td class="list_value_wrap center">'.$place.'</td></tr>';
				}
			}
			$tableSosa .= 	'</tbody>';
			$nbSosa = count($listSosa);
			$thSosa = pow(2, $selectedgen-1);
			$perc = WT_Perso_Functions::getPercentage($nbSosa, $thSosa);
			$tableSosa .= 	'<tfoot><tr class="sortbottom"><td class="list_label" colspan="6">'.WT_I18N::translate('Number of Sosa ancestors: %1$d known / %2$d theoretical (%3$0.2f %%)',$nbSosa, $thSosa, $perc);
			if($nbDisplayed != $nbSosa) $tableSosa .= ' ['.WT_I18N::translate('%d hidden', $nbSosa - $nbDisplayed).']';
			$tableSosa .=	'</td></tr></tfoot>';
			$tableSosa .= 	'</table>';
	
			echo $tableSosa;
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