<?php

use WT\Log;
/**
 * Class for managing certificates, extending a WT_Media object.
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Perso_Certificate extends WT_Media {

	const URL_PREFIX  = 'module.php?mod=perso_certificates&mod_action=certificatelist&cid=';
	
	private static $_isModuleOperational = -1;
	
	protected $certType = null;
	protected $certDate = null;
	protected $certDetails = null;
	protected $source = null;
	
	/**
	 * Return whether the Certificate module is active.
	 *
	 * @return bool True if module active, false otherwise
	 */
	public static function isModuleOperational(){
		if(self::$_isModuleOperational == -1){
			self::$_isModuleOperational = array_key_exists('perso_certificates', WT_Module::getActiveModules());
		}
		return self::$_isModuleOperational;
	}
	
	// Extend WT_Media constructor
	public function __construct($data) {
		// Data is only the file name
		$data = str_replace("\\", '/', $data);
		$xref = WT_Perso_Functions::encryptToSafeBase64($data);
		$gedcom = sprintf(
			'0 @%1$s@ OBJE'.PHP_EOL.
			'1 FILE %2$s',
			$xref, $data
		);
		parent::__construct($xref, $gedcom, '', WT_GED_ID);
		
		$this->title = basename($this->file, '.'.$this->extension());
		
		$ct = preg_match("/(?<year>\d{1,4})(\.(?<month>\d{1,2}))?(\.(?<day>\d{1,2}))?( (?<type>[A-Z]{1,2}) )?(?<details>.*)/", $this->title, $match);
		if($ct > 0){
			$monthId = (int) $match['month'];
			$calendarShortMonths = WT_Perso_Functions::getCalendarShortMonths();
			$monthShortName = array_key_exists($monthId, $calendarShortMonths) ? $calendarShortMonths[$monthId] : $monthId;
			$this->certDate = new WT_Date($match['day'].' '.strtoupper($monthShortName).' '.$match['year']);
			$this->certType = $match['type'];
			$this->certDetails = $match['details'];			
		} else {
			$this->certDetails = $this->title;
		}
	}
	
	// Extend class WT_Media
	static public function getInstance($data, $gedcom_id=WT_GED_ID, $gedcom=null) {
		try{
			$certfile = WT_Perso_Functions::decryptFromSafeBase64($data);
			
			//NEED TO CHECK THAT !!!
			if(WT_Perso_Functions::isValidPath($certfile, true)) {
				return new WT_Perso_Certificate($certfile);
			}
		}
		catch (Exception $ex) { 
			Log::addErrorLog('Certificate module error : > '.$ex->getMessage().' < with data > '.$data.' <');
		}	

		return null;
	}
		
	// Extend class WT_Media
	protected function _canDisplayDetailsByType($access_level) {
		$linked_indis = $this->fetchLinkedIndividuals();
		foreach ($linked_indis as $linked_indi) {
			if ($linked_indi && !$linked_indi->canShow($access_level)) {
				return false;
			}
		}
		$linked_fams = $this->fetchLinkedFamilies();
		foreach ($linked_fams as $linked_fam) {
			if ($linked_fam && !$linked_fam->canShow($access_level)) {
				return false;
			}
		}
	}
	
	/**
	 * Define a source associated with the certificate
	 *
	 * @param string|WT_Source $data
	 */
	public function setSource($data){
		if($data instanceof WT_Source){
			$this->source = $data;
		} else {
			$this->source = WT_Source::getInstance($data);
		}
	}
	
	/**
	 * Returns the certificate date
	 *
	 * @return WT_Date Certificate date
	 */
	public function getCertificateDate(){
		return $this->certDate;
	}
	
	/**
	 * Returns the type of certificate
	 *
	 * @return string Certificate date
	 */
	public function getCertificateType(){
		return $this->certType;
	}
	
	/**
	 * Returns the details of the certificate (basename without the date and type)
	 *
	 * @return string Certificate details
	 */
	public function getCertificateDetails(){
		return $this->certDetails;
	}
	
	/**
	 * Return the city the certificate comes from
	 *
	 * @return string|NULL Certificate city
	 */
	public function getCity(){
		$chunks = explode('/', $this->file, 2);
		if(count($chunks) > 1) return $chunks[0];
		return null;
	}
	
	// Extend class WT_Media
	public function getServerFilename($which='main') {
		$filename = WT_Perso_Functions_Certificates::getRealCertificatesDirectory(). $this->file;
		return WT_Perso_Functions::encodeUtf8ToFileSystem($filename);
	}
	
	// Extend class WT_Media
	public function getHtmlUrlDirect($which = 'main', $download = false) {
		$sidstr = ($this->source) ? '&sid='.$this->source->getXref() : '';
		return
			'module.php?mod=perso_certificates&mod_action=certificatefirewall&cid=' . $this->getXref() . $sidstr .
			'&cb=' . $this->getEtag($which);
	}
	
	/**
	 * Returns the watermark text to be displayed.
	 * If a source ID has been provided with the certificate, use this image,
	 * otherwise try to find a linked source within the GEDCOM (the first occurence found is used).
	 * Else a default text is used.
	 *
	 * @return string Watermark text
	 */
	 public function getWatermarkText(){	
	 	$module = new perso_certificates_WT_Module();
		$wmtext = $module->getSetting('PC_WM_DEFAULT', WT_I18N::translate('This image is protected under copyright law.'));
		$sid= WT_Filter::get('sid', WT_REGEX_XREF);	
	
		if($sid){
			$this->source = WT_Source::getInstance($sid);
		}
		else{
			$this->fetchALinkedSource();  // the method already attach the source to the Certificate object;
		}
		
		if($this->source) {
			$wmtext = '&copy;';
			$repofact = $this->source->getFirstFact('REPO');
			if($repofact) {
				$repo = $repofact->getTarget();
				if($repo && $repo instanceof WT_Repository)  $wmtext .= ' '.$repo->getFullName().' - ';
			}
			$wmtext .= $this->source->getFullName();			
		}	
		return $wmtext;
	}
	
	// Extend class WT_Media
	public function displayImage($which = 'main') {
		global $controller;
		
		$js = '	if(isCertifColorboxActive == 0) { 
					activatecertifcolorbox();
					isCertifColorboxActive = 1;
				}
			';
		
		$script = '';
		if($controller && !($controller instanceof WT_Controller_Individual)){
			$controller->addInlineJavascript($js);
		} else {
			$script = '<script>' . $js . '</script>';
		}
		
		if ($which == 'icon' || !file_exists($this->getServerFilename())) {
			// Use an icon
			$image =
			'<i dir="auto" class="icon-perso-certificate margin-h-2"' .
			' title="' . strip_tags($this->getFullName()) . '"' .
			'></i>';
		} else {
			$imgsize = getimagesize($this->getServerFilename());
			$image =
			'<img' .
			' class ="'. 'certif_image'					 	. '"' .
			' dir="'   . 'auto'                           	. '"' . // For the tool-tip
			' src="'   . $this->getHtmlUrlDirect() 			. '"' .
			' alt="'   . strip_tags($this->getFullName()) 	. '"' .
			' title="' . strip_tags($this->getFullName()) 	. '"' .
			$imgsize[3] . // height="yyy" width="xxx"
			'>';
		}	
		return
		'<a' .
		' class="'          . 'certgallery'                          . '"' .
		' href="'           . $this->getHtmlUrlDirect()    		 . '"' .
		' type="'           . $this->mimeType()                  . '"' .
		' data-obje-url="'  . $this->getHtmlUrl()                . '"' .
		' data-title="'     . strip_tags($this->getFullName())   . '"' .
		'>' . $image . '</a>'.$script;
	}
	
	//Extend class WT_GedcomRecord
	public function linkedIndividuals($link = '_ACT'){
		$rows = WT_DB::prepare(
				'SELECT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom'.
				' FROM `##individuals`'.
				' WHERE i_file=? AND i_gedcom LIKE ?')
		->execute(array(WT_GED_ID, '%_ACT '.$this->file.'%'))->fetchAll();
		
		$list = array();
		foreach ($rows as $row) {
			$record = WT_Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
			if ($record->canShowName()) {
				$list[] = $record;
			}
		}
		return $list;
	}

	//Extend class WT_GedcomRecord
	public function linkedFamilies($link = '_ACT'){
		$rows = WT_DB::prepare(
				'SELECT f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom'.
				' FROM `##families`'.
				' WHERE f_file=? AND f_gedcom LIKE ?')
		->execute(array(WT_GED_ID, '%_ACT '.$this->file.'%'))->fetchAll();
		
		$list = array();
		foreach ($rows as $row) {
			$record = WT_Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
			if ($record->canShowName()) {
				$list[] = $record;
			}
		}
		return $list;
	}
	
	/**
	 * Returns a unique source linked to the certificate
	 *
	 * @return WT_Source|NULL Linked source
	 */
	public function fetchALinkedSource(){		
		$sid = null;
		
		// Try to find in individual, then families, then other types of records. We are interested in the first available value.
		$ged =
		WT_DB::prepare(
				'SELECT i_gedcom AS gedrec FROM `##individuals`'.
				' WHERE i_file=? AND i_gedcom LIKE ?')
				->execute(array($this->gedcom_id, '%_ACT '.$this->file.'%'))->fetchOne();
		if(!$ged){
			$ged = WT_DB::prepare(
					'SELECT f_gedcom AS gedrec FROM `##families`'.
					' WHERE f_file=? AND f_gedcom LIKE ?')
					->execute(array($this->gedcom_id, '%_ACT '.$this->file.'%'))->fetchOne();
			if(!$ged){
				$ged = WT_DB::prepare(
						'SELECT o_gedcom AS gedrec FROM `##other`'.
						' WHERE o_file=? AND o_gedcom LIKE ?')
						->execute(array($this->gedcom_id, '%_ACT '.$this->file.'%'))->fetchOne();
			}
		}
		//If a record has been found, parse it to find the source reference.
		if($ged){
			$gedlines = explode("\n", $ged);
			$level = 0;
			$levelsource = -1;
			$sid_tmp=null;
			$sourcefound = false;
			foreach($gedlines as $gedline){
				// Get the level
				if (!$sourcefound && preg_match('~^('.WT_REGEX_INTEGER.') ~', $gedline, $match)) {
					$level = $match[1];
					//If we are not any more within the context of a source, reset
					if($level <= $levelsource){
						$levelsource = -1;
						$sid_tmp = null;
					}
					// If a source, get the level and the reference
					if (preg_match('~^'.$level.' SOUR @('.WT_REGEX_XREF.')@$~', $gedline, $match2)) {
						$levelsource = $level;
						$sid_tmp=$match2[1];
					}
					// If the image has be found, get the source reference and exit.
					if($levelsource>=0 && $sid_tmp && preg_match('~^'.$level.' _ACT '.preg_quote($this->file).'~', $gedline, $match3)){
						$sid = $sid_tmp;
						$sourcefound = true;
					}
				}
			}
		}
		
		if($sid) $this->source = WT_Source::getInstance($sid);
		
		return $this->source;	
	}
		
}

?>