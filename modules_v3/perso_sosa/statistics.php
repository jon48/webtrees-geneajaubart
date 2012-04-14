<?php
/**
 * Display the Sosa statistics page.
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

$controller=new WT_Controller_Base();
$controller
	->setPageTitle(WT_I18N::translate('Sosa Statistics'))
	->pageHeader();

echo '<div class="center"><h2>', WT_I18N::translate('Sosa Statistics'), '</h2>';

$indi_root = WT_Person::getInstance(get_gedcom_setting(WT_GED_ID, 'PERSO_PS_ROOT_INDI'));
if($indi_root){
	
	$statsGen = WT_Perso_Functions_Sosa::getStatisticsByGeneration();
	$stats = new WT_Stats(WT_GEDCOM);

	//General statistics
	
	$sosaCount = WT_Perso_Functions_Sosa::getSosaCount();
	$diffSosaCount = WT_Perso_Functions_Sosa::getDifferentSosaCount();
	$percSosaBase = WT_Perso_Functions::getPercentage($diffSosaCount, $stats->_totalIndividuals());
	$implex = 100-WT_Perso_Functions::getPercentage($diffSosaCount, $sosaCount);
	if($indi_root->canDisplayDetails()) echo '<h4>'.WT_I18N::translate('%s\'s ancestors', PrintReady($indi_root->getFullName())).'</h4>';
	echo '<table class="list_table">',
		'<td colspan="2" class="topbottombar center">',WT_I18N::translate('General statistics'),'</td>',
		'<tr><td class="descriptionbox">', WT_I18N::translate('Number of ancestors'),'</td>',
		'<td class="optionbox vmiddle">', $sosaCount, '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Number of different ancestors'),'</td>',
		'<td class="optionbox vmiddle">', $diffSosaCount, '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('%% of ancestors in the base'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::translate('%.1f %%',$percSosaBase), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Pedigree collapse'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::translate('%.2f %%',$implex), '</td></tr>',
		'<tr><td class="descriptionbox">',WT_I18N::translate('Mean generation time'),'</td>',
		'<td class="optionbox vmiddle">', WT_I18N::translate('%.1f years', WT_Perso_Functions_Sosa::getMeanGenerationTime()), '</td></tr>',
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
		$percSosaCountTheor = WT_Perso_Functions::getPercentage($tab['sosaCount'], $genTheoretical);
		$genEquiv += $percSosaCountTheor/100;
		$missing=2*$prevKnown - $tab['sosaCount'];
		$percSosaCountPrevKnown = 100-WT_Perso_Functions::getPercentage($tab['sosaCount'], 2*$prevKnown);
		$percSosaTotCountTotTheor = WT_Perso_Functions::getPercentage($tab['sosaTotalCount'], $totalTheoretical);
		$genDiff=$tab['diffSosaTotalCount']-$prevDiff;
		$percGenDiffSosaCount = WT_Perso_Functions::getPercentage($genDiff, $tab['sosaCount']);
		$percImplex = 100 - WT_Perso_Functions::getPercentage($tab['diffSosaTotalCount'], $tab['sosaTotalCount']); 
		echo '<tr><td class="descriptionbox">', WT_I18N::translate('<strong>G%d</strong>', $gen),'</td>',
			'<td class="descriptionbox">'.WT_I18N::translate('%1$s <> %2$s', $genY1, $genY2),'</td>',
			'<td class="optionbox vmiddle">', $genTheoretical,'</td>',
			'<td class="optionbox vmiddle">', $tab['sosaCount'], '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::translate('%.2f %%',$percSosaCountTheor) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', $missing, '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::translate('%.2f %%',$percSosaCountPrevKnown) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', $tab['sosaTotalCount'], '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::translate('%.2f %%',$percSosaTotCountTotTheor) ,'&nbsp;</td>',
			'<td class="optionbox vmiddle">', $genDiff, '</td>',
			'<td class="optionbox vmiddle left percent_container"><div class="percent_frame"><div class="percent_cell" style="width:',$percGenDiffSosaCount, '%;">&nbsp;', WT_I18N::translate('%.0f %%',$percGenDiffSosaCount), '&nbsp;</div></div></td>',
			'<td class="optionbox vmiddle">', $tab['diffSosaTotalCount'], '</td>',
			'<td class="optionbox vmiddle right">&nbsp;', WT_I18N::translate('%.2f %%',$percImplex) ,'&nbsp;</td>',
			'</tr>';
		$genTheoretical = $genTheoretical * 2;
		$prevKnown=$tab['sosaCount'];
		$prevDiff=$tab['diffSosaTotalCount'];
	}
	echo '<tr><td colspan="13" class="topbottombar center">', WT_I18N::translate('Generation-equivalent: %.2f generations', $genEquiv), '</td></tr>',
		'</table>';
	
	
}
else{
	echo '<div class="warning">'.WT_I18N::translate('No Sosa root individual has been defined.').'</div>';
}

echo '</div>';

?>