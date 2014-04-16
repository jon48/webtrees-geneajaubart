<?php
/**
 * Class to crawl record and facts
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_RecordCrawler {
		
	private static $CRAWLERS = array();
	
	private $_gedid;
	private $_crawledRecord;
	
	/**
	 * Gets the singleton instance of the Record Crawler
	 * 
	 * @param int $ged_id Gedcom Id
	 * @return WT_Perso_RecordCrawler RecordCrawler singleton:
	 */
	public static function getInstance($ged_id = WT_GED_ID) {
		if(!isset(self::$CRAWLERS[$ged_id])) {
			self::$CRAWLERS[$ged_id] = new self($ged_id);
		}
		return self::$CRAWLERS[$ged_id];
	}

	/**
	 * Class constructor (singleton pattern)
	 * 
	 * @param int $ged_id Gedcom Id
	 */
	private function __construct($ged_id = WT_GED_ID) {
		$this->_gedid = $ged_id;
		$this->_crawledRecord = array();
	}

	/**
	 * Helper function to create an array from an element, or add the element if the array already exists.
	 *
	 * @param unknown $element Element to add
	 * @param array $array Array to which the element must be added.
	 */
	private function addElement($element, &$array){
		$array ? (in_array($element, $array) ?: $array[] = $element) : $array = array($element);
	}
	
	/**
	 * Crawls the records and facts.
	 * The first parameter is the source record or fact, the second describe the path to the target. 
	 * 
	 * @param WT_GedcomRecord|WT_Fact|null $object Source object
	 * @param string $relation Description of the target relation
	 * @return array List of target relations found
	 */
	public function crawl($object, $relation) {
		$res = array();
		if($object && strlen($relation) > 0) {
			//If this relation has already been calculated, then return the cached value,
			if(
				$object instanceof WT_GedcomRecord 
				&& isset($this->_crawledRecord[$object->getXref()])
				&& isset($this->_crawledRecord[$object->getXref()][$relation])
			) return $this->_crawledRecord[$object->getXref()][$relation];
			
			$nextitems = explode(':', $relation, 2);
			$nextrelation = '';
			if(count($nextitems) == 2) { list($nextobjectstr, $nextrelation) = $nextitems; }
			else { $nextobjectstr = $nextitems[0]; }
			$nextobjects = $this->getNextRecords($object, $nextobjectstr);
			if(!$nextobjects) {
				$nextobjects = $this->getNextFacts($object, $nextobjectstr);
			}
			if($nextobjects) {
				foreach ($nextobjects as $nextobject) {
					if(strlen($nextrelation) > 0) {
						$res = array_merge($res, $this->crawl($nextobject, $nextrelation));
					}
					else{
						$res[] = $nextobject;
					}
				}
			}
			if( $object instanceof WT_GedcomRecord) {
				if(isset($this->_crawledRecord[$object->getXref()])) {
					$this->_crawledRecord[$object->getXref()][$relation] = $res;
				}
				else{
					$this->_crawledRecord[$object->getXref()] = array( $relation => $res );
				}
			}
			return $res;
		} else {
			return $res;
		}		
	}
	
	/**
	 * Get an array of the next Gedcom records corresponding to the description string
	 * 
	 * @param unknown $object Source object
	 * @param string $nextobjectstr Description to next object
	 * @return array|null Array of next records 
	 */
	private function getNextRecords($object, $nextobjectstr) {
		$nextobjects = null;
		if(preg_match("/^([a-zA-Z]+)(?:\[([a-zA-Z]+)=([a-zA-Z]+)\])?$/", $nextobjectstr, $match)) {
			switch($match[1]) {
				case 'self':
					if($object instanceof WT_GedcomRecord) $nextobjects = array($object);
					break;
				case 'par' :
				case 'fat' :
				case 'mot' :
					if($object instanceof WT_Individual) {
						$cfamily = $object->getPrimaryChildFamily();
						if($cfamily) {
							if(($nextobjectstr == 'par' || $nextobjectstr == 'fat') && $cfamily->getHusband())
								$this->addElement($cfamily->getHusband(), $nextobjects);
							if(($nextobjectstr == 'par' || $nextobjectstr == 'mot') && $cfamily->getWife())
								$this->addElement($cfamily->getWife(), $nextobjects);
						}
					} elseif ($object instanceof WT_Family) {
						if(($nextobjectstr == 'par' || $nextobjectstr == 'fat') && $object->getHusband())
							$this->addElement($object->getHusband(), $nextobjects);
						if(($nextobjectstr == 'par' || $nextobjectstr == 'mot') && $object->getWife())
							$this->addElement($object->getWife(), $nextobjects);
					}
					break;
				case 'sib' :
				case 'bro' :
				case 'sis' :
					if($object instanceof WT_Individual) {
						$cfamily = $object->getPrimaryChildFamily();
						if($cfamily) {
							foreach($cfamily->getChildren() as $child) {
								if($child !== $object) {
									if($nextobjectstr == 'sib') { $this->addElement($child, $nextobjects); }
									elseif ($nextobjectstr == 'bro' && $child->getSex() == 'M') { $this->addElement($child, $nextobjects); }
									elseif ($nextobjectstr == 'sis' && $child->getSex() == 'F') { $this->addElement($child, $nextobjects); }
								}
							}
						}
					}
					break;
				case 'chi' :
				case 'son' :
				case 'dau' :
					if($object instanceof WT_Individual) {
						$sfamilies = $object->getSpouseFamilies();
						foreach($sfamilies as $sfamily) {
							foreach($sfamily->getChildren() as $child) {
								if($nextobjectstr == 'chi') { $this->addElement($child, $nextobjects); }
								elseif ($nextobjectstr == 'son' && $child->getSex() == 'M') { $this->addElement($child, $nextobjects); }
								elseif ($nextobjectstr == 'dau' && $child->getSex() == 'F') { $this->addElement($child, $nextobjects); }
							}
						}
					}
					elseif ($object instanceof WT_Family) {
						foreach($object->getChildren() as $child) {
							if($nextobjectstr == 'chi') { $this->addElement($child, $nextobjects); }
							elseif ($nextobjectstr == 'son' && $child->getSex() == 'M') { $this->addElement($child, $nextobjects); }
							elseif ($nextobjectstr == 'dau' && $child->getSex() == 'F') { $this->addElement($child, $nextobjects); }
						}
					}
					break;
				case 'spo' :
				case 'hus' :
				case 'wif' :
					if($object instanceof WT_Individual) {
						$sfamilies = $object->getSpouseFamilies();
						foreach($sfamilies as $sfamily) {
							if($nextobjectstr == 'spo' && $sfamily->getSpouse($object))
								$this->addElement($sfamily->getSpouse($object), $nextobjects);
						}
					} elseif ($object instanceof WT_Family) {
						if(($nextobjectstr == 'spo' || $nextobjectstr == 'hus') && $object->getHusband())
							$this->addElement($object->getHusband(), $nextobjects);
						if(($nextobjectstr == 'spo' || $nextobjectstr == 'wif') && $object->getWife())
							$this->addElement($object->getWife(), $nextobjects);
					}
					break;
				default:
					//TODO What about sources, notes
					break;
			}
			if(count($match) > 3) {
				foreach($nextobjects as $objectkey => $nextobject) {
					$removeobject = true;
					$facts = $nextobject->getFacts($match[2]);
					foreach ($facts as $fact) {
						if($fact->getValue() == $match[3]) {
							$removeobject = false;
							break;
						}
					}
					if($removeobject) unset($nextobjects[$objectkey]);
				}
			}
		}
		
		return $nextobjects &&  count ($nextobjects ) > 0 ? $nextobjects : null;
	}
	
	/**
	 * Get an array of the next Facts corresponding to the description string
	 *
	 * @param unknown $object Source object
	 * @param string $nextobjectstr Description to next facts
	 * @return array|null Array of next facts
	 */
	private function getNextFacts($object, $nextobjectstr) {
		$nextfacts = null;
		if($object instanceof WT_GedcomRecord){
			$facts = $object->getFacts($nextobjectstr);
			foreach($facts as $fact) {
				$target = $fact->getTarget();
				if($target) {
					$this->addElement($target, $nextfacts);
				}
				else {
					$this->addElement($fact, $nextfacts);
				}
			}
		} elseif ($object instanceof WT_Fact) {
			switch($nextobjectstr) {
				case 'PLAC':
					if(!$object->getPlace()->isEmpty()) $this->addElement($object->getPlace(), $nextfacts);
					break;
				case 'DATE':
					if($object->getDate()->isOK()) $this->addElement($object->getDate(), $nextfacts);
					break;
				case 'SOUR':
					$sources = $object->getCitations();
					foreach ($sources as $source) {
						$this->addElement($source, $nextfacts);
					}
					break;
				case 'NOTE':
					$notes = $object->getNotes();
					foreach($notes as $note) {
						$this->addElement($note, $nextfacts);
					}
					break;
				//TODO Manage more specifically first names and surnames
				default:
					$attribute = $object->getAttribute($nextobjectstr);
					if($attribute) { $this->addElement($attribute, $nextfacts); }
			}
			//TODO Only level 2 attribute are available. Possible to add a case to go further
		}
		return $nextfacts;
	}
	
}

?>