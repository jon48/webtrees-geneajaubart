<?php
/**
 * Decorator class to extend native Fact class.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Fact {
		
	const MAX_IS_SOURCED_LEVEL = 3;
	
	protected $fact; 
	
	/**
	* Contructor for the decorator
	*
	* @param WT_Fact $fact_in The Fact to extend
	*/
	public function __construct(WT_Fact $fact_in){
		$this->fact = $fact_in;
	}
	
	/**
	* Check if a fact has a date and is sourced
	* Values:
	* 		- 0, if no date is found for the fact
	* 		- -1, if the date is not precise
	* 		- -2, if the date is precise, but no source is found
	* 		- 1, if the date is precise, and a source is found
	* 		- 2, if the date is precise, a source exists, and is supported by a certificate (requires _ACT usage)
	* 		- 3, if the date is precise, a source exists, and the certificate supporting the fact is within an acceptable range of date
	*
	* @return int Level of sources
	*/
	public function isSourced(){
		$isSourced=0;
		$date = $this->fact->getDate(false);
		if($date->JD()>0) {
			$isSourced=-1;
			if($date->qual1=='' && $date->MinJD() == $date->MaxJD()){
				$isSourced=-2;
				$citations = $this->fact->getCitations();
				foreach($citations as $citation){
					$isSourced=max($isSourced, 1);
					if(preg_match('/3 _ACT (.*)/', $citation) ){
 						$isSourced=max($isSourced, 2);
 						preg_match_all("/4 DATE (.*)/", $citation, $datessource, PREG_SET_ORDER);
 						foreach($datessource as $daterec){
 							$datesource = new WT_Date($daterec[1]);
 							if(abs($datesource->JD() - $date->JD()) < 180){
 								$isSourced = max($isSourced, 3); //If this level increases, do not forget to change the constant MAX_IS_SOURCED_LEVEL
 							}
 						}
 					}
				}
			}
		}
		return $isSourced;
	}
	
}

?>