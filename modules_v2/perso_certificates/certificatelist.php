<?php
/**
 * List of certificates used to document events in webtrees.
 * These images are not included as Multimedia objects.
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

$requestedCity = str_replace(@"\'", "'", safe_POST('city'));
if(!$requestedCity) $requestedCity = str_replace(@"\'", "'", rawurldecode(safe_GET('city')));
$requestedCertif = str_replace(@"\'", "'", rawurldecode(safe_GET('certif')));

// check if the page can be displayed
if(get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE) < WT_USER_ACCESS_LEVEL){
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.'login.php');
	exit;
}

// -- print html header information
print_header(WT_I18N::translate('Certificates'));

echo '<div class="center"><h2>', WT_I18N::translate('Certificates'), '</h2>';

// Get Javascript variables from lb_config.php ---------------------------
if (WT_USE_LIGHTBOX) {
	require WT_ROOT.WT_MODULES_DIR.'lightbox/lb_defaultconfig.php';
	require WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
}

$tabCities=WT_Perso_Functions_Certificates::getCitiesList();

echo '<form method="post" name="selcity" action="module.php?mod=perso_certificates&mod_action=certificatelist">',
	'<select name="city">';
foreach($tabCities as $cities){
	$selectedCity="";
	if($requestedCity && trim($cities)==trim($requestedCity)) $selectedCity='selected="true"';
	echo '<option value="'.$cities.'" '.$selectedCity.' />'.$cities.'</option>';
}
echo '</select>',
	'<input type="submit" value="'.WT_I18N::translate('Show').'" />',
	'</form>';

if($requestedCity){
	if(!$requestedCertif){
		$tabCertifs=WT_Perso_Functions_Certificates::getCertificatesList($requestedCity);
		echo '<h4>',$requestedCity,'</h4><table class="center">';
		foreach($tabCertifs as $tabCertif){
			echo  '<tr>',
					'<td class="list_value">',$tabCertif[1],'</td>',
					'<td class="list_value">',$tabCertif[2],'</td>',
					'<td class="list_value"><a href="module.php?mod=perso_certificates&mod_action=certificatelist&city=',rawurlencode($requestedCity),'&certif=',rawurlencode($tabCertif[0]),'">',$tabCertif[3],'</a></td>',
				'</tr>';
		}
		echo '</table>';
	}
	else{
		$ct=preg_match("/(.*).(jpg|jpeg|png|gif)/", $requestedCertif, $match);
		if($ct>0){
			$certificate = $match[1];
			echo '<h4>',$certificate,'</h4>';
			$certdir = WT_Perso_Functions_Certificates::getPublicCertificatesDirectory();
			$pathCertif=$certdir.rawurlencode($requestedCity).'/'.rawurlencode($requestedCertif);
			echo '<a href="',$pathCertif,'" title="'.$requestedCertif.'"',
				' rel="clearbox[certificate]"',
				' rev="PC::'.$requestedCity.'::'.$requestedCertif.'::" >',
				'<img src="',$pathCertif,'" class="certif_image" alt="',$certificate,'" title="',$certificate,'" /></a>';
		}

		$tabIndi=WT_Perso_Functions_Certificates::getLinkedIndividuals($requestedCity.'/'.$requestedCertif);
		if(count($tabIndi)>0) print_indi_table($tabIndi, WT_I18N::translate('Individuals linked to this certificate'));
		
		$tabFam=WT_Perso_Functions_Certificates::getLinkedFamilies($requestedCity.'/'.$requestedCertif);
		if(count($tabFam)>0) print_fam_table($tabFam, WT_I18N::translate('Families linked to this certificate'));

	}
}

echo '</div>';

print_footer();

?>