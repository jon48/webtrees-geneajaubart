<?php
/**
 * Decorator class to extend native Event class.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Event {
		
	const MAX_IS_SOURCED_LEVEL = 3;
	
	protected $event; 
	
	/**
	* Contructor for the decorator
	*
	* @param WT_Event $event_in The Event to extend
	*/
	public function __construct(WT_Event $event_in){
		$this->event = $event_in;
	}
	
	/**
	* Check if an event has a date and is sourced
	* Values:
	* 		- 0, if no date is found for the event
	* 		- -1, if the date is not precise
	* 		- -2, if the date is precise, but no source is found
	* 		- 1, if the date is precise, and a source is found
	* 		- 2, if the date is precise, a source exists, and is supported by a certificate (requires _ACT usage)
	* 		- 3, if the date is precise, a source exists, and the certificate supporting the event is within an acceptable range of date
	*
	* @return int Level of sources
	*/
	public function isSourced(){
		$isSourced=0;
		$date = $this->event->getDate(false);
		if($date->JD()>0) {
			$isSourced=-1;
			if($date->qual1=='' && $date->MinJD() == $date->MaxJD()){
				$isSourced=-2;
				$srec=$this->event->gedcomRecord;
 				$nbSources = substr_count($srec, '2 SOUR');
				for($i=1;$i<=$nbSources;$i++){
					$isSourced=max($isSourced, 1);
					$source = get_sub_record(2, '2 SOUR',  $srec, $i);
					if(preg_match('/3 _ACT (.*)/', $source) ){
 						$isSourced=max($isSourced, 2);
 						preg_match_all("/4 DATE (.*)/", $source, $datessource, PREG_SET_ORDER);
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