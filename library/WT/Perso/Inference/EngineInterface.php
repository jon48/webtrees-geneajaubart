<?php
/**
 * Interface for inferences Engines.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

interface WT_Perso_Inference_EngineInterface {

	/**
	 * Gets the singleton instance of the engine
	 * 
	 * @param int $gedid GedcomId
	 * @return WT_Perso_Inference_Engine Inference engine singleton
	 */
	public static function getInstance ($gedid);
		
	/**
	 * Gets the title of the engine for display purpose
	 * 
	 * @return string Engine title
	 */
	public function getTitle ();
		
	/**
	 * Gets Infered value, based on the input parameters
	 * If an inference is found, an array is returned, with:
	 * 		- at index 0: value of the infered value
	 * 		- at index 1: confidence of the inference
	 * 
	 * @param WT_GedcomRecord $record Source gedcom record
	 * @param string $attribute Target value to infer
	 * @param boolean $userestrictions Use restrictions in the inference
	 * @return (array|null) Infered value, if exists
	 */
	public function getInferedValue(WT_GedcomRecord $record, $attribute, $userestrictions = true);
	
	/**
	 * Compute data required for inference.
	 */
	public function compute();
		
}


?>