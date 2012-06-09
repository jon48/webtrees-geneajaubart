<?php
/**
 * Additional functions for certificates (based on certificates module)
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

class WT_Perso_Functions_Certificates {
	
	private static $_citiesList = null;	
	
	/**
	 * Returns the certificates directory path as it is really (within the firewall directory).
	 * 
	 * @return string Real certificates directory path
	 */
	public static function getRealCertificatesDirectory(){
				
		$cert_fw_rootdir = get_module_setting('perso_certificates', 'PC_CERT_FW_ROOTDIR', 'data/'); 
		$cert_rootdir = get_module_setting('perso_certificates', 'PC_CERT_ROOTDIR', 'certificates/');
	
		return $cert_fw_rootdir.$cert_rootdir;
	}
	
	/**
	 * Returns the certificates directory path as it appears publicly in the URLs.
	 * 
	 * @return string Public certificates directory path
	 */
	public static function getPublicCertificatesDirectory(){
		
		$cert_rootdir = get_module_setting('perso_certificates', 'PC_CERT_ROOTDIR', 'certificates/');
	
		return WT_MODULES_DIR.'perso_certificates/'.$cert_rootdir;
	}
	
	/**
	 * Returns an array of the folders (cities) in the certificate directory.
	 * Cities name are UTF8 encoded.
	 *
	 * @return array Array of cities name
	 */
	public static function getCitiesList(){
		if(!isset($_citiesList) || is_null($_citiesList)){
			$certdir = self::getRealCertificatesDirectory();
			$_citiesList= array();
		
			$dir=opendir($certdir);
			
			while($entry = readdir($dir)){
				if($entry!='.' && $entry!='..' && is_dir($certdir.$entry.'/')){
					$_citiesList[]=utf8_encode($entry);
				}
			}
			sort($_citiesList);
		}
		return $_citiesList;
	}
	
	/**
	 * Returns the list of available certificates for a specified city.
	 * Format of the list :
	 * < file name , date of the certificate , type of certificate , name of the certificate > 
	 * Data are UTF8 encoded.
	 *
	 * @param string $selCity City to look in
	 * @return array List of certificates
	 */
	public static function getCertificatesList($selCity){
	
		$selCity = utf8_decode($selCity);
		
		$certdir = self::getRealCertificatesDirectory();
		$tabCertif= array();
	
		if(is_dir($certdir.$selCity)){
			$dir=opendir($certdir.$selCity);
			while($entry = readdir($dir)){
				if($entry!='.' && $entry!='..' && !is_dir($certdir.$entry.'/')){
					$fileParts= explode('.', $entry);
					$nb=count($fileParts);
					$ext = $fileParts[$nb-1];
					if(isImageTypeSupported($ext)){
						$date='';
						$type='';
						$desc=$fileParts[$nb-2];
						$i=0;
						while($i<$nb-2){
							$date.=trim($fileParts[$i]).'.';
							$i++;
						}
						$ct=preg_match("/([0-9]*) ([A-Z]{1,2}) (.*)/", $fileParts[$nb-2], $match);
						if($ct>0){
							$date.=trim($match[1]);
							$type=trim($match[2]);
							$desc=trim($match[3]);
						}
						else{
							$ct2=preg_match("/([0-9]*) (.*)/", $fileParts[$nb-2], $match);
							if($ct2>0){
								$date.=trim($match[1]);
								$desc=trim($match[2]);
							}
						}
						$tabCertif[]= array(utf8_encode($entry), utf8_encode($date), utf8_encode($type), utf8_encode($desc));
	
					}
				}
			}
	
		}
	
		sort($tabCertif);
		return $tabCertif;
	}
	
	/**
	 * Return the list of certificates from a city $city and containing the characters $contains
	 *
	 * @param string $city City to search in
	 * @param string $contains Characters to match
	 * @param string $limit Maximum number of results
	 * @return array Array of matching certificates
	 */
	public static function getCertificateListBeginWith($city, $contains, $limit= 9999){
		$tabFiles= array();	
		$dirPath=$certdir = self::getRealCertificatesDirectory().utf8_decode($city).'/';
		$contains = utf8_decode($contains);
		$nbCert = 0;
		
		if(is_dir($dirPath)){
			$dir=opendir($dirPath);
			while(($entry = readdir($dir)) && $nbCert < $limit){
				if($entry!='.' && $entry!='..' && $entry!='Thumbs.db' &&!is_dir($dirPath.$entry.'/') && stripos($entry, $contains)!== false){
					$tabFiles[]=utf8_encode($entry);
					$nbCert++;
				}
			}
		}
		sort($tabFiles);
		return $tabFiles;
	}
	
	/**
	 * Print the list of certificates, to be used by the jQuery module
	 *
	 * @param string $city City to search in
	 * @param string $contains Characters to match
	 */
	public static function printCertificateListBeginWith($city, $contains) {	
		$listCertif=WT_Perso_Functions_Certificates::getCertificateListBeginWith($city, $contains, 10);
		
		echo Zend_Json::encode($listCertif);
	}
	
	/**
	 * Returns the list of individuals linked to a certificate
	 *
	 * @param string $certif Path of the certificate file (as entered in the GEDCOM)
	 * @return array List of individuals
	 */
	public static function getLinkedIndividuals($certif){
		return WT_DB::prepare("SELECT i_id FROM `##individuals` WHERE i_file=? AND i_gedcom LIKE \"%_ACT ".$certif."%\"")
				->execute(array(WT_GED_ID))
				->fetchOneColumn();
	}
	
	/**
	 * Returns the list of families linked to a certificate
	 *
	 * @param string $certif Path of the certificate file (as entered in the GEDCOM)
	 * @return array List of families
	 */
	public static function getLinkedFamilies($certif){	
		return WT_DB::prepare("SELECT f_id FROM `##families` WHERE f_file=? AND f_gedcom LIKE \"%_ACT ".$certif."%\"")
				->execute(array(WT_GED_ID))
				->fetchOneColumn();
	}
	
	
}

?>