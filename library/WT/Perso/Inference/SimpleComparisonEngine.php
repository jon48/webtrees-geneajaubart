<?php
/**
 * Decorator class to extend native Individual class.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Inference_SimpleComparisonEngine implements WT_Perso_Inference_EngineInterface {
		
	const DEFAULT_MINIMUM_PERCENTAGE = 0.6;
	const DEFAULT_MINIMUM_COUNT = 10;
	
	private static $DEFAULT_INFERENCES = array (
		/* Birth place - General */
		array('INDI', 'BIRT:PLAC', 'fat:BIRT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'mot:BIRT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'spo:BIRT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'DEAT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'fat:DEAT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'mot:DEAT:PLAC'),
		array('INDI', 'BIRT:PLAC', 'RESI:PLAC'),
		array('INDI', 'BIRT:PLAC', 'FAMC:RESI:PLAC'),
		array('INDI', 'BIRT:PLAC', 'FAMS:RESI:PLAC'),
		array('INDI', 'BIRT:PLAC', 'FAMC:MARR:PLAC'),
		array('INDI', 'BIRT:PLAC', 'FAMS:MARR:PLAC'),
		/* Birth place - Specifics */
		array('INDI', 'self[SEX=F]:BIRT:PLAC', 'FAMS:MARR:PLAC'),
		array('INDI', 'self[SEX=M]:BIRT:PLAC', 'FAMS:RESI:PLAC'),
		/* Death place */
		array('INDI', 'DEAT:PLAC', 'fat:DEAT:PLAC'),
		array('INDI', 'DEAT:PLAC', 'mot:DEAT:PLAC'),
		array('INDI', 'DEAT:PLAC', 'spo:DEAT:PLAC'),
		array('INDI', 'DEAT:PLAC', 'chi:BIRT:PLAC'),
		array('INDI', 'DEAT:PLAC', 'BIRT:PLAC'),
		array('INDI', 'DEAT:PLAC', 'FAMS:MARR:PLAC'),
		array('INDI', 'DEAT:PLAC', 'RESI:PLAC'),
		array('INDI', 'DEAT:PLAC', 'FAMS:RESI:PLAC'),
		/* Occupation place */
		array('INDI', 'self[SEX=M]:OCCU', 'fat:OCCU'),
		array('INDI', 'self[SEX=F]:OCCU', 'mot:OCCU'),
		/* Marriage place */
		array('FAM', 'MARR:PLAC', 'MARC:PLAC'),
		array('FAM', 'MARR:PLAC', 'hus:BIRT:PLAC'),
		array('FAM', 'MARR:PLAC', 'wif:BIRT:PLAC'),
		array('FAM', 'MARR:PLAC', 'chi:BIRT:PLAC'),
		array('FAM', 'MARR:PLAC', 'hus:DEAT:PLAC'),
		array('FAM', 'MARR:PLAC', 'wif:DEAT:PLAC')
	);
	private static $ENGINES = array();
	
	private $_gedid;
	private $_inferences;
	private $_filteredinferences;
	private $_crawler;
	
	/**
	 * Class constructor (singleton pattern)
	 * 
	 * @param int $gedid GedcomId
	 */
	private function __construct($gedid = WT_GED_ID) {
		$this->_gedid = $gedid;
		$this->_inferences = array();	
		$this->_crawler = WT_Perso_RecordCrawler::getInstance($gedid);
		$this->init();
	}
	
	/**
	 * Initialise the engine, and create the prerequisites if necessary
	 */
	private function init() {
		try {
			WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_inferences/db_schema/SimpleComparison/', 'PINF_SC_SCHEMA_VERSION', 1);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			die($ex);
		}	
				
		if(count($this->getAllInferences()) == 0) {
			foreach(self::$DEFAULT_INFERENCES as $inference) {
				try {
					WT_DB::prepare(
						'INSERT INTO ##pinferences_simplecomp'.
						' (pisc_file, pisc_record_type, pisc_record_value, pisc_rela_value)'.
						' VALUES (?, ?, ?, ?)'
					)->execute(array($this->_gedid, $inference[0], $inference[1], $inference[2]));
				}
				catch (PDOException $ex) { }
			}	
		}
	}
	
	/**
	 * Returns an array of all inferences 
	 * 
	 * @return array List of inferences:
	 */
	private function getAllInferences() {
		if (count($this->_inferences) == 0) {
			$this->_inferences = WT_DB::prepare(
				'SELECT pisc_id, pisc_file, pisc_record_type, pisc_record_value, pisc_rela_value, pisc_matches, pisc_count,'.
				' IF(pisc_count>0, pisc_matches/pisc_count, 0) percent'.
				' FROM ##pinferences_simplecomp'.
				' WHERE pisc_file = ?'.
				' ORDER BY IF(pisc_count>0, pisc_matches/pisc_count, 0) DESC, pisc_count DESC, pisc_record_type, pisc_record_value'
			)->execute(array($this->_gedid))->fetchAll(PDO::FETCH_ASSOC);
		}
		return $this->_inferences;
	}
	
	/**
	 * Returns an array of inferences, filtered by:
	 * 		- Source gedcom record type (INDI, FAM,...)
	 * 		- Target attribute
	 * 		- Minimum values (count, confidence)
	 * 
	 * @param (null|string) $type Source gedcom record type
	 * @param (null|string) $attribute Target attribute
	 * @param boolean $useminvalues Use minimum values
	 */
	private function getFilteredInferences($type = null, $attribute = null, $useminvalues = true) {
		$key = ($type ?: '*').'_'.($attribute ?: '*').'_'.($useminvalues ? 'Y' : 'N');
		if(!isset($this->_filteredinferences[$key])) {
			$res = $this->getAllInferences();
			foreach ($res as $rowid => $row) {
				$removerow = false;
				if($type && $type != $row['pisc_record_type']) $removerow = true;
				if($attribute) {
					// We are checking if the attribute is at the end of the inference source
					if(strlen($attribute) > strlen($row['pisc_record_value'])) { // Check needed for the test to work
						$removerow = true;
					}
					elseif (substr_compare($row['pisc_record_value'], $attribute, -strlen($attribute), strlen($attribute)) !== 0)
						$removerow = true;
				}				
				if($useminvalues &&
					( $row['percent'] < ( get_gedcom_setting($this->_gedid, 'PERSO_PI_SC_MIN_PERCENT') ?: self::DEFAULT_MINIMUM_PERCENTAGE) 
					||$row['pisc_count'] < ( get_gedcom_setting($this->_gedid, 'PERSO_PI_SC_MIN_COUNT') ?: self::DEFAULT_MINIMUM_COUNT))
				) $removerow = true;
				if($removerow) unset($res[$rowid]);
			}
			$this->_filteredinferences[$key] = $res;
		}
		return $this->_filteredinferences[$key];
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public static function getInstance($gedid = WT_GED_ID) {
		if(!isset(self::$ENGINES[$gedid])) {
			self::$ENGINES[$gedid] = new self($gedid);
		}
		return self::$ENGINES[$gedid];
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function getName() {
		return 'SimpleComparisonEngine';
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function getTitle() {
		return WT_I18N::translate('Simple value comparison');
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function engineAction($action) {
		switch($action) {
			case 'ajaxinferencelist':
			case 'ajaxadmindelete';
				$this->$action();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function getInferedValue(WT_GedcomRecord $record, $attribute, $useminvalues = true) {
		if($record) $drecord = new WT_Perso_GedcomRecord($record);
		foreach($this->getFilteredInferences( $drecord->getType(), $attribute, $useminvalues) as $inference) {
			if(strlen($inference['pisc_record_value']) != strlen($attribute)) { // No initial gedcom record
				$srecord = array_unique($this->_crawler->crawl($record, substr($inference['pisc_record_value'], 0, - strlen($attribute) - 1)), SORT_REGULAR);
				if(!(count($srecord) == 1 && $srecord[0] === $record)) continue;
			}
			$target = array_unique ($this->_crawler->crawl($record, $inference['pisc_rela_value']), SORT_REGULAR);
			if(count($target) == 1) return array($target[0], $inference['percent']); // We do not want to decide if there are more than 1 distinct result	
		}
		return null;
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function compute() {
		$inferenceByType = array();
		
		foreach($this->getAllInferences() as $inference){
			if(isset($inferenceByType[$inference['pisc_record_type']])){
				$inferenceByType[$inference['pisc_record_type']][] = $inference;
			}
			else {
				$inferenceByType[$inference['pisc_record_type']] = array($inference);
			}
		}
		
		foreach($inferenceByType as $type => $inferences) {
			$recordlist = WT_Perso_Query_GedcomRecord::gedcomrecords($type, $this->_gedid);
			
			foreach($recordlist as $rid => $rgedcom) {
				$record = WT_GedcomRecord::getInstance($rid, $this->_gedid, $rgedcom);
				foreach($inferences as $infid => $inference){		
					$inferSource = $inference['pisc_record_value'];
					$inferTarget = $inference['pisc_rela_value'];

					$sources = $this->_crawler->crawl($record, $inferSource);
					$targets = $this->_crawler->crawl($record, $inferTarget);
					
					foreach($sources as $source) {
						if(count($targets) > 0) $inferences[$infid]['pisc_count']++;
						foreach($targets as $target) {
							if ($source instanceof WT_Place && $target instanceof WT_Place) {
								if($source->getGedcomName() == $target->getGedcomName()) {
									$inferences[$infid]['pisc_matches']++;
									break;
								}
							}
							elseif ($source instanceof WT_Fact && $target instanceof WT_Fact) {
								if($source->getValue() == $target->getValue()) {
									$inferences[$infid]['pisc_matches']++;
									break;
								}
							}
							else {
								if($source == $target) {
									$inferences[$infid]['pisc_matches']++;
									break;
								}
							}
						}
					}
				}
			}
			
			foreach($inferences as $inference) {
				WT_DB::prepare(
					'UPDATE ##pinferences_simplecomp'.
					' SET pisc_matches = ?, pisc_count = ?'.
					' WHERE pisc_id = ?'
				)->execute(array($inference['pisc_matches'], $inference['pisc_count'], $inference['pisc_id']));
			}
		}
		
		$this->_inferences = array();
		$this->getAllInferences();
	}
	
	// Implement WT_Perso_Inference_EngineInterface
	public function getConfigDisplay() {
		$html = '';
		
		$inf_id = 'ID-PI-SI-'.floor(microtime()*1000000);
		
		$html .= '<div>'.
					'<dt>'. WT_I18N::translate('Minimum percentage').'</dt>'.  // TODO How to manage helptext??
					'<dd>'.WT_Perso_Functions_Edit::edit_module_field_inline(
							'gedcom_setting-PERSO_PI_SC_MIN_PERCENT-'.$this->_gedid, 
							get_gedcom_setting($this->_gedid, 'PERSO_PI_SC_MIN_PERCENT') ?: self::DEFAULT_MINIMUM_PERCENTAGE, 
							null, 
							'perso_inferences'
					).'</dd>'.
					'<dt>'. WT_I18N::translate('Minimum population count').'</dt>'.  // TODO How to manage helptext??
					'<dd>'.WT_Perso_Functions_Edit::edit_module_field_inline(
							'gedcom_setting-PERSO_PI_SC_MIN_COUNT-'.$this->_gedid,
							get_gedcom_setting($this->_gedid, 'PERSO_PI_SC_MIN_COUNT') ?: self::DEFAULT_MINIMUM_COUNT,
							null,
							'perso_inferences'
					).'</dd>'.
				'</div>';
		
		$js = ' function fnDeleteInferenceRow (inferenceid) {
		            $.ajax({ "url": "module.php",
		                "type": "GET",
		                "data": {
							"mod" : "perso_inferences",
							"mod_action": "engineaction",
							"gedid" : gedid,
							"engine" : "'.WT_Filter::escapeJs($this->getName()).'",
							"engineaction" : "ajaxadmindelete",
							"inferenceid": inferenceid },
		                "success": function(response){
							if(response.result == "ok"){
								infSCConfigDatatable.fnReloadAjax();
							}
							alert(response.text);				
						},
		                "dataType": "json",
		                "error": function (response) {
							alert("'.strip_tags(WT_I18N::translate('The inference entry could not be deleted.')).'");		
		                }
		            });
				}
				
				gedid = '.$this->_gedid.';
				
				//Datatable initialisation
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
	
				infSCConfigDatatable = jQuery("#tInfSCConfigTable_'.$inf_id.'").dataTable({
					"sDom": \'<"H"<"dt-clear">ir>t\',
					'.WT_I18N::datatablesI18N().',
					"bJQueryUI": true,
					"aoColumns": [
						/* 0 ID		 			*/ {"bVisible": false},
						/* 1 Source type		*/ {"bSortable": false, "sClass": "center"},
						/* 2 Source attr.		*/ {"bSortable": false, "sClass": "center", "sType": "unicode"},
						/* 3 Comparison attr.	*/ {"bSortable": false, "sClass": "center"},
						/* 4 Matches 			*/ {"bSortable": false, "sClass": "center", "sType": "numeric"},
						/* 5 Count			 	*/ {"bSortable": false, "sClass": "center", "sType": "numeric"},
						/* 6 Percentage		 	*/ {"bSortable": false, "sClass": "center", "iDataSort": 7 },
						/* 7 PERCENTAGE		 	*/ {"bVisible": false, "sType": "numeric"},
						/* 8 <delete> 		 	*/ {"bSortable": false, "sClass": "center"}
					],
					"bAutoWidth": false,
					"iDisplayLength": 1000, 
					// Server side processing
					"bProcessing " : true,
					"bServerSide" : true,
					"sAjaxSource": "module.php",
					"fnServerData": function ( sSource, aoData, fnCallback ) {
						aoData.push({ "name" : "mod", "value": "perso_inferences"});
						aoData.push({ "name" : "mod_action", "value": "engineaction"});
						aoData.push({ "name" : "gedid", "value": gedid});
						aoData.push({ "name" : "engine", "value": "'.WT_Filter::escapeJs($this->getName()).'"});
						aoData.push({ "name" : "engineaction", "value": "ajaxinferencelist"});	
						$.ajax({
							"dataType": "json",
							"url": sSource,
							"data": aoData,
							"success": fnCallback
						});
					}			
				});
		';
		
		$html .= '</div>&nbsp</div>'.
			'<div>'.
				'<table id="tInfSCConfigTable_'.$inf_id.'" class="dtInferenceDatatable">'.
					'<thead>'.
						'<tr>'.
							'<th>ID</th>'.
							'<th>'.WT_I18N::translate('Source record').'</th>'.
							'<th>'.WT_I18N::translate('Source attribute').'</th>'.
							'<th>'.WT_I18N::translate('Comparison attribute').'</th>'.
							'<th>'.WT_I18N::translate('Matches count').'</th>'.
							'<th>'.WT_I18N::translate('Total count').'</th>'.
							'<th>'.WT_I18N::translate('Percentage').'</th>'.
							'<th>PERCENT</th>'.
							'<th>'.WT_I18N::translate('Delete').'</th>'.
						'</tr>'.
					'</thead>'.
				'</table>'.
			'</div>';
		
		$html .= '<script>' . $js . '</script>';
		
		
		return $html;
	}
	
	/**
	 * Returns the list of inferences for the SimpleComparison engine
	 * 
	 * Input parameters - GET :
	 * 	- gedid : gedcom ID to return the inferences for
	 *  - sEcho : datatable server-side processing parameter, must be returned as it
	 * 
	 * JSON format
	 * {
	 * 		iTotalRecords : int - Total number of records,
	 * 		iTotalDisplayRecords : int - Total display number of records,
	 * 		sEcho : string - Parameter received from the request, to be returned,
	 * 		aaData : array
	 * 		{
	 * 			DT_RowID : string - ID for the current row tr,
	 * 			0 : int - Inference record ID,
	 * 			1 : string - Source gedcom record type,
	 * 			2 : string - Source attribute,
	 * 			3 : string - Target attribute,
	 * 			4 : int - Number of matches,
	 * 			5 : int - Count of total population ,
	 * 			6 : string - Probability - for display,
	 * 			7 : float - Probability,
	 * 			8 : string - Delete button
	 * 		}
	 * }
	 * 
	 */
	private function ajaxinferencelist() {
		$sEcho = WT_Filter::getInteger('sEcho');
		$gedid = WT_Filter::getInteger('gedid');
		
		$controller = new WT_Perso_Controller_Json();
			
		$jsonArray = array();
		$jsonArray['iTotalRecords'] = 0;
		$jsonArray['iTotalDisplayRecords'] = 0;
		$jsonArray['sEcho'] = $sEcho;
		$jsonArray['aaData'] = array();
				
		if(WT_Perso_Inference_Helper::isModuleOperational()
		&& $gedid && array_key_exists($gedid, WT_Tree::getIdList())) {
			$controller->requireManagerLogin($gedid);
			
			$inferences = $this->getAllInferences();
						
			$jsonArray['iTotalRecords'] =  count($inferences);
			$jsonArray['iTotalDisplayRecords'] =  count($inferences);

			foreach($inferences as $inference ){
				$row = array();
				$row['DT_RowId'] = $inference['pisc_id'];
				$row[0] = $inference['pisc_id'];
				$row[1] = WT_Gedcom_Tag::getLabel($inference['pisc_record_type']);
				$row[2] = $inference['pisc_record_value'];
				$row[3] = $inference['pisc_rela_value'];
				$row[4] = $inference['pisc_matches'];
				$row[5] = $inference['pisc_count'];
				$row[6] = sprintf('%.2f %%', 100 * $inference['percent']);
				$row[7] = $inference['percent'];
				$row[8] = '<i class="icon-delete" onclick=" if (confirm(\''.
						WT_Filter::escapeHtml(WT_I18N::translate('Are you sure you want to delete this inference?')).
						'\')) { fnDeleteInferenceRow(\''.$inference['pisc_id'].'\'); }"></i>';
				$jsonArray['aaData'][] = $row;
			}
		}
		
		$controller->pageHeader();
		echo Zend_Json::encode($jsonArray);
	}
	
	/**
	 * Delete an inference record, and return the result
	 * 
	 * Input parameters - GET :
	 * 	- gedid : gedcom ID of the inference
	 *  - inferenceid : ID of the inference to delete
	 *  
	 *  JSON format
	 * 	{
	 * 		result 	: string - Result of the deletion ('failure' or 'ok'),
	 * 		text 	: string - Text to display
	 * 	}
	 *  
	 *  Display a text result
	 *
	 */
	private function ajaxadmindelete(){
		$gedid = WT_Filter::getInteger('gedid');
		$id = WT_Filter::getInteger('inferenceid');
	
		$controller = new WT_Perso_Controller_Json();
	
		$result = array(
				'result' => 'failure',
				'text'	=>	WT_I18N::translate('The inference entry could not be deleted.')
		);
	
		if(WT_Perso_Inference_Helper::isModuleOperational()
		&& $gedid && array_key_exists($gedid, WT_Tree::getIdList())) {
			$controller->requireManagerLogin($gedid);
			
			$sql = 'DELETE FROM ##pinferences_simplecomp WHERE pisc_id = ?';
			WT_DB::prepare($sql)->execute(array($id));
				
			$result['result'] = 'ok';
			$result['text'] = WT_I18N::translate('The inference entry has been successfully deleted.');
			AddToLog('Module Inferences '.$this->getName().' : Inference ID "'.$id.'" has been deleted.', 'config');
		}
			
		$controller->pageHeader();
		echo Zend_Json::encode($result);
	}
	
}

?>