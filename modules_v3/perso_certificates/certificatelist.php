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
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
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
		                    /* 0-Date */  			{ "iDataSort" : 1, "sWidth": "15%", "sClass": "center" },
							/* 1-DateSort */		{ "sType": "unicode", "bVisible" : false },
		                    /* 2-Type */ 			{ "sWidth": "5%", "bSearchable": false, "sClass": "center"},
		                    /* 3-CertificateSort */ { "sType": "unicode", "bVisible" : false },
		                    /* 4-Certificate */     { "iDataSort" : 3, "sClass": "left" }
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
	$html .= '<th>datesort</th>';
	$html .= '<th>'.WT_I18N::translate('Type').'</th>';
	$html .= '<th>certificatesort</th>';
	$html .= '<th>'.WT_I18N::translate('Certificate').'</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	foreach ($certificates as $certificate) {
		$html .= '<tr>';
		//-- Certificate date
		if($date = $certificate->getCertificateDate()) {
			$html .= '<td>'.$date->Display().'</td><td>'.$date->JD().'</td>';
		}
		else{
			$html .= '<td>&nbsp;</td><td>0</td>';
		}
		//-- Certificate type
		$type = $certificate->getCertificateType() ?: '';
		$html .= '<td>'.WT_Filter::escapeHtml($type).'</td>';
		//-- Certificate name
		$name = $certificate->getCertificateDetails() ?: '';
		$sortname = "";
		$ct_names=preg_match("/([A-Z]{2,})/", $name, $match);
		if($ct_names>0) $sortname = $match[1].'_';
		$sortname .= $name;
		$html .= '<td>'.WT_Filter::escapeHtml($sortname).'</td>';
		$html .= '<td><a href="'.$certificate->getHtmlUrl().'">'.WT_Filter::escapeHtml($name).'</a></td>';
		$html .= '</tr>';
	}
	$html .= '</tbody>';
	$html .= '</table>';
	$html .= '</div>';
	
	return $html;
}

$cid = WT_Filter::get('cid');
$city = WT_Filter::get('city');

// check if the page can be displayed
if(get_module_setting($this->getName(), 'PC_SHOW_CERT', WT_PRIV_HIDE) < WT_USER_ACCESS_LEVEL){
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.'login.php');
	exit;
}

$controller=new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate('Certificates'))
	->pageHeader();

if($city && strlen($city) > 22){
	$city = WT_Perso_Functions::decryptFromSafeBase64($city);
}

$certificate = null;
if($cid && strlen($cid) > 22){
	$certificate = WT_Perso_Certificate::getInstance($cid);
	if($certificate) $city = $certificate->getCity() ?: $city;
}

echo '<div class="pcertif-list-page center"><h2>', $controller->getPageTitle(),'</h2>';

$tabCities=WT_Perso_Functions_Certificates::getCitiesList();

echo '<form method="get" name="selcity" action="module.php">',
	'<input type="hidden" name="mod" value="perso_certificates">',
	'<input type="hidden" name="mod_action" value="certificatelist">',
	'<select name="city">';
foreach($tabCities as $cities){
	$selectedCity="";
	if($city && trim($cities)==trim($city)) $selectedCity='selected="true"';
	echo '<option value="'.WT_Perso_Functions::encryptToSafeBase64($cities).'" '.$selectedCity.' />'.$cities.'</option>';
}
echo '</select>',
	'<input type="submit" value="'.WT_I18N::translate('Show').'" />',
	'</form>';

if ($certificate){
	$controller
		->addInlineJavascript('
			jQuery("#certificate-tabs").tabs();
			jQuery("#certificate-tabs").css("visibility", "visible");
		');	
	
	$tabIndi = $certificate->linkedIndividuals();
	$tabFam = $certificate->linkedFamilies();
	
	echo '<div id="certificate-details">';
	echo '<h4>',$certificate->getFullName(),'</h4>';
	//Construct tabs
	echo '<div id="certificate-tabs">';
	//Populate certificate image
	echo '<div id="certificate-edit">';
	echo $certificate->displayImage();		
	echo '</div>'; // close "certificate-edit"
		
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
	if($hasIndi){
		echo '<div id="indi-certificate">';
		echo format_indi_table($tabIndi, WT_I18N::translate('Individuals linked to this certificate'));
		echo '</div>'; // close "indi-certificate"
	}
	
	//Populate related families tab
	if($hasFam){
		echo '<div id="fam-certificate">';
		echo format_fam_table($tabFam, WT_I18N::translate('Families linked to this certificate'));
		echo '</div>'; // close "fam-certificate"
	}
	
	echo '</div>'; //close div "certificate-tabs"
	echo '</div>'; //close div "certificate-details"		
	
}
else if($city){
	$tabCertifs=WT_Perso_Functions_Certificates::getCertificatesList($city);
	echo format_certificate_table($tabCertifs, $city);
}

echo '</div>';

?>