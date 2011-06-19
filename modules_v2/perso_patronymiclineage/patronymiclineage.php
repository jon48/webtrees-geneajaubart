<?php
/**
 * Displays the patronymic lineage page.
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

global $SEARCH_SPIDER, $SURNAME_LIST_STYLE, $WT_IMAGES, $UNKNOWN_NN;

define('WT_ICON_RINGS', '<img src="'.$WT_IMAGES['rings'].'" alt="'.WT_Gedcom_Tag::getLabel('MARR').'" title="'.WT_Gedcom_Tag::getLabel('MARR').'" />');

// Retrieve parameters.
$alpha   =safe_GET('alpha'); // All surnames beginning with this letter where "@"=unknown and ","=none
$surname =safe_GET('surname', '[^<>&%{};]*'); // All indis with this surname.  NB - allow ' and "
$show_all=safe_GET('show_all', array('no','yes'), 'no'); // All surnames

// Don't show the list until we have some filter criteria
if (isset($alpha) || isset($surname) || $show_all == 'yes') {
	$showList = true;
} else {
	$showList = false;
}

// Fetch a list of the initial letters of all surnames in the database
$initials=WT_Query_Name::surnameAlpha(false, false, WT_GED_ID);

// Make sure selections are consistent.
// i.e. can't specify show_all and surname at the same time.
if ($show_all=='yes') {
	$alpha='';
	$surname='';
	$surname_sublist = 'yes';
	$legend=WT_I18N::translate('All');
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&show_all=yes';
} elseif ($surname) {
	$alpha=WT_Query_Name::initialLetter($surname);
	$show_all='no';
	if ($surname=='@N.N.') {
		$legend=$UNKNOWN_NN;
	} else {
		$legend=$surname;
	}
	$surname_sublist='no';
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&surname='.rawurlencode($surname);
} else {
	$show_all='no';
	$surname='';
	$surname_sublist = 'yes';
	if ($alpha=='@') {
		$legend=$UNKNOWN_NN;
		$surname_sublist='no';
		$surname='@N.N.';
	} else {
		$legend=$alpha;
	}
	$url='module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($alpha);
}

print_header(WT_I18N::translate('Patronymic Lineages').' : '.$legend);
echo '<h2 class="center">', WT_I18N::translate('Patronymic Lineages'), '</h2>';

// Print a selection list of initial letters
foreach ($initials as $letter=>$count) {
	switch ($letter) {
	case '@':
		$html=$UNKNOWN_NN;
		break;
	case ',':
		break;
	case ' ':
		$html='&nbsp;';
		break;
	default:
		$html=$letter;
		break;
	}
	if($letter != ','){
		if ($count) {
			if ($showList && $letter==$alpha && $show_all=='no') {
				if ($surname) {
					$html='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($letter).'&amp;ged='.WT_GEDURL.'" class="warning" title="'.$count.'">'.$html.'</a>';
				} else {
					$html='<span class="warning" title="'.$count.'">'.$html.'</span>';
				}
			} else {
				$html='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&alpha='.rawurlencode($letter).'&amp;ged='.WT_GEDURL.'" title="'.$count.'">'.$html.'</a>';
			}
		}
		$list[]=$html;
	}
}

// Search spiders don't get the "show all" option as the other links give them everything.
if (!$SEARCH_SPIDER) {
	if ($show_all=='yes') {
		$list[]='<span class="warning">'.WT_I18N::translate('All').'</span>';
	} else {
		$list[]='<a href="module.php?mod=perso_patronymiclineage&mod_action=patronymiclineage&show_all=yes'.'&amp;ged='.WT_GEDURL.'">'.WT_I18N::translate('All').'</a>';
	}
}

echo '<div class="alpha_index"><p class="center">';
echo WT_I18N::translate('Choose a letter to show individuals whose family name starts with that letter.');
echo help_link('alpha');
echo '<br />', join(' | ', $list), '</p>';

echo '</div>';

if ($showList) {
	$surns=WT_Query_Name::surnames($surname, $alpha, false, false, WT_GED_ID);
	if ($surname_sublist=='yes') {
		// Show the surname list
		switch ($SURNAME_LIST_STYLE) {
		case 'style1';
			echo format_surname_list($surns, 3, true, 'module', '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		case 'style3':
			echo format_surname_tagcloud($surns, 'module', true, '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		case 'style2':
		default:
			echo format_surname_table($surns, 'module', '&mod=perso_patronymiclineage&mod_action=patronymiclineage');
			break;
		}
	} else {
		//Link to indilist
		echo '<p class="center"><strong><a href="indilist.php?ged='.WT_GEDCOM.'&surname='.urlencode($surname).'">'.WT_I18N::translate('Go to the list of individuals with surname %s', check_NN($legend)).'</a></strong></p>';
		
		if ($legend && $show_all=='no') {
			$legend=WT_I18N::translate('Individuals in %s lineages', check_NN($legend));
		}
		
		WT_Perso_Functions_PatronymicLineage::printLineages($surname, $legend, WT_GED_ID);
	}
}

print_footer();

?>