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
require_once WT_ROOT.'includes/functions/functions_print_lists.php';

/**
 * print a sortable table of certificates
 *
 * @param array $certificates contain certificates list.
 * @param string $city Certificate city
 */
function print_certificate_table($certificates, $city) {
	global $SHOW_LAST_CHANGE, $TEXT_DIRECTION, $WT_IMAGES, $SEARCH_SPIDER;

	if (!$certificates) {
		return;
	}
	$table_id = "ID".floor(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	echo WT_JS_START;?>
	jQuery(document).ready(function(){
		jQuery('#<?php echo $table_id; ?>').dataTable( {
			"sDom": '<"H"pf<"dt-clear">irl>t<"F"pl>',
			"oLanguage": {
				"sLengthMenu": '<?php echo /* I18N: Display %s [records per page], %s is a placeholder for listbox containing numeric options */ WT_I18N::translate('Display %s', '<select><option value="10">10<option value="20">20</option><option value="30">30</option><option value="50">50</option><option value="100">100</option><option value="-1">'.WT_I18N::translate('All').'</option></select>'); ?>',
				"sZeroRecords": '<?php echo WT_I18N::translate('No records to display');?>',
				"sInfo": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '_START_', '_END_', '_TOTAL_'); ?>',
				"sInfoEmpty": '<?php echo /* I18N: %s are placeholders for numbers */ WT_I18N::translate('Showing %1$s to %2$s of %3$s', '0', '0', '0'); ?>',
				"sInfoFiltered": '<?php echo /* I18N: %s is a placeholder for a number */ WT_I18N::translate('(filtered from %s total entries)', '_MAX_'); ?>',
				"sProcessing": '<?php echo WT_I18N::translate('Loading...');?>',
				"sSearch": '<?php echo WT_I18N::translate('Filter');?>',				"oPaginate": {
					"sFirst":    '<?php echo /* I18N: button label, first page    */ WT_I18N::translate('first');    ?>',
					"sLast":     '<?php echo /* I18N: button label, last page     */ WT_I18N::translate('last');     ?>',
					"sNext":     '<?php echo /* I18N: button label, next page     */ WT_I18N::translate('next');     ?>',
					"sPrevious": '<?php echo /* I18N: button label, previous page */ WT_I18N::translate('previous'); ?>'
				}
			},
			"bJQueryUI": true,
			"bAutoWidth":false,
			"bProcessing": true,
			"bStateSave": true,
			"aoColumns": [
                    /* 0-Date */  			{ "sWidth": "15%" },
                    /* 1-Type */ 			{ "sWidth": "5%", "bSearchable": false },
                    /* 2-CertificateSort */ { "bVisible" : false },
                    /* 3-Certificate */     { "iDataSort" : 2 }
                ],
            "aaSorting": [[0,'asc'], [2,'asc']],
			"iDisplayLength": 20,
			"sPaginationType": "full_numbers"
	   });
		jQuery(".certificate-list").css('visibility', 'visible');
		jQuery(".loading-image").css('display', 'none');
	});
	<?php echo WT_JS_END;
	//--table wrapper
	echo '<div class="loading-image">&nbsp;</div>';
	echo '<div class="certificate-list">';
	echo '<fieldset><legend>';
	if (isset($WT_IMAGES['certificate-list'])) {
		echo '<img src="'.$WT_IMAGES['certificate-list'].'" alt="" align="middle" /> ';
	}
	if ($city != '') {
		echo $city;
	} else {
		echo WT_I18N::translate('Certificates');
	}
	echo '</legend>';

	//-- table header
	echo '<table id="', $table_id, '"><thead><tr>';
	echo '<th>', WT_I18N::translate('Date'), '</th>';
	echo '<th>', WT_I18N::translate('Type'), '</th>';
	echo '<th>certificatesort</th>';
	echo '<th>', WT_I18N::translate('Certificate'), '</th>';
	echo '</tr></thead>';
	//-- table body
	echo '<tbody>';
	$n=0;
	foreach ($certificates as $certificate) {
		echo '<tr>';
		//-- Certificate date
		$date = $certificate[1];
		echo '<td>', htmlspecialchars($date), '</td>';
		//-- Certificate type
		$type = $certificate[2];
		echo '<td>', htmlspecialchars($type), '</td>';
		//-- Certificate name
		$name = $certificate[3];
		$sortname = "";
		$ct_names=preg_match("/([A-Z]{2,})/", $name, $match);
		if($ct_names>0) $sortname = $match[1].'_';
		$sortname .= $name;
		echo '<td>', htmlspecialchars($sortname), '</td>';
		echo '<td align="', get_align($name), '"><a href="module.php?mod=perso_certificates&mod_action=certificatelist&city=',rawurlencode($city),'&certif=',rawurlencode($certificate[0]),'">',$name,'</a></td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table></fieldset>';
	echo '</div>';
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
		print_certificate_table($tabCertifs, $requestedCity);
	}
	else{
		echo WT_JS_START;
		?>	jQuery(document).ready(function() {
				jQuery("#certificate-tabs").tabs();
				jQuery("#certificate-tabs").css('visibility', 'visible');
			});
		<?php
		echo WT_JS_END;
		
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
			print_indi_table($tabIndi, WT_I18N::translate('Individuals linked to this certificate'));
			echo '</div>'; // close "indi-certificate"
		}
		
		//Populate related families tab
		if(count($tabFam)>0){
			echo '<div id="fam-certificate">';
			print_fam_table($tabFam, WT_I18N::translate('Families linked to this certificate'));
			echo '</div>'; // close "fam-certificate"
		}
		
		echo '</div>'; //close div "certificate-tabs"
		echo '</div>'; //close div "certificate-details"		
		
	}
}

echo '</div>';

print_footer();

?>