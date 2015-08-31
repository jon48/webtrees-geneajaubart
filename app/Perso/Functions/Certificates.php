<?php
/**
 * Additional functions for certificates (based on certificates module)
 *
 * @package webtrees
 * @subpackage PersoLibrary
 * @author Jonathan Jaubart <dev@jaubart.com>
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
		$module = new perso_certificates_WT_Module();
		$cert_rootdir = $module->getSetting('PC_CERT_ROOTDIR', 'certificates/');
		return WT_DATA_DIR.$cert_rootdir;
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
				if($entry!='.' && $entry!='..' && is_dir($certdir.$entry)){
					$_citiesList[]=WT_Perso_Functions::encodeFileSystemToUtf8($entry);
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
	
		$selCity = WT_Perso_Functions::encodeUtf8ToFileSystem($selCity);
		
		$certdir = self::getRealCertificatesDirectory();
		$tabCertif= array();
	
		if(is_dir($certdir.$selCity)){
			$dir=opendir($certdir.$selCity);
			while($entry = readdir($dir)){
				if($entry!='.' && $entry!='..' && !is_dir($certdir.$entry.'/')){
					$path = WT_Perso_Functions::encodeFileSystemToUtf8($selCity.'/'.$entry);
					$certificate = new WT_Perso_Certificate($path);
					if(self::isImageTypeSupported($certificate->extension())){
						//if($certificate->canDisplayDetails())
						$tabCertif[] = 	$certificate;
					}
				}
			}	
		}
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
		$dirPath=$certdir = self::getRealCertificatesDirectory().WT_Perso_Functions::encodeUtf8ToFileSystem($city).'/';
		$contains = utf8_decode($contains);
		$nbCert = 0;
		
		if(is_dir($dirPath)){
			$dir=opendir($dirPath);
			while(($entry = readdir($dir)) && $nbCert < $limit){
				if($entry!='.' && $entry!='..' && $entry!='Thumbs.db' &&!is_dir($dirPath.$entry.'/') && stripos($entry, $contains)!== false){
					$tabFiles[]=WT_Perso_Functions::encodeFileSystemToUtf8($entry);
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
	 * Returns whether the image type is supported by the system, and if so, return the standardised type
	 *
	 * @param string $reqtype Extension to test
	 * @return boolean|string Is supported?
	 */
	public static function isImageTypeSupported($reqtype) {
		$supportByGD = array('jpg'=>'jpeg', 'jpeg'=>'jpeg', 'gif'=>'gif', 'png'=>'png');
		$reqtype = strtolower($reqtype);
	
		if (empty($supportByGD[$reqtype])) return false;
		$type = $supportByGD[$reqtype];
	
		if (function_exists('imagecreatefrom'.$type) && function_exists('image'.$type)) return $type;
		// Here we could check for image types that are supported by other than the GD library
		return false;
	}
}

?>