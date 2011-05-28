<?php
/**
 * Decorator class to extend native Person class.
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

class WT_Perso_Person extends WT_Perso_GedcomRecord {

	// Cached results from various functions.
	private $_titles=null;
	private $_unprotectedPrimarySurname = null;
	private $_sosa = null;
	
	/**
	 * Extend WT_Person getInstance, in order to retrieve directly a WT_Perso_Person object 
	 *
	 * @param unknown_type $data Data to identify the individual
	 * @return WT_Perso_Person|null WT_Perso_Person instance
	 */
	public static function getIntance($data){
		$dindi = null;
		$indi = WT_Person::getInstance($data);
		if($indi){
			$dindi = new WT_Perso_Person($indi);
		}
		return $dindi;
	}

	/**
	 * Get an array of the different titles (tag TITL) of an individual
	 *
	 * @return array Array of titles
	 */
	public function getTitles(){
		if(is_null($this->_titles)){
			$this->_titles=array();
			$ct = preg_match_all('/1 TITL (.*)/', $this->gedrec, $match);
			if($ct>0){
				$titles=$match[1];
				foreach($titles as $title){
					$ct2 = preg_match_all('/(.*) (('.get_module_setting('perso_general', 'PG_TITLE_PREFIX', '').')(.*))/', $title, $match2);
					if($ct2>0){
						$this->_titles[$match2[1][0]][]= trim($match2[2][0]);
					}
					else{
						$this->_titles[$title][]="";
					}
				}
			}
		}
		return $this->_titles;
	}

	/**
	 * Returns primary Surname of the individual.
	 * Warning : no check of privacy if done in this function.
	 *
	 * @return string Primary surname
	 */
	public function getUnprotectedPrimarySurname() {
		if(!$this->_unprotectedPrimarySurname){
			$tmp=$this->gedcomrecord->getAllNames();
			$this->_unprotectedPrimarySurname = $tmp[$this->gedcomrecord->getPrimaryName()]['surname'];
		}
		return $this->_unprotectedPrimarySurname;
	}
	
	/**
	 * Returns an estimated birth place based on statistics on the base
	 *
	 * @param boolean $perc Should the coefficient of reliability be returned
	 * @return string Estimated birth place if found, null otherwise
	 */
	public function getEstimatedBirthPlace($perc=false){
		return $this->gedcomrecord->getBirthPlace();
	}
	
	/**
	 * Add Sosa to the list of individual's sosa numbers
	 * Warning: the module cannot handle more than 64 generation (DB restriction)
	 *
	 * @param number $sosa Sosa number to insert
	 */
	public function addSosa($sosa){
		$gen = WT_Perso_Functions_Sosa::getGeneration($sosa);
		if($gen<65){ // The DB table is not able to accept more than 64 generations
			if($this->_sosa){
				$this->_sosa[$sosa] = $gen;
			}
			else{
				$this->_sosa = array($sosa => $gen);
			}
		}
	}
	
	/**
	 * Remove Sosa from the list of individual's sosa numbers
	 *
	 * @param number $sosa Sosa number to remove
	 */
	public function removeSosa($sosa){
		if($this->_sosa && isset($this->_sosa[$sosa])){
			unset($this->_sosa[$sosa]);
		}
	}
	
	/**
	 * Add Sosa to the list of individual's sosa numbers, then add Sosas to his parents, if they exist.
	 * Recursive function.
	 * Require a $tmp_sosatable to store to-be-written Sosas
	 *
	 * @param number $sosa Sosa number to add
	 */
	public function addAndComputeSosa($sosa){
		global $tmp_sosatable;
		
		$this->addSosa($sosa);
	
		$birth_year = $this->gedcomrecord->getEstimatedBirthDate()->gregorianYear();
		$death_year = $this->gedcomrecord->getEstimatedDeathDate()->gregorianYear();
		
		if($this->_sosa && $this->_sosa[$sosa]){
			$tmp_sosatable[] = array($this->gedcomrecord->getXref(), $this->gedcomrecord->getGedId(), $sosa, $this->_sosa[$sosa], $birth_year, $death_year); 
			
			WT_Perso_Functions_Sosa::flushTmpSosaTable();
				
			$fam=$this->gedcomrecord->getPrimaryChildFamily();
			if($fam){
				$husb=$fam->getHusband();
				$wife=$fam->getWife();
				if($husb){
					$dhusb = new WT_Perso_Person($husb);
					$dhusb->addAndComputeSosa(2* $sosa);
				}
				if($wife){
					$dwife = new WT_Perso_Person($wife);
					$dwife->addAndComputeSosa(2* $sosa + 1);
				}
			}
		}
	}
	
	/**
	 * Remove Sosa from the list of individual's sosa numbers, then remove Sosas from his parents, if they exist.
	 * Recursive function.
	 * Require a $tmp_removeSosaTab to store to-be-removed Sosas
	 *
	 * @param number $sosa Sosa number to add
	 */
	public function removeSosas(){
		global $tmp_removeSosaTab;
		
		$sosalist = $this->getSosaNumbers();
		if($sosalist){
			$tmp_removeSosaTab = array_merge($tmp_removeSosaTab, array_keys($sosalist));
			
			WT_Perso_Functions_Sosa::flushTmpRemoveTable();
			
			$fam=$this->gedcomrecord->getPrimaryChildFamily();
			if($fam){
				$husb=$fam->getHusband();
				$wife=$fam->getWife();
				if($husb){
					$dhusb = new WT_Perso_Person($husb);
					$dhusb->removeSosas();
				}
				if($wife){
					$dwife = new WT_Perso_Person($wife);
					$dwife->removeSosas();
				}
			}
		}
		
	}
	
	/**
	 * Return whether an individual is a Sosa or not
	 *
	 * @return boolean Is the individual a Sosa ancestor
	 */
	public function isSosa(){
		if($this->_sosa && count($this->_sosa)>0){
			return true;
		}
		else{
			$this->getSosaNumbers();
			if($this->_sosa && count($this->_sosa)>0){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Get the list of Sosa numbers for this individual
	 * This list is cached.
	 *
	 * @return array|null List of Sosa numbers
	 */
	public function getSosaNumbers(){
		if($this->_sosa){
			return $this->_sosa;
		}
		if(WT_Perso_Functions_Sosa::isModuleOperational()){
			$this->_sosa = WT_DB::prepare('SELECT ps_sosa, ps_gen FROM ##psosa WHERE ps_i_id=? AND ps_file=?')
				->execute(array($this->gedcomrecord->getXref(), $this->gedcomrecord->getGedId()))
				->fetchAssoc();
			return $this->_sosa;
		}
		return null;
	}
	
}

?>