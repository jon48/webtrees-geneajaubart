<?php
/**
 * Display Geographical Dispersion of Sosa Ancestors.
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
	->setPageTitle(WT_I18N::translate('Sosa Geographical dispersion'))
	->pageHeader();

echo '<div class="pgeodispersion-geodispersion-page center">',
	'<h2>', WT_I18N::translate('Sosa Geographical dispersion'), '</h2>';


if(WT_Perso_Functions_Sosa::isModuleOperational()){
	$geoid = safe_GET('geoid', WT_REGEX_INTEGER, null);
	
	if($geoid){
		$title = getGeoDispersionTitle($geoid);
		
		if($title){
			$controller
			->addExternalJavaScript(WT_STATIC_URL.'js/raphaeljs/raphael.min.js')
			->addInlineJavaScript('
					jQuery("#geodispersion-tabs").tabs();
					jQuery("#geodispersion-tabs").css("visibility", "visible");
					
					jQuery.get(
						"module.php",
						{ "mod" : "perso_geodispersion",  "mod_action": "ajaxgeodispersiondata", "geoid" : "'.$geoid.'" },
						function(data){
							if(data){	    
								jQuery("#geodisp-data-general").html(data.generaltab);
								jQuery("#geodisp-data-generations").html(data.generationstab);
						    }
						    jQuery(".loading-image").hide();				    		
						},
						"json"
					);
				');	
			
			echo '<div id="geodispersion-panel">';
			echo '<h4>',WT_I18N::translate($title),'</h4>';
			
			//Construct tabs
			echo '<div id="geodispersion-tabs">';
			echo '<ul>',
					'<li><a href="#geodisp-general">'.WT_I18N::translate('General data').'</a></li>',
					'<li><a href="#geodisp-generations">'.WT_I18N::translate('Data by Generations').'</a></li>',
				'</ul>';
			
			echo '<div id="geodisp-general">';
			echo '<div id="loading-general" class="loading-image">&nbsp;</div>';
			echo '<div id="geodisp-data-general" class="center"></div>';
			echo '</div>'; // close "geodisp-general"
			
			echo '<div id="geodisp-generations">';
			echo '<div id="loading-generations" class="loading-image">&nbsp;</div>';
			echo '<div id="geodisp-data-generations"></div>';
			echo '</div>'; // close "geodisp-generations"
			
			echo '</div>'; //close div "geodispersion-tabs"
			echo '</div>'; //close div "geodispersion-panel"
		
		}
		else{
			echo '<p class="warning">'.WT_I18N::translate('The required dispersion analysis does not exist.').'<p>';
		}
		
	}
	else{
		// Display the list of available maps;
	}
		
}
else{
	echo '<p class="warning">'.WT_I18N::translate('The Perso Sosa module must be installed and enabled to display this page.').'<p>';
}

echo '</div>';


/**
 * Return the title of the GeoDispersion analysis
 *
 * @param null|int $geoid ID of the Geodispersion analysis
 */
function getGeoDispersionTitle($geoid){
	return WT_DB::prepare('SELECT pg_descr FROM ##pgeodispersion WHERE pg_id=? AND pg_status=?')
		->execute(array($geoid, 'enabled'))->fetchOne(null);
}

?>