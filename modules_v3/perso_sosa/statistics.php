<?php
/**
 * Display the Sosa statistics page.
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $controller, $WT_TREE;

$controller=new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate('Sosa Statistics'))
	->pageHeader();

echo '<div class="center"><h2>', $controller->getPageTitle(), '</h2>';

$indi_root = WT_Individual::getInstance($WT_TREE->getPreference('PERSO_PS_ROOT_INDI'));
if($indi_root){
	
	$statsGen = WT_Perso_Functions_Sosa::getStatisticsByGeneration();
	
	//General statistics	
	$sosaCount = WT_Perso_Functions_Sosa::getSosaCount();
	$diffSosaCount = WT_Perso_Functions_Sosa::getDifferentSosaCount();
	$percSosaBase = WT_Perso_Functions::safeDivision($diffSosaCount, WT_Perso_Query_Stats::totalIndividuals($WT_TREE));
	$implex = 1-WT_Perso_Functions::safeDivision($diffSosaCount, $sosaCount);
	if($indi_root->canShow()) echo '<h4>'.WT_I18N::translate('%s\'s ancestors', $indi_root->getFullName()).'</h4>';
	$meantimegentime = WT_Perso_Functions_Sosa::getMeanGenerationTime();
	echo '<table class="list_table">',
		'<td colspan="2" class="topbottombar center">',WT_I18N::translate('General statistics'),'</td>',
		'<tr><td class="descriptionbox">', WT_I18N::translate('Number of ancestors'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::number($sosaCount), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Number of different ancestors'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::number($diffSosaCount), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('%% of ancestors in the base'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::percentage($percSosaBase, 1), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Pedigree collapse'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::percentage($implex, 2), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Mean generation time'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::plural('%s year', '%s years', $meantimegentime,  WT_I18N::number($meantimegentime, 1)), '</td></tr>',
		'</table>';	
	
	echo '<br/>';
	
	//Detailed statistics by generations
	echo '<table class="list_table sosastats">',
		'<td colspan="13" class="topbottombar center">',WT_I18N::translate('Statistics by generations'),'</td>',
		'<tr><th colspan="2" class="topbottombar center">&nbsp;</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Theoretical'),help_link('stats_theoretical', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Known'),help_link('stats_known', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('%%'),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Losses G-1'),help_link('stats_losses', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('%%'),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Total known'),help_link('stats_totalknown', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('%%'),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Different'),help_link('stats_different', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('%%'),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Total Different'),help_link('stats_totaldiff', $this->getName()),'</th>',
		'<th class="topbottombar center">',WT_I18N::translate('Pedigree collapse'),help_link('stats_implex', $this->getName()),'</th>',
		'<tr>';

	$genTheoretical=1;
	$totalTheoretical=0;
	$prevDiff=0;
	$prevKnown=0.5;
	$genEquiv=0;

	foreach($statsGen as $gen=>$tab){
		$genY1=WT_I18N::translate('-');
		$genY2=WT_I18N::translate('-');
		if($tab['firstBirth']>0) $genY1=$tab['firstBirth'];
		if($tab['lastBirth']>0) $genY2=$tab['lastBirth'];
		$totalTheoretical += $genTheoretical;
		$percSosaCountTheor = WT_Perso_Functions::safeDivision($tab['sosaCount'], $genTheoretical);
		$genEquiv += $percSosaCountTheor;
		$missing=2*$prevKnown - $tab['sosaCount'];
		$percSosaCountPrevKnown = 1-WT_Perso_Functions::safeDivision($tab['sosaCount'], 2*$prevKnown);
		$percSosaTotCountTotTheor = WT_Perso_Functions::safeDivision($tab['sosaTotalCount'], $totalTheoretical);
		$genDiff=$tab['diffSosaTotalCount']-$prevDiff;
		$percGenDiffSosaCount = WT_Perso_Functions::safeDivision($genDiff, $tab['sosaCount']);
		$percImplex = 1 - WT_Perso_Functions::safeDivision($tab['diffSosaTotalCount'], $tab['sosaTotalCount']); 
		echo '<tr><td class="descriptionbox">', WT_I18N::translate('<strong>G%d</strong>', $gen),'</td>',
			'<td class="descriptionbox">'.WT_I18N::translate('%1$s <> %2$s', $genY1, $genY2),'</td>',
			'<td class="optionbox vmiddle">', WT_I18N::number($genTheoretical),'</td>',
			'<td class="optionbox vmiddle">', WT_I18N::number($tab['sosaCount']), '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::percentage($percSosaCountTheor, 2) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', $missing > 0 ? '<a href="module.php?mod=perso_sosa&mod_action=missingancestors&gen='.$gen.'">'.WT_I18N::number($missing).'</a>' : WT_I18N::number($missing), '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::percentage($percSosaCountPrevKnown, 2) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', WT_I18N::number($tab['sosaTotalCount']), '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::percentage($percSosaTotCountTotTheor,2) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', WT_I18N::number($genDiff), '</td>',
			'<td class="optionbox vmiddle left percent_container"><div class="percent_frame"><div class="percent_cell" style="width:',100*$percGenDiffSosaCount, '%;">&nbsp;', WT_I18N::percentage($percGenDiffSosaCount), '&nbsp;</div></div></td>',
			'<td class="optionbox vmiddle">', WT_I18N::number($tab['diffSosaTotalCount']), '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::percentage($percImplex,2) ,'&nbsp;</td>',
			'</tr>';
		$genTheoretical = $genTheoretical * 2;
		$prevKnown=$tab['sosaCount'];
		$prevDiff=$tab['diffSosaTotalCount'];
	}
	echo '<tr><td colspan="13" class="topbottombar center">', WT_I18N::translate('Generation-equivalent: %s generations', WT_I18N::number($genEquiv,2)), '</td></tr>',
		'</table>';
	
	
}
else{
	echo '<div class="warning">'.WT_I18N::translate('No Sosa root individual has been defined.').'</div>';
}

echo '</div>';

?>