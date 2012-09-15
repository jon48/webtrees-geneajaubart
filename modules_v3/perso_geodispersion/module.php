<?php
/**
 * Class for Perso Geodispersion module.
 * This module is used for displaying the geographical dispersion of Sosa ancestors.
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

// Create tables, if not already present
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_geodispersion/db_schema/', 'PGEODISP_SCHEMA_VERSION', 1);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class perso_geodispersion_WT_Module extends WT_Module implements WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable {

	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Geographical Dispersion');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Display the geographical dispersion of the root person’s Sosa ancestors.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'geodispersion':
				require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
				break;
			case 'ajaxgeodispersiondata':
			case 'ajaxplacehierarchy':
			case 'ajaxadmingethierarchy':
			case 'ajaxadminconfigdata':
			case 'ajaxadminupdate':
			case 'ajaxadminadd':
			case 'ajaxadmindelete':
				$this->$mod_action();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
				'h_config_tab_name' => 40,
				'h_config_tab_content' => 40
		);
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('GeoDispersion'), '</span></a></li>';
	}

	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){
		global $controller;

		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';

		if(WT_Perso_Functions_Sosa::isModuleOperational()){
			$tab_id = 'ID'.floor(microtime()*1000000);
				
			$controller
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.jeditable.min.js')
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.validate.min.js')
			->addExternalJavascript(WT_STATIC_URL.'js/jquery/jquery.dataTables.editable.js')
			->addInlineJavascript('
					function updatePlaceHierarchy(){
					// Create the display array for place hierarchy
						jQuery.get(
							"module.php",
							{ "mod" : "'.$this->getName().'",  "mod_action": "ajaxplacehierarchy", "ged": ged },
							function(data){
								var display = "'.WT_I18N::translate('No place or indication of place structure could be found in your data.').'";
								var newDropDown = "";
								if(data){	    
									hierarchyArray = data.hierarchy;
									if(hierarchyArray && data.nblevels > 0){
										dropdownHierarchyArray = [];		 	
							     		for(i=1;i<=hierarchyArray.length;i++){
							     			dropdownHierarchyArray.push("(" + i + ") " + hierarchyArray[i-1]);
							     			newDropDown = newDropDown + "<option value=\'" + i + "\'>";
							     			if(data.isdefined){
							     				newDropDown = newDropDown + hierarchyArray[i-1];
							     			}
							     			else{
							     				newDropDown = newDropDown + i;
							     			}
							     			newDropDown = newDropDown +"</option>";
							    		 }
							    		 if(data.isdefined){
							    		 	 display = "'.WT_I18N::translate('According to the GEDCOM header, the places within your file follows the structure:').'";
							    		 }
							    		 else{
							    		 	display = "'.WT_I18N::translate('Your GEDCOM header does not contain any indication of place structure.').'<br/>'.WT_I18N::translate('Here is an example of your place data:').'";
							    		 }
							    		 display = display + "<br/>" + dropdownHierarchyArray.join("'.WT_I18N::$list_separator.'");    		
							    		jQuery("#dGeoConfigTable_'.$tab_id.'").show();	    	
									}							    	
								    else{
								    	jQuery("#dGeoConfigTable_'.$tab_id.'").hide();
								    }
							    }
							    jQuery("#dPlaceHierarchy_'.$tab_id.'").html(display);	
							    jQuery("#formAddNewRow_'.$tab_id.' #newsubdiv").html(newDropDown);
							    newDropDown = "<option value=\'-1\'>'.WT_I18N::translate('No map').'</option>" + newDropDown;
							    jQuery("#formAddNewRow_'.$tab_id.' #newtoplevel").html(newDropDown);					    		
							},
							"json"
						);
					}
					
					jQuery(document).ready(function() {		
						ged = "'.WT_GEDCOM.'";
						hierarchyArray = null;
						dropdownHierarchyArray = null;
						geoConfigDatatable = null;
						
						updatePlaceHierarchy();						
						
						//Change behaviour on Gedcom dropdown list
						jQuery("#ddlGedcoms_'.$tab_id.'").change(function() {
							ged = $("#ddlGedcoms_'.$tab_id.' option:selected").text();
							
							jQuery("#formAddNewRow_'.$tab_id.' #ged").val(ged);
							
							// Update place hierarchy
							updatePlaceHierarchy();
														
							// - Refresh data
							geoConfigDatatable.fnDraw();
							
						});	

						//Datatable initialisation
						jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
						jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
			
						geoConfigDatatable = jQuery("#tGeoConfigTable_'.$tab_id.'").dataTable({
							"sDom": \'<"H"pf<"dt-clear">irl>t<"F"p>\',
							'.WT_I18N::datatablesI18N().',
							"bJQueryUI": true,
							"aoColumns": [
								/* 0 ID		 		*/ {"bVisible": false},
								/* 1 Activated 		*/ {"bSortable": false, "sClass": "center"},
								/* 2 Description	*/ {"bSortable": false, "sClass": "center", "sType": "unicode"},
								/* 3 Subdivision	*/ {"bSortable": false, "sClass": "center"},
								/* 4 Map 			*/ {"bSortable": false, "sClass": "center"},
								/* 5 MapTopLevel 	*/ {"bSortable": false, "sClass": "center"},
								/* 6 Use flags	 	*/ {"bSortable": false, "sClass": "center"},
								/* 7 Gen Details 	*/ {"bSortable": false, "sClass": "center"},
							],
							"aaSorting": [[2, "asc"], [3, "asc"]],
							"iDisplayLength": 10,
							"sPaginationType": "full_numbers",
							// Server side processing
							"bProcessing " : true,
							"bServerSide" : true,
							"sAjaxSource": "module.php",
							"fnServerData": function ( sSource, aoData, fnCallback ) {
								aoData.push({ "name" : "mod", "value": "'.$this->getName().'"});
								aoData.push({ "name" : "mod_action", "value": "ajaxadminconfigdata"});
								aoData.push({ "name" : "ged", "value": ged});
								$.ajax({
									"dataType": "json",
									"url": sSource,
									"data": aoData,
									"success": fnCallback
								});
							}
						});
						geoConfigDatatable.makeEditable({
							"sUpdateURL" : "module.php?mod='.$this->getName().'&mod_action=ajaxadminupdate",
							"sAddURL" : "module.php?mod='.$this->getName().'&mod_action=ajaxadminadd",
							"sDeleteURL" : "module.php?mod='.$this->getName().'&mod_action=ajaxadmindelete",
							"aoColumns": [
								/* 0 Activated 		*/ {"tooltip": "'.WT_I18N::translate('Click to enable or disable this analysis').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'",  "data": "{ \'enabled\':\''.WT_I18N::translate('Enabled').'\', \'disabled\':\''.WT_I18N::translate('Disabled').'\' }" },
								/* 1 Description	*/ {"tooltip": "'.WT_I18N::translate('Click to edit the description').'", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'", "type": "textarea"},
								/* 2 Subdivision	*/ {"tooltip": "'.WT_I18N::translate('Click to change the level of subdivision').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'", loadurl : "module.php", loaddata : { "mod" : "'.$this->getName().'", "mod_action" : "ajaxadmingethierarchy", "ged" : function(){return ged}}},
								/* 3 Map 			*/ {"tooltip": "'.WT_I18N::translate('Click to change the map').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'", "data": '.Zend_Json::encode(WT_Perso_Functions_Map::getAvailableGeoDispersionMaps()).'},
								/* 4 MapTopLevel 	*/ {"tooltip": "'.WT_I18N::translate('Click to change the top level of the map').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'", loadurl : "module.php", loaddata : { "mod" : "'.$this->getName().'", "mod_action" : "ajaxadmingethierarchy", "ged" : function(){return ged}, "nomap":1}},
								/* 5 Use flags		*/ {"tooltip": "'.WT_I18N::translate('Click to enable or disable the use of flags').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'",  "data": "{ \'yes\':\''.WT_I18N::translate('yes').'\', \'no\':\''.WT_I18N::translate('no').'\' }" },
								/* 6 Gen Details	*/ {"tooltip": "'.WT_I18N::translate('Click to change the number of places to display in the generation analysis').'", "type": "select", "onblur": "cancel", "submit" : "'.WT_I18N::translate('OK').'",  "data": "{ \'0\':\''.WT_I18N::translate('All').'\', \'1\':\'1\', \'2\':\'2\', \'3\':\'3\', \'4\':\'4\', \'5\':\'5\', \'6\':\'6\', \'7\':\'7\', \'8\':\'8\', \'9\':\'9\', \'10\':\'10\' }" },
							],
							"sAddNewRowButtonId" : "buttonAddRow_'.$tab_id.'",
							"oAddNewRowButtonOptions" : {	
								label: "'.WT_I18N::translate('Add...').'",
								icons: { primary: "ui-icon-plus" } 
							},
							"sDeleteRowButtonId" : "buttonDeleteRow_'.$tab_id.'",
							"oDeleteRowButtonOptions" : {
								label: "'.WT_I18N::translate('Remove').'", 
								icons: { primary:"ui-icon-trash"}
							},
							"fnOnDeleted": function() {
                             	geoConfigDatatable.fnDraw();
                             }, 
							"sAddNewRowFormId" : "formAddNewRow_'.$tab_id.'",
							"sAddNewRowOkButtonId" : "buttonNewRowOK_'.$tab_id.'",
							"sAddNewRowCancelButtonId" : "buttonNewRowCancel_'.$tab_id.'",
							"oAddNewRowFormOptions" : { 	
								title: "'.WT_I18N::translate('Add a new entry').'",
								show: "blind",
								modal: true
							},
							"sAddDeleteToolbarSelector" : ".dataTables_length"
						});			
					});
			
				');

			$gedcoms = get_all_gedcoms();
			echo '<div class="center">';
			echo WT_I18N::translate('Choose tree: ');
			echo '<select id="ddlGedcoms_'.$tab_id.'">';
			foreach ($gedcoms as $gedid => $gedname) {
				echo '<option value='.$gedid;
				if($gedid == WT_GED_ID) echo ' selected=true';
				echo '>'.$gedname.'</option>';
			}
			echo '</select>';
			echo '</div>';
			echo '<div id="dPlaceHierarchy_'.$tab_id.'" class="center"></div>';
			echo '<div id="dGeoConfigTable_'.$tab_id.'" class="center">';
			echo '<form id="formAddNewRow_'.$tab_id.'" action="#" title="'.WT_I18N::translate('Add a new entry').'">',
					'<input type="hidden" name="id" id="newid" value="DATAROWID" rel="0" />',
					'<input type="hidden" name="status" id="newstatus" value="enabled" rel="1" />',
					'<input id="ged" type="hidden" name="ged" value="'.WT_GEDCOM.'">',
					'<label for="name">'.WT_I18N::translate('Description').'</label><br /><input type="text" name="descr" class="required" rel="2" />',
					'<br />',
					'<label for="name">'.WT_I18N::translate('Level of analysis').'</label><br /><select name="subdiv" id="newsubdiv" class="required" rel="3"></select>',
					'<br />',
					'<label for="name">'.WT_I18N::translate('Map').'</label><br /><select name="map" rel="4">';
			foreach(WT_Perso_Functions_Map::getAvailableGeoDispersionMaps() as $mapkey => $mapname){
				echo '<option value="'.$mapkey.'">'.$mapname.'</option>';
			}
			echo '</select>',
					'<br />',
					'<label for="name">'.WT_I18N::translate('Map Top level').'</label><br /><select name="toplevel" id="newtoplevel" rel="5"></select>',
					'<br />',
					'<label for="name">'.WT_I18N::translate('Use Flags').'</label><br /><select name="useflagsgen" rel="6">',
					'<option value="yes">'.WT_I18N::translate('yes').'</option>',
					'<option value="no">'.WT_I18N::translate('no').'</option>',
					'</select>',
					'<br />',
					'<label for="name">'.WT_I18N::translate('Place Details').'</label><br /><select name="detailsgen" rel="7">',
					'<option value="0">'.WT_I18N::translate('All').'</option>',
					'<option value="1">1</option>',
					'<option value="2">2</option>',
					'<option value="3">3</option>',
					'<option value="4">4</option>',
					'<option value="5">5</option>',
					'<option value="6">6</option>',
					'<option value="7">7</option>',
					'<option value="8">8</option>',
					'<option value="9">9</option>',
					'<option value="10">10</option>',
					'</select>',
				'</form>';			
			echo '<table id="tGeoConfigTable_'.$tab_id.'" class="dtGeoConfigTable">',
					'<thead>',
						'<tr>',
							'<th>ID</th>',
							'<th>',WT_I18N::translate('Enabled'),'</th>',
							'<th>',WT_I18N::translate('Description'),'</th>',
							'<th>',WT_I18N::translate('Level of analysis'),'</th>',
							'<th>',WT_I18N::translate('Map'),'</th>',
							'<th>',WT_I18N::translate('Map Top level'),'</th>',
							'<th>',WT_I18N::translate('Use Flags'),'</th>',
							'<th>',WT_I18N::translate('Place Details'),'</th>',
						'</tr>',
					'</thead>',
				'</table>';
			echo '</div>';
		}
		else{
			echo '<p class="center">'.WT_I18N::translate('The Perso Sosa module is required for this module to run. Please activate it.').'</p>';
		}
		echo '</dl></td></tr></table></div>';
	}

	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		return $value;
	}

	/*
	 * AJAX Calls
	 */
	
	/**
	 * Return the HTML code for the general and generations tab of the geodispersion page
	 * 
	 * Input parameters - GET :
	 * 	- geoid : ID of the geodispersion analysis
	 * 
	 * JSON format
	 * 	{
	 * 		generaltab : string - HTML code for the general tab,
	 * 		generationstab : string - HTML code for the generations tab
	 * 	}
	 *
	 */
	private function ajaxgeodispersiondata(){
		
		$geoid = safe_GET('geoid', WT_REGEX_INTEGER, null);
		
		header('Content-Type: text/plain; charset=UTF-8');
		
		$jsonArray = array(
			'generaltab' => '',
			'generationstab' => ''
		);
		
		$parameters = WT_DB::prepare('SELECT pg_file AS gedid, pg_descr AS description, pg_sublevel AS sublevel, pg_map AS map, pg_toplevel AS toplevel, pg_useflagsgen AS useflags, pg_detailsgen AS detailsgen'.
								' FROM ##pgeodispersion'.
								' WHERE pg_id=? AND pg_status=?')
							->execute(array($geoid, 'enabled'))
							->fetchOneRow();
		
		if($geoid && $parameters){
			$toplevelvalue = null;
			if($parameters->map && $parameters->toplevel){				
				$mapSettings = $this->getMapSettings($parameters->map);
				if($mapSettings){
					$toplevelvalue = $mapSettings['toplevel'] ; // get it from the map
				}
				else{
					$toplevelvalue = '*';
				}
			}
			
			// Compute tables for birth places
			list($placesDispGeneral, $placesDispGenerations) = $this->computeDispersionTables(WT_Perso_Functions_Sosa::getAllSosaWithGenerations($parameters->gedid), $parameters->sublevel, $parameters->toplevel, $toplevelvalue);
						
			// Generate the General tab
			$html = '';
			if($placesDispGeneral){
				$nbFound = $placesDispGeneral['knownsum'];
				$nbOther = 0;				
				if(isset($placesDispGeneral['other'])) $nbOther =$placesDispGeneral['other'];
				$nbUnknown = $placesDispGeneral['unknown'];
				$percKnown = WT_Perso_Functions::getPercentage($nbFound - $nbOther, $nbFound + $nbUnknown);
				$html.='<div id="geodispersion_summary">'.
				       '<table class="center">'.
							'<tr>'.
								'<td class="descriptionbox">'.WT_I18N::translate('Places found').'</td>'.
								'<td class="optionbox">'.WT_I18N::translate('%1$d (%2$.0f %%)',$nbFound - $nbOther, $percKnown).'</td>'.
							'</tr>';				
				if($nbOther > 0){
					$percOther = WT_Perso_Functions::getPercentage($nbOther, $nbFound + $nbUnknown);
					$html.=	'<tr>'.
								'<td class="descriptionbox">'.WT_I18N::translate('Other places').'</td>'.
								'<td class="optionbox">'.WT_I18N::translate('%1$d (%2$.0f %%)',$nbOther, $percOther).'</td>'.
							'</tr>';
				}				
				$html.=		'<tr>'.
								'<td class="descriptionbox">'.WT_I18N::translate('Places not found').'</td>'.
								'<td class="optionbox">'.WT_I18N::translate('%1$d (%2$.0f %%)',$nbUnknown, 100 - $percKnown).'</td>'.
							'</tr>'.
						'</table>'.
					'</div>';

				
				// If map, display map
				// Else, display list of places in a table
				$html.='<br/><div id="geodispersion_data">';
				if($parameters->map) {
					if($mapSettings){	
						
						$max = $placesDispGeneral['max'];
						$maxcolor = $mapSettings['canvas']['maxcolor'];
						$hovercolor = $mapSettings['canvas']['hovercolor'];
						foreach($placesDispGeneral as $location => $count){
							$levelvalues = array_reverse(array_map('trim',explode(',', $location)));
							if(isset($mapSettings['subdivisions'][$levelvalues[0]])){								
								$mapSettings['subdivisions'][$levelvalues[0]]['count'] = $count;
								$mapSettings['subdivisions'][$levelvalues[0]]['transparency'] = WT_Perso_Functions::getPercentage($count, $max)/100;
								if($parameters->useflags == 'yes') $mapSettings['subdivisions'][$levelvalues[0]]['flag'] = WT_Perso_Functions_Map::getPlaceIcon(implode(', ', $levelvalues), 50);
							}
						}
						
						$html.= '<script>';	
						$html.= '							
							var tip = null;
							var tipText = "";
							var over = false;
							var isin = false;
							
							function addTip(node, txt){
							    jQuery(node).bind({
							    	mouseover : function(){
							    		oldisin = isin;
							    		isin = true;
							    		if(oldisin != isin){
							       			tipText = txt;
							       			tip.stop(true, true).fadeIn();
							       			over = true;
							       		}
							    	},
							    	mouseout : function(){
							    		oldisin = isin;
							    		isin = false;
							    		if(oldisin != isin){
							       			tip.stop(true, true).fadeOut("fast");
							       			over = false;
							       		}
							    	}
							    	
							    });
							}
							jQuery(document).ready(function() {
								tip = $("#geodispersion_tip").hide();
														
								var positionTab = jQuery("#geodispersion-tabs").position();
							
								jQuery("#geodispersion_map").mousemove(function(e){
								    if (over){
								      tip.css("left", e.pageX + 20 - positionTab.left).css("top", e.pageY + 20 - positionTab.top);
								      tip.html(tipText);
								    }
								});
							
								var paper = new Raphael(document.getElementById("geodispersion_map"), '.$mapSettings['canvas']['width'].', '.$mapSettings['canvas']['height'].');
								var background = paper.rect(0, 0, '.$mapSettings['canvas']['width'].', '.$mapSettings['canvas']['height'].');
								background.attr({"fill" : "'.$mapSettings['canvas']['bgcolor'].'", "stroke" : "'.$mapSettings['canvas']['bgstroke'].'", "stroke-width": 1, "stroke-linejoin": "round" });						
								var attr = { fill: "'.$mapSettings['canvas']['defaultcolor'].'", stroke: "'.$mapSettings['canvas']['defaultstroke'].'", "stroke-width": 1, "stroke-linejoin": "round" };
								var map = {};
						';
						
						foreach($mapSettings['subdivisions'] as $name => $location){
							$html.= 'map.area'.$location['id'].' = paper.path("'.$location['coord'].'").attr(attr);';
							if(isset($location['transparency'])) {
								$textToolTip = '<strong>'.$name.'</strong><br/>';
								if($parameters->useflags == 'yes' && $location['flag'] != '') $textToolTip .= '<span class="geodispersion_flag">'.$location['flag'].'</span><br/>';
								$textToolTip .= WT_I18N::translate('%d individuals', $location['count']).'<br/>'.WT_I18N::translate('%.1f %%', WT_Perso_Functions::getPercentage($location['count'], $nbFound - $nbOther));
								$html.= 'addTip(map.area'.$location['id'].'.node, "'.addslashes($textToolTip).'");';
								$html.= 'map.area'.$location['id'].'.attr({"fill" : "'.$maxcolor.'", "fill-opacity" : '.$location['transparency'].' });';
								$html.= 'map.area'.$location['id'].'.mouseover(function () {'.
											'map.area'.$location['id'].'.stop().animate({"fill" : "'.$mapSettings['canvas']['hovercolor'].'", "fill-opacity" : 1}, 100, "linear");'.
										'});'.
										'map.area'.$location['id'].'.mouseout(function () {'.
											'map.area'.$location['id'].'.stop().animate({"fill" : "'.$maxcolor.'", "fill-opacity" : '.$location['transparency'].'}, 100, "linear");'.
										'});'
								;
							}
						}
						$html .= '});';
						$html.= '</script>';						
						$html.= '<div id="geodispersion_map"></div>';
						$html.= '<div id="geodispersion_tip"></div>';
						
					}
					else{
						$html .= '<p class="warning">'.WT_I18N::translate('The map could not be loaded.').'<p>';
					}
				}
				else{
					$i='1';
					$j='1';
					$previousNb=0;
					$html.='<table class="center">';

					arsort($placesDispGeneral);
					foreach($placesDispGeneral as $place => $nb){
						if($place != 'knownsum' && $place != 'other' && $place != 'unknown' && $place != 'max') {
							$perc = WT_Perso_Functions::getPercentage($nb, $nbFound - $nbOther);
							if($nb!=$previousNb){
								$j=$i;
							}
							else{
								$j='&nbsp;';
							}						
							
							$levels = array_map('trim',explode(',', $place));
							$placename = $levels[($parameters->sublevel)-1];
							if($placename == '' && $parameters->sublevel > 1) $placename = WT_I18N::translate('Unknown (%s)', $levels[($parameters->sublevel)-2]);
							$html.='<tr>'.
										'<td class="descriptionbox"><strong>'.$j.'</strong></td>'.
										'<td class="descriptionbox">'.$placename.'</td>'.
										'<td class="optionbox">'.WT_I18N::translate('%d',$nb).'</td>'.
										'<td class="optionbox">'.WT_I18N::translate('%.1f %%', $perc).'</td>'.
									'</tr>';
							$i++;
							$previousNb=$nb;
						}						
					}
	
					$html.='</table>';
				}
				$html.='</div>';
			}
			else{
				$html = '<p class="warning">'.WT_I18N::translate('No data is available for the general analysis.').'<p>';
			}
			$jsonArray['generaltab'] = $html;
			//print_r($html);
			
			// Generate the Generations tab
			$html = '';
			
			if($placesDispGenerations){
			ksort($placesDispGenerations);
			$html.='<div id="geodispersion_gen">'.
						'<table id="geodispersion_gentable" class="center">';
			foreach($placesDispGenerations as $gen => $genData){
				if(($res = $this->getGenerationPlacesRow($genData, $parameters->sublevel, $parameters->useflags, $parameters->detailsgen)) != ''){
					$countGen = 0;
					$unknownGen = 0;
					if(isset($genData['sum'])) $countGen += $genData['sum'];
					if(isset($genData['other'])) $countGen += $genData['other'];
					if(isset($genData['unknown'])) $unknownGen = $genData['unknown'];
					$html .= '<tr>'.
								'<td class="descriptionbox">'.WT_I18N::translate("Generation %s", $gen);
					if(!is_null($parameters->detailsgen) && $parameters->detailsgen == 0){
						$html .= '<br />';
					}
					else{
						$html .= ' ';
					}
					$html .= 		WT_I18N::translate('(%.1f %%)', WT_Perso_Functions::getPercentage($countGen, $countGen + $unknownGen)).'</td>'.
								'<td class="optionbox left">'.$this->getGenerationPlacesRow($genData, $parameters->sublevel, $parameters->useflags, $parameters->detailsgen).'</td>'.
							'</tr>'
					;
				}
			}
			$html.=		'</table>'.
			            '<div class="left"><strong>'.WT_I18N::translate('Interpretation help:').'</strong><br />'.
			            	WT_I18N::translate('<strong>Generation X (yy %%)</strong>: The percentage indicates the number of found places compared to the total number of ancestors in this generation.').'<br />';
			if(!is_null($parameters->detailsgen) && $parameters->detailsgen == 0){
				$html .= WT_I18N::translate('<strong><em>Place</em> or <em>Flag</em> aa (bb %%)</strong>: The first number indicates the total number of ancestors born in this place, the percentage relates this count to the total number of found places. No percentage means it is less than 10%%.').'<br />';
				$html .= WT_I18N::translate('If any, the darker area indicates the number of unknown places within the generation or places outside the analysed area, and its percentage compared to the number of ancestors. No percentage means it is less than 10%%.');
			}
			else{
				$html .= WT_I18N::translate('<strong><em>Place</em> [aa - bb %%]</strong>: The first number indicates the total number of ancestors born in this place, the percentage compares this count to the total number of found places.').'<br />';
				$html .= WT_I18N::translate('Only the %d more frequent places for each generation are displayed.', $parameters->detailsgen);
			}
			            	'</div>'.
					'</div>';
			}
			else{
				$html = '<p class="warning">'.WT_I18N::translate('No data is available for the generations analysis.').'<p>';
			}
			$jsonArray['generationstab'] = $html;
			//print_r($placesDispGenerations);
		}
		else{
			$jsonArray['generaltab'] = '<p class="warning">'.WT_I18N::translate('The Perso Sosa module must be installed and enabled to display this page.').'<p>';
			$jsonArray['generationstab'] = '<p class="warning">'.WT_I18N::translate('The Perso Sosa module must be installed and enabled to display this page.').'<p>';
		}		
		
		echo Zend_Json::encode($jsonArray);
	}
	
	/**
	 * Return the place hierarchy, as found in the GEDCOM file, in JSON format.
	 * If no structure is defined in the header, the function will look for an example of place within the data.
	 * 
	 * Input parameters - GET :
	 * 	- ged is managed in the session.php
	 * 
	 * JSON format
	 * 	{
	 * 		ged_id : int or null - Gedcom ID,
	 * 		isdefined : bool - Is the place structure defined in  the GEDCOM header,
	 * 		nbLevels : int - Number of levels of the place hierarchy,
	 * 		hierarchy : array - Place hierarchy details
	 * 	}
	 * 
	 * @return string JSON for place hierarchy
	 */
	private function ajaxplacehierarchy(){
		header('Content-Type: text/plain; charset=UTF-8');

		$jsonArray = array();
		$jsonArray['ged_id'] = WT_GED_ID;		// This is already taken care of in the session.php
		$jsonArray['isdefined'] = false;
		$jsonArray['nblevels'] = 0;
		$jsonArray['hierarchy'] = null;
		if($placestructure = WT_Perso_Functions_Map::getPlaceHierarchyHeader(WT_GED_ID)){
			$jsonArray['isdefined'] = true;
			$jsonArray['nblevels'] = count($placestructure);
			$jsonArray['hierarchy'] = $placestructure;
		}
		else{
			list($jsonArray['nblevels'], $jsonArray['hierarchy']) = WT_Perso_Functions_Map::getRandomPlaceExample(WT_GED_ID);
		}
		echo Zend_Json::encode($jsonArray);
	}

	/**
	 * Return the place hierarchy in JSON format, suitable for the jEditable datatable dropdown lists.
	 * 
	 * Input parameters - GET :
	 * 	- ged is managed in the session.php
	 *  - nomap : Should the option No map be displayed in the dropdown lists.
	 * 
	 * JSON format
	 * {
	 * 		-1 : "No map" (optional),
	 * 		$i : Level $i of the hierarchy
	 * }
	 * 
	 * @return string JSON for place hierarchy dropdown lists
	 */
	private function ajaxadmingethierarchy(){
		$nomap = safe_GET_integer('nomap', 0, 1, 0);

		header('Content-Type: text/plain; charset=UTF-8');

		$jsonArray = array();
		if($nomap == 1) $jsonArray[-1] = WT_I18N::translate('No map');
		if($placestructure = WT_Perso_Functions_Map::getPlaceHierarchyHeader(WT_GED_ID)){
			for($i = 1; $i<count($placestructure); $i++){
				$jsonArray[$i] = $placestructure[$i-1];
			}
		}
		else{
			list($nblevels, $placesample) = WT_Perso_Functions_Map::getRandomPlaceExample(WT_GED_ID);
			for($i = 1; $i<$nblevels; $i++){
				$jsonArray[$i] = $i;
			}
		}
		echo Zend_Json::encode($jsonArray);
	}

	/**
	 * Return the data for the geodispersion admin table, in JSON format
	 * 
	 * Input parameters - GET :
	 * 	- ged is managed in the session.php
	 *  - sEcho : datatable server-side processing parameter, must be returned as it
	 *  - iDisplayStart : datatable server-side processing parameter for paging, define the starting index of the list to be returned
	 *  - iDisplayLength : datatable server-side processing parameter for paging, define the length of the list to be returned
	 *  - sSearch : datatable server-side processing parameter for filtering, defines the filter 
	 * 
	 * JSON format
	 * {
	 * 		iTotalRecords : int - Total number of records,
	 * 		iTotalDisplayRecords : int - Total display number of records,
	 * 		sEcho : string - Parameter received from the request, to be returned,
	 * 		aaData : array
	 * 		{
	 * 			DT_RowID : string - ID for the current row tr,
	 * 			0 : int - GeoDispersion record ID,
	 * 			1 : string - Is record enabled,
	 * 			2 : string - Description,
	 * 			3 : int - Level of subdivision to analyse,
	 * 			4 : string or null - Map to display, if any,
	 * 			5 : int - Level of the top level of the map,
	 * 			6 : string - Use flags for display in generational analysis,
	 * 			7 : string or int - Number of levels to display in generational analysis (0=All)
	 * 		}
	 * }
	 * 
	 * @return string JSON for table data
	 */
	private function ajaxadminconfigdata(){
		$sEcho = intval(safe_GET('sEcho', WT_REGEX_INTEGER, null));
		$iDisplayStart = safe_GET('iDisplayStart', WT_REGEX_INTEGER, null);
		$iDisplayLength = safe_GET('iDisplayLength', WT_REGEX_INTEGER, null);
		$sSearch = safe_GET('sSearch');

		header('Content-Type: text/plain; charset=UTF-8');
			
		$jsonArray = array();
		$jsonArray['iTotalRecords'] = 0;
		$jsonArray['iTotalDisplayRecords'] = 0;
		$jsonArray['sEcho'] = $sEcho;
		$jsonArray['aaData'] = array();

		if(WT_USER_IS_ADMIN){
			$sql = 'SELECT COUNT(pg_id) FROM ##pgeodispersion WHERE pg_file=?';
			$nbResults = WT_DB::prepare($sql)->execute(array(WT_GED_ID))->fetchOne(0);
			$jsonArray['iTotalRecords'] =  $nbResults;
			$jsonArray['iTotalDisplayRecords'] =  $nbResults;
			$sql = 'SELECT pg_id, pg_file, pg_descr, pg_sublevel, pg_map, pg_toplevel, pg_status, pg_useflagsgen, pg_detailsgen
					FROM ##pgeodispersion
					WHERE pg_file=?';
				
			//Filtering
			if($sSearch){
				$sql .= ' AND pg_descr LIKE "%'.$sSearch.'%"';
			}
			//Ordering
			$sql .= ' ORDER BY pg_descr ASC, pg_sublevel ASC';
			//Paging
			if(!is_null($iDisplayStart) && !is_null($iDisplayLength)){
				$sql .= ' LIMIT '.$iDisplayStart.', '.$iDisplayLength;
			}
			$aResults = WT_DB::prepare($sql)->execute(array(WT_GED_ID))->fetchAll();
			$placestructure = WT_Perso_Functions_Map::getPlaceHierarchyHeader(WT_GED_ID);
			foreach($aResults as $aResult ){
				$row = array();
				$row['DT_RowId'] = 'row_'.$aResult->pg_id;
				$row[0] = $aResult->pg_id;
				$row[1] = ($aResult->pg_status=="enabled") ? WT_I18N::translate('Enabled') : WT_I18N::translate('Disabled');
				$row[2] = $aResult->pg_descr;
				$row[3] = $placestructure ? $placestructure[$aResult->pg_sublevel-1] : $aResult->pg_sublevel;
				if($aResult->pg_map){
					$row[4] = $aResult->pg_map;
					$row[5] = $placestructure ? $placestructure[$aResult->pg_toplevel-1] : $aResult->pg_toplevel;
				}
				else{
					$row[4] = WT_I18N::translate('No map');
					$row[5] = WT_I18N::translate('No map');
				}
				$row[6] = ($aResult->pg_useflagsgen=="yes") ? WT_I18N::translate('yes') : WT_I18N::translate('no');
				$row[7] = ($aResult->pg_detailsgen==0) ? WT_I18N::translate('All') : $aResult->pg_detailsgen;
				$jsonArray['aaData'][] = $row;
			}
		}

		echo Zend_Json::encode($jsonArray);
	}

	/**
	 * Update a geodispersion analysis entry
	 * 
	 * Input parameters - POST :
	 * 	- id : ID of the Geodispersion analysis
	 *  - value : New value
	 *  - columnId : Column ID of the parameter
	 *  
	 *  Display value updated
	 *
	 */
	private function ajaxadminupdate(){
		$id = safe_POST('id', WT_REGEX_INTEGER, null);
		$value = safe_POST('value');
		$columnId = safe_POST('columnId', WT_REGEX_INTEGER, null) ;

		header('Content-Type: text/plain; charset=UTF-8');
		
		$sentvalue = $value;
		if(WT_USER_IS_ADMIN && $id && !is_null($value)  && $columnId){
			$sql = 'UPDATE ##pgeodispersion';
			$canUpdate = false;
			switch($columnId){
				case 1:
					$sql .= ' SET pg_status = ?'; $canUpdate = true; break;
				case 2:
					$sql .= ' SET pg_descr = ?'; $canUpdate = true; break;
				case 3:
					$sql .= ' SET pg_sublevel = ?'; $canUpdate = true; break;
				case 4:
					$sql .= ' SET pg_map = ?'; 
					if($value == 'nomap') {
						$value = null;
						$sql .= ', pg_toplevel = ?';
					}
					$canUpdate = true; 
					break;
				case 5:
					$sql .= ' SET pg_toplevel = ?'; 
					if($value == -1) {
						$value = null;
						$sql .= ', pg_map = ?';
					}
					$canUpdate = true; 
					break;
				case 6:
					$sql .= ' SET pg_useflagsgen = ?'; $canUpdate = true; break;
				case 7:
					$sql .= ' SET pg_detailsgen = ?'; $canUpdate = true; break;
				default:
					break;
			}
			$sql .= ' WHERE pg_id = ?';
			$params = array($value);
			if(($columnId == 4 && $value == null) || ($columnId == 5 && $value == null)) $params[] = null;
			$params[] = $id;
				
			if($canUpdate) WT_DB::prepare($sql)->execute($params);
			AddToLog('Module '.$this->getName().' : Geo Analysis ID "'.$id.'" - Parameter "'.$columnId.'" set to "'.$value.'"', 'config');
		}
			
		echo $sentvalue;
	}

	/**
	 * Delete a geodispersion analysis
	 * 
	 * Input parameters - POST :
	 * 	- id : ID of the Geodispersion analysis to delete
	 *  
	 *  Display a text result
	 *
	 */
	private function ajaxadmindelete(){
		$id = safe_POST('id', WT_REGEX_INTEGER, null);

		header('Content-Type: text/plain; charset=UTF-8');

		$txtresult = WT_I18N::translate('The Geodispersion analysis entry could not be deleted.');
		
		if(WT_USER_IS_ADMIN && $id){
			$sql = 'DELETE FROM ##pgeodispersion WHERE pg_id = ?';
			WT_DB::prepare($sql)->execute(array($id));
			
			$txtresult = WT_I18N::translate('The Geodispersion analysis entry has been successfully deleted.');
			AddToLog('Module '.$this->getName().' : Geo Analysis ID "'.$id.'" has been deleted.', 'config');
		}
			
		echo $txtresult;
	}

	/**
	 * Delete a geodispersion analysis
	 * 
	 * Input parameters - POST :
	 * 	- descr : Geodispersion analysis description
	 *  - subdiv : Subdivision of analysis
	 *  - map : Map to use
	 *  - toplevel : Map top level subdivision
	 *  - useflagsgen : Use flags
	 *  - detailsgen : Number of place to display
	 *  
	 *  Display the ID of the new Geodispersion analysis entry
	 *
	 */
	private function ajaxadminadd(){
		$descr = safe_POST('descr');
		$subdiv = safe_POST('subdiv', WT_REGEX_INTEGER, null);
		$map = safe_POST('map');
		$toplevel = safe_POST('toplevel', WT_REGEX_INTEGER, null);
		$useflagsgen = safe_POST('useflagsgen');
		$detailsgen = safe_POST('detailsgen', WT_REGEX_INTEGER, null);

		header('Content-Type: text/plain; charset=UTF-8');

		$id = -1;

		if(WT_USER_IS_ADMIN && $descr && $subdiv && $useflagsgen && $detailsgen >= 0){
			$sql = 'INSERT INTO ##pgeodispersion'.
				' (pg_file, pg_descr, pg_sublevel, pg_map, pg_toplevel, pg_status, pg_useflagsgen, pg_detailsgen)'.
				' VALUES (?, ?, ?, ?, ?, "enabled", ?, ?)';
			try{
				WT_DB::getInstance()->beginTransaction();
				if($map == 'nomap' || $toplevel == -1){
					$map = null;
					$toplevel = null;
				}
				WT_DB::prepare($sql)->execute(array(WT_GED_ID, $descr, $subdiv, $map, $toplevel, $useflagsgen, $detailsgen));
				$id = WT_DB::getInstance()->lastInsertId();
				WT_DB::getInstance()->commit();
				AddToLog('Module '.$this->getName().' : Geo Analysis ID "'.$id.'" added with parameters ['.$descr.', '.$subdiv.','.$map.','.$toplevel.','.$useflagsgen.', '.$detailsgen.'].', 'config');
			}
			catch(Exception $e){
				WT_DB::getInstance()->rollback();
				AddToLog('Module '.$this->getName().' : A new Geo Analysis could not be added. See error log.', 'config');
				AddToLog('Module '.$this->getName().' : A new Geo Analysis failed to be added. Parameters ['.$descr.', '.$subdiv.','.$map.','.$toplevel.','.$useflagsgen.', '.$detailsgen.']. Exception '.$e->getMessage(), 'error');
			}
		}

		echo $id;
	}

	/*
	 * GeoDispersion functions
	 */
	
	/**
	 * Return the dispersion analysis tables.
	 * Two arrays are returned : 
	 * 	- the General analysis, which returns the number of ancestors for each place found, plus 4 additional indicators :
	 * 		- knownsum : Number of known places
	 * 		- unknown : Number of unknown places
	 * 		- max : Maximum count of ancestors within a place
	 * 		- other : Other places (not in the top level area)
	 * - the Generations analysis, which returns the number of ancestors for each place found for each generation, plus 3 additional indicators within each generation :
	 * 		- sum : Number of known places
	 * 		- unknown : Number of unknown places
	 * 		- other : Other places (not in the top level area)
	 *
	 * @param array $sosalist List of all sosas
	 * @param int $subdivlevel Level of the subdvision of analysis
	 * @param int $toplevel Level of the top subdvision
	 * @param string $toplevelvalue Value of the top subdivision
	 * @return array Array of the general and generations table 
	 */
	private function computeDispersionTables($sosalist, $subdivlevel, $toplevel, $toplevelvalue){
		$placesDispGeneral = null;
		$placesDispGenerations = null;
		
		if($sosalist && count($sosalist) > 0) {
			$placesDispGeneral['knownsum'] = 0;
			$placesDispGeneral['unknown'] = 0;
			$placesDispGeneral['max'] = 0;
			foreach($sosalist as $sosaid => $gens) {
				$sosa = WT_Perso_Person::getIntance($sosaid);
				$place =$sosa->getEstimatedBirthPlace();
				$genstab = explode(',', $gens); 
				$isUnknown=true;
				if($sosa->getDerivedRecord()->canDisplayDetails() && !is_null($place)){
					$levels = array_reverse(array_map('trim',explode(',', $place)));
					if(count($levels)>= $subdivlevel){
						$toplevelvalues = array_map('trim',explode(',', strtolower($toplevelvalue)));
						if(is_null($toplevel) || $toplevelvalue == '*' || ($toplevel <= $subdivlevel && in_array(strtolower($levels[$toplevel-1]), $toplevelvalues))) {
							$placest = implode(WT_I18N::$list_separator, array_slice($levels, 0, $subdivlevel));
							if(isset($placesDispGeneral[$placest])) {
								$placesDispGeneral[$placest] += 1;
							}
							else { $placesDispGeneral[$placest] = 1;
							}
							if($placesDispGeneral[$placest]>$placesDispGeneral['max']) $placesDispGeneral['max'] = $placesDispGeneral[$placest];
							foreach($genstab as $gen) {
								if(isset($placesDispGenerations[$gen][$placest])) {
									$placesDispGenerations[$gen][$placest] += 1;
								}
								else { $placesDispGenerations[$gen][$placest] = 1;
								}
								if(isset($placesDispGenerations[$gen]['sum'])) {
									$placesDispGenerations[$gen]['sum'] += 1;
								}
								else { $placesDispGenerations[$gen]['sum'] = 1;
								}
							}
						}
						else{
							if(isset($placesDispGeneral['other'])) {
								$placesDispGeneral['other'] += 1;
							}
							else { $placesDispGeneral['other'] = 1;
							}
							foreach($genstab as $gen) {
								if(isset($placesDispGenerations[$gen]['other'])) {
									$placesDispGenerations[$gen]['other'] += 1;
								}
								else { $placesDispGenerations[$gen]['other'] = 1;
								}
							}
						}
						$placesDispGeneral['knownsum'] += 1;
						$isUnknown = false;						
					}
				}
				if($isUnknown){
					$placesDispGeneral['unknown'] += 1;
					foreach($genstab as $gen) {
						if(isset($placesDispGenerations[$gen]['unknown'])) { $placesDispGenerations[$gen]['unknown'] += 1;}
						else { $placesDispGenerations[$gen]['unknown'] = 1; }
					}
				}
			}
		}
		
		return array($placesDispGeneral, $placesDispGenerations);
	}
	
	/**
	 * Return a serialised version of the map settings contained within the XML files
	 * 
	 * Structure :
	 * 	- description : Display name of the map
	 * 	- toplevel : Values of the top level subdivisions (separated by commas, if multiple)
	 * 	- canvas : all settings related to the map canvas.
	 * 		- width : canvas width, in px
	 * 		- height : canvas height, in px
	 * 		- maxcolor : color to identify places with ancestors, RGB hexadecimal
	 * 		- hovercolor : same as previous, color when mouse is hovering the place, RGB hexadecimal
	 * 		- bgcolor : map background color, RGB hexadecimal
	 * 		- bgstroke : map stroke color, RGB hexadecimal
	 * 		- defaultcolor : default color of places, RGB hexadecimal
	 * 		- defaultstroke : default stroke color, RGB hexadecimal
	 * 	- subdvisions : for each subdivision :
	 * 		- <em>name</em>
	 * 			- id : Subdivision id, must be compatible with PHP variable constraints, and unique
	 * 			- coord : SVG description of the subdvision shape
	 *
	 * @param string $xmlPath Path of the XML description, relative to the /maps/ folder
	 * @return array Map settings array
	 */
	private function getMapSettings($xmlPath){
		$mapSettings = null;
		if(file_exists(WT_ROOT.WT_MODULES_DIR.$this->getName().'/maps/'.$xmlPath)){
			$xml = simplexml_load_file(WT_ROOT.WT_MODULES_DIR.$this->getName().'/maps/'.$xmlPath);
			if($xml){
				$mapSettings = array();
				$mapSettings['description'] = trim($xml->displayName);
				$mapSettings['toplevel'] = trim($xml->topLevel);
				$mapSettings['canvas']['width'] = trim($xml->canvas->width);
				$mapSettings['canvas']['height'] = trim($xml->canvas->height);
				$mapSettings['canvas']['maxcolor'] = trim($xml->canvas->maxcolor);
				$mapSettings['canvas']['hovercolor'] = trim($xml->canvas->hovercolor);
				$mapSettings['canvas']['bgcolor'] = trim($xml->canvas->bgcolor);
				$mapSettings['canvas']['bgstroke'] = trim($xml->canvas->bgstroke);
				$mapSettings['canvas']['defaultcolor'] = trim($xml->canvas->defaultcolor);
				$mapSettings['canvas']['defaultstroke'] = trim($xml->canvas->defaultstroke);
				$mapSettings['subdivisions'] = array();
				foreach($xml->subdivisions->children() as $subdivision){
					$attributes = $subdivision->attributes();
					$mapSettings['subdivisions'][trim($attributes['name'])] = array(
										'id' => trim($attributes['id']),
										'coord' => trim($subdivision[0])
					);
				}
			}
		}
		return $mapSettings;
	}
	
	/**
	 * Return the HTML code to display generation data rows.
	 * Different displays are possible, depending on the geodispersion analysis settings:
	 * 	- Either all levels must be displayed (Details levels = 0)
	 * 		- With flags
	 * 		- Or without flags
	 * 	- Or only the first more frequent elements must be displayed
	 *
	 * @param array $data Data array
	 * @param int $sublevel Level of subdivision of analysis 
	 * @param string $useflag Use flags ?
	 * @param int $detailslevel Number of places to display (by frequency order)
	 * @return string HTML code for generation row
	 */
	private function getGenerationPlacesRow($data, $sublevel, $useflag, $detailslevel){
		$html = '';
		arsort($data);
		$sum = 0;
		$other = 0;
		if(isset($data['sum'])) $sum = $data['sum'];
		if(isset($data['other'])) $other = $data['other'];
		if(!is_null($detailslevel) && $detailslevel == 0 && $sum > 0){
			$unknownother = $other;
			if(isset($data['unknown'])) $unknownother += $data['unknown'];
			$html .='<table class="geodispersion_bigrow"><tr>';
			foreach($data as $placename=> $count){
				if($placename!='sum' && $placename!='unknown' && $placename!='other'){
					$levels = array_map('trim',explode(',', $placename));
					$content = '';
					if($useflag == 'yes' && ($flag = WT_Perso_Functions_Map::getPlaceIcon(implode(', ', array_reverse($levels)), 25)) != ''){
						$content .= '<td class="geodispersion_flag">'.$flag.'</td><td>';
					}
					else{
						$content .= '<td><span title="'.implode(WT_I18N::$list_separator, array_reverse($levels)).'">'.$levels[$sublevel-1].'</span><br/>';
					}
					$content .= $count;
					$perc=WT_Perso_Functions::getPercentage($count, $sum + $unknownother);
					$perc2=WT_Perso_Functions::getPercentage($count, $sum);					
					if($perc2>=10) $content.= '<br/><span class="small">('.WT_I18N::translate('%.0f %%', $perc2).')</span>';
					$content .= '</td>';
					
					$html .= '<td class="geodispersion_rowitem" width="'.max(round($perc, 0),1).'%">'.
								'<table><tr>'.
									'<td><table><tr>'.$content.'</tr></table></td>'.
								'</tr></table>'.
							'</td>'
					;
				}
			}
			if($unknownother>0){
				$perc=WT_Perso_Functions::getPercentage($unknownother, $sum + $unknownother);
				$html .='<td class="geodispersion_unknownitem left" >'.
				            $unknownother;
				if($perc>=10) $html.= '<br/><span class="small">('.WT_I18N::translate('%.1f %%', $perc).')</span>';
				$html .='</td>';
			}
			$html .= '</tr></table>';
		}
		else{
			$nbPlaces = 1;
			$placesArray = array();
			reset($data);
			while($nbPlaces <= $detailslevel && key($data) !== null){
				$placename = key($data);
				if($placename != 'sum' && $placename != 'unknown'){
					if($placename != 'other'){
						$levels = array_map('trim',explode(',', $placename));
						$placename = '<span title="'.implode(WT_I18N::$list_separator, array_reverse($levels)).'">'.$levels[$sublevel-1].'</span>';
					}
					else{
						$placename = WT_I18N::translate('Other places');
					}
					$count = current($data);
					$placesArray[] = WT_I18N::translate('<strong>%s</strong> [%d - %0.1f %%]', $placename, $count, WT_Perso_Functions::getPercentage($count, $sum + $other));
					$nbPlaces++;
				}
				next($data);
			}
			$html = implode(WT_I18N::$list_separator, $placesArray);
		}
		return $html;
	}
	
}

?>