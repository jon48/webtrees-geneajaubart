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
	 * Get the name of the inference engine
	 * 
	 * @return string Name of the inference engine
	 */
	public function getName();
	
	/**
	 * Gets the title of the engine for display purpose
	 * 
	 * @return string Engine title
	 */
	public function getTitle ();
	
	/**
	 * Displays the output linked to the action specified.
	 * 
	 * @param string $action Action page to display
	 */
	public function engineAction($action);
	
	/**
	 * Gets Inferred value, based on the input parameters
	 * If an inference is found, an array is returned, with:
	 * 		- at index 0: value of the inferred value
	 * 		- at index 1: confidence of the inference
	 * 
	 * @param WT_GedcomRecord $record Source gedcom record
	 * @param string $attribute Target value to infer
	 * @param boolean $userestrictions Use restrictions in the inference
	 * @return (array|null) Inferred value, if exists
	 */
	public function getInferredValue(WT_GedcomRecord $record, $attribute, $userestrictions = true);
	
	/**
	 * Compute data required for inference.
	 */
	public function compute();
	
	/**
	 * Return the HTML code for the specific engine configuration.
	 * 
	 * @return string HTML code for configuration
	 */
	public function getConfigDisplay();
	
	/**
	 * Validate the value sent for the setting against specific inference engine rules.
	 * 
	 * Can return either the initial or a modified value,
	 * or the error message 'ERROR_VALIDATION' is the validation fails.
	 * 
	 * @param string $setting Setting name
	 * @param string $value Replacement setting value to be validated.
	 * @return string Result of the validation (value or error message) 
	 */
	public function validateConfigSettings($setting, $value);
	
}


?>