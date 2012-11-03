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

global $controller;

require_once WT_ROOT.'includes/functions/functions_print_lists.php';

/**
 * print a sortable table of certificates
 *
 * @param array $certificates contain certificates list.
 * @param string $city Certificate city
 * @return string HTML code for table of certificates
 */
function format_certificate_table($certificates, $city) {
	global $SHOW_LAST_CHANGE, $TEXT_DIRECTION, $SEARCH_SPIDER, $controller;

	if (!$certificates) {
		return;
	}
	$html = '';
	$table_id = "ID".floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
			->addInlineJavascript('
				/* Initialise datatables */
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
				oTable'.$table_id.' = jQuery("#'.$table_id.'").dataTable( {
					"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
					'.WT_I18N::datatablesI18N().',
					"bJQueryUI": true,
					"bAutoWidth":false,
					"bProcessing": true,
					"aoColumns": [
		                    /* 0-Date */  			{ "sWidth": "15%", "sClass": "center" },
		                    /* 1-Type */ 			{ "sWidth": "5%", "bSearchable": false, "sClass": "center"},
		                    /* 2-CertificateSort */ { "sType": "unicode", "bVisible" : false },
		                    /* 3-Certificate */     { "iDataSort" : 2, "sClass": "left" }
		                ],
		            "aaSorting": [[0,"asc"], [2,"asc"]],
					"iDisplayLength": 20,
					"sPaginationType": "full_numbers"
			   });
				jQuery(".certificate-list").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');
	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .=  '<div class="certificate-list">';
	//-- table header
	$html .= '<table id="'.$table_id.'"><thead><tr>';
	$html .= '<th>'.WT_I18N::translate('Date').'</th>';
	$html .= '<th>'.WT_I18N::translate('Type').'</th>';
	$html .= '<th>certificatesort</th>';
	$html .= '<th>'.WT_I18N::translate('Certificate').'</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	foreach ($certificates as $certificate) {
		$html .= '<tr>';
		//-- Certificate date
		$date = $certificate[1];
		$html .= '<td>'.htmlspecialchars($date).'</td>';
		//-- Certificate type
		$type = $certificate[2];
		$html .= '<td>'.htmlspecialchars($type).'</td>';
		//-- Certificate name
		$name = $certificate[3];
		$sortname = "";
		$ct_names=preg_match("/([A-Z]{2,})/", $name, $match);
		if($ct_names>0) $sortname = $match[1].'_';
		$sortname .= $name;
		$html .= '<td>'.htmlspecialchars($sortname).'</td>';
		$html .= '<td><a href="module.php?mod=perso_certificates&mod_action=certificatelist&city='.rawurlencode($city).'&certif='.rawurlencode($certificate[0]).'">'.$name.'</a></td>';
		$html .= '</tr>';
	}
	$html .= '</tbody>';
	$html .= '</table>';
	$html .= '</div>';
	
	return $html;
}

$requestedCity = str_replace(@"\'", "'", safe_POST('city'));
if(!$requestedCity) $requestedCity = str_replace(@"\'", "'", rawurldecode(safe_GET('city')));
$requestedCertif = str_replace(@"\'", "'", rawurldecode(safe_GET('certif')));

// check if the page can be displayed
if(get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE) < WT_USER_ACCESS_LEVEL){
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.'login.php');
	exit;
}

$controller=new WT_Controller_Base();
$controller
	->setPageTitle(WT_I18N::translate('Certificates'))
	->pageHeader();

echo '<div class="pcertif-list-page center"><h2>', WT_I18N::translate('Certificates'), '</h2>';

if (WT_USE_LIGHTBOX) {
	$album = new lightbox_WT_Module();
	$album->getPreLoadContent();
}

$tabCities=WT_Perso_Functions_Certificates::getCitiesList();

echo '<form method="get" name="selcity" action="module.php">',
	'<input type="hidden" name="mod" value="perso_certificates">',
	'<input type="hidden" name="mod_action" value="certificatelist">',
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
		echo format_certificate_table($tabCertifs, $requestedCity);
	}
	else{
		$controller
			->addInlineJavascript('
				jQuery("#certificate-tabs").tabs();
				jQuery("#certificate-tabs").css("visibility", "visible");
			');
		
		//Prepare data for images and linked records
		$certificate = $requestedCertif;
		$ct=preg_match("/(.*).(jpg|jpeg|png|gif)/", $requestedCertif, $match);
		if($ct>0) $certificate = $match[1];;
		$tabIndi=WT_Perso_Functions_Certificates::getLinkedIndividuals($requestedCity.'/'.$requestedCertif);
		$tabFam=WT_Perso_Functions_Certificates::getLinkedFamilies($requestedCity.'/'.$requestedCertif);
		
		echo '<div id="certificate-details">';
		echo '<h4>',$certificate,'</h4>';
		//Construct tabs
		echo '<div id="certificate-tabs">';
		//Populate certificate image
		if($ct>0){
			echo '<div id="certificate-edit">';
			$certdir = WT_Perso_Functions_Certificates::getPublicCertificatesDirectory();
			$pathCertif=$certdir.rawurlencode($requestedCity).'/'.rawurlencode($requestedCertif);
			echo '<a href="',$pathCertif,'" title="'.$requestedCertif.'"',
						' rel="clearbox[certificate]"',
						' rev="PC::'.$requestedCity.'::'.$requestedCertif.'::" >',
						'<img src="',$pathCertif,'" class="certif_image" alt="',$certificate,'" title="',$certificate,'" /></a>';
			echo '</div>'; // close "certificate-edit"
		}
		$hasIndi = (count($tabIndi)>0);
		$hasFam = (count($tabFam)> 0);
		if( $hasIndi || $hasFam ){ // Do not display anything if neither a family nor a individual is related
			echo '<ul>';
			if ($hasIndi) {
				echo '<li><a href="#indi-certificate"><span id="indisource">', WT_I18N::translate('Individuals'), '</span></a></li>';
			}
			if (count($tabFam)) {
				echo '<li><a href="#fam-certificate"><span id="famsource">', WT_I18N::translate('Families'), '</span></a></li>';
			}
			echo '</ul>';
		}

		//Populate related individuals tab
		if(count($tabIndi)>0){
			echo '<div id="indi-certificate">';
			echo format_indi_table($tabIndi, WT_I18N::translate('Individuals linked to this certificate'));
			echo '</div>'; // close "indi-certificate"
		}
		
		//Populate related families tab
		if(count($tabFam)>0){
			echo '<div id="fam-certificate">';
			echo format_fam_table($tabFam, WT_I18N::translate('Families linked to this certificate'));
			echo '</div>'; // close "fam-certificate"
		}
		
		echo '</div>'; //close div "certificate-tabs"
		echo '</div>'; //close div "certificate-details"		
		
	}
}

echo '</div>';

?>