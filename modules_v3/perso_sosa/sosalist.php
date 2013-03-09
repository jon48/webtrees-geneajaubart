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

global $controller;

$selectedgen = safe_REQUEST($_REQUEST, 'gen', WT_REGEX_INTEGER, null);

$controller=new WT_Controller_Base();
$controller
	->setPageTitle(WT_I18N::translate('Sosa Ancestors'))
	->pageHeader()
	->addInlineJavascript('
		jQuery("#sosalist-tabs").tabs();
		jQuery("#sosalist-tabs").css("visibility", "visible");

		jQuery.get(
			"module.php",
			{ "mod" : "perso_sosa",  "mod_action": "ajaxsosalistdata", "type" : "indi", "gen" : "'.$selectedgen.'" },
			"html"
		).success(
			function(data){
				if(data){
					jQuery("#sosalist-indi-data").html(data);
					/* datatablesosaindi(); */
			    }
			    jQuery("#loading-indi").hide();
			}
		).error(
			function(){
				jQuery("#sosalist-indi-data").html("'.addslashes('<p class="warning">'.WT_I18N::translate('An error occurred while retrieving data...').'</p>').'");
			    jQuery("#loading-indi").hide();
			}
		);

		jQuery.get(
			"module.php",
			{ "mod" : "perso_sosa",  "mod_action": "ajaxsosalistdata", "type" : "fam", "gen" : "'.$selectedgen.'" },
			"html"
		).success(
			function(data){
				if(data){
					jQuery("#sosalist-fam-data").html(data);
			    }
			    jQuery("#loading-fam").hide();
			}
		).error(
			function(){
				jQuery("#sosalist-fam-data").html("'.addslashes('<p class="warning">'.WT_I18N::translate('An error occurred while retrieving data...').'</p>').'");
			    jQuery("#loading-fam").hide();
			}
		);

	');
;

echo '<div class="center"><h2>', WT_I18N::translate('Sosa Ancestors'), '</h2>';

$maxGen = WT_Perso_Functions_Sosa::getLastGeneration();

if($maxGen>0){	
	echo '<form method="get" name="setgen" action="module.php">',
		'<input type="hidden" name="mod" value="perso_sosa">',
		'<input type="hidden" name="mod_action" value="sosalist">',
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
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen-1,'"><i class="icon-ldarrow" title="',WT_I18N::translate('Previous generation'),'" ></i>&nbsp;&nbsp;</a>',
			WT_I18N::translate('Generation %d', $selectedgen),
			'<a href="module.php?mod=perso_sosa&mod_action=sosalist&gen=',$selectedgen+1,'">&nbsp;&nbsp;<i class="icon-rdarrow" title="',WT_I18N::translate('Next generation'),'" ></i></a>',
			'</h4>';
		$nbSosa = WT_Perso_Functions_Sosa::getSosaCountAtGeneration($selectedgen);
		if($nbSosa > 0){		
			//Construct tabs
			echo '<div id="sosalist-tabs">';
			echo '<ul>',
					'<li><a href="#sosalist-indi">'.WT_I18N::translate('Individuals').'</a></li>',
					'<li><a href="#sosalist-fam">'.WT_I18N::translate('Families').'</a></li>',
				'</ul>';
			
			echo '<div id="sosalist-indi">';
			echo '<div id="loading-indi" class="loading-image">&nbsp;</div>';
			echo '<div id="sosalist-indi-data" class="center"></div>';
			echo '</div>'; // close "sosalist-indi"
			
			echo '<div id="sosalist-fam">';
			echo '<div id="loading-fam" class="loading-image">&nbsp;</div>';
			echo '<div id="sosalist-fam-data"></div>';
			echo '</div>'; // close "sosalist-fam"
			
			echo '</div>'; //close div "sosalist-tabs"
		}
		else{
			echo '<p class="warning">'.WT_I18N::translate('No ancestor has been found for generation %d', $selectedgen).'</p>';
		}			
	}

}
else {
	echo '<p class="warning">'.WT_I18N::translate('The list could not be displayed. Reasons might be:').'<br/><ul><li>'.
		WT_I18N::translate('No Sosa root individual has been defined.').'</li><li>'.
		WT_I18N::translate('The Sosa ancestors have not been computed yet.').'</li></p>';
}

echo '</div>';
?>