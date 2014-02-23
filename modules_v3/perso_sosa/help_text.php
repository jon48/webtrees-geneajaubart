<?php
/**
 * Module Perso Sosa help text
 *
 * @package webtrees
 * @subpackage Perso
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
	case 'config_root_indi':
		$title=WT_I18N::translate('Root individual');
		$text=
			'<p>'.
			WT_I18N::translate('Define the Sosa root individual for the specified GEDCOM.').
			'</p>';
		break;
	case 'config_computesosa':
		$title=WT_I18N::translate('Compute all Sosas');
		$text=
			'<p>'.
			WT_I18N::translate('Compute all Sosa ancestors for the specified GEDCOM, from the set Sosa root individual.').
			'</p>';
		break;
	case 'stats_theoretical':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Theoretical');
		$text=
			'<p>'.
			WT_I18N::translate('Theoretical number of ancestors in generation G.').
			'</p>';
		break;
	case 'stats_known':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Known');
		$text=
			'<p>'.
			WT_I18N::translate('Number of ancestors found in generation G. A same individual can be counted several times.').
			'</p>'.
			'<p>'.
			WT_I18N::translate('The <strong>%%</strong> column is the ratio of found ancestors in generation G compared to the theoretical number.').
			'</p>';
		break;
	case 'stats_losses':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Losses G-1');
		$text=
			'<p>'.
			WT_I18N::translate('Number of ancestors not found in generation G, but whose children are known in generation G-1.').
			'</p>'.
			'<p>'.
			WT_I18N::translate('The <strong>%%</strong> column is the ratio of not found ancestors in generation G amongst the theoretical ancestors in this generation whose children are known in generation G-1. This is an indicator of the completion of a generation relative to the completion of the previous generation.').
			'</p>';
		break;
	case 'stats_totalknown':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Total known');
		$text=
			'<p>'.
			WT_I18N::translate('Cumulative number of ancestors found up to generation G. A same individual can be counted  several times.').
			'</p>'.
			'<p>'.
			WT_I18N::translate('The <strong>%%</strong> column is the ratio of cumulative found ancestors in generation G compared to the cumulative theoretical number.').
			'</p>';
		break;
	case 'stats_different':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Different');
		$text=
			'<p>'.
			WT_I18N::translate('Number of distinct ancestors found in generation G. A same individual is counted only once.').
			'</p>'.
			'<p>'.
			WT_I18N::translate('The <strong>%%</strong> column displays the ratio of distinct individuals compared to the number of ancestors found in generation G.').
			'</p>';
		break;
	case 'stats_totaldiff':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Total Different');
		$text=
			'<p>'.
			WT_I18N::translate('Number of cumulative distinct ancestors found up to generation G. A same individual is counted only once in the total number, even if present in different generations.').
			'</p>';
		break;
	case 'stats_implex':
		$title=WT_I18N::translate('Sosa statistics').' - '.WT_I18N::translate('Pedigree collapse');
		$text=
			'<p>'.
			WT_I18N::translate('Pedigree collapse at generation G.').
			'</p>'.
			'<p>'.
			WT_I18N::translate('Pedigree collapse is a measure of the real number of ancestors of a person compared to its theorical number. The higher this number is, the more marriages between related persons have happened. Extreme examples of high pedigree collapse are royal families for which this number can be as high as nearly 90%% (Alfonso XII of Spain).').
			'</p>'
			;
		break;
	
}

?>