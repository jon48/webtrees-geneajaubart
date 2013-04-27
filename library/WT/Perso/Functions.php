<?php
/**
 * General additional functions
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

class WT_Perso_Functions {

	const ENCRYPTION_IV_SIZE = 16;
	
	private static $_isIsSourcedModuleOperational = -1;
	private static $_isUrlAlive = array();
	
	/**
	 * Debug tool: prompt a Javascript pop-up with a text
	 *
	 * @param string $text Text to display
	 */
	static public function promptAlert($text){
		echo '<script>';
		echo 'alert("',htmlspecialchars($text),'")';
		echo '</script>';
	}
	
	/**
	 * Returns the percentage of two numbers
	 *
	 * @param int $num Numerator
	 * @param int $denom Denominator
	 * @return float Percentage
	 */
	public static function getPercentage($num, $denom){
		if($denom!=0){
			return 100 * $num / $denom;
		}
		return 0;
	}
	
	/**
	 * Get width and heigth of an image resized in order fit a target size.
	 *
	 * @param string $file The image to resize
	 * @param int $target	The final max width/height
	 * @return array array of ($width, $height). One of them must be $target
	 */
	static public function getResizedImageSize($file, $target=25){
		list($width, $height, $type, $attr) = getimagesize($file);
		$max = max($width, $height);
		$rapp = $target / $max;
		$width = intval($rapp * $width);
		$height = intval($rapp * $height);
		return array($width, $height);
	}

	/**
	 * Checks if an URL is available.
	 * Useful when disconnected from Internet, or website down.
	 *
	 * @param string $url URL of the website to check
	 * @return boolean Is the URL active ?
	 */
	static public function isUrlAlive($url){
		if(isset($_isUrlAlive[$url])) return true;
		
		$url_ini = $url;		
		$url = @parse_url($url);
		
		if (!$url) {
			return false;
		}
		
		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
		$path = (isset($url['path'])) ? $url['path'] : '';

		if ($path == '')
		{
			$path = '/';
		}

		$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

		if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
		{
			$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
			$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
			if(preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers )){
				$_isUrlAlive[$url_ini] = true;
				return true;
			}
		}
		return false;
		
	}
	
	/**
	 * Checks if a table exist in the DB schema
	 *
	 * @param string $table Name of the table to look for
	 * @return boolean Does the table exist
	 */
	public static function doesTableExist($table) {
		try {
			WT_DB::prepare("SELECT 1 FROM {$table}")->fetchOne();
			return true;
		} catch (PDOException $ex) {
			return false;
		}
	}
	
	/**
	* Return whether the IsSourced module is active or not.
	*
	* @return bool True if module active, false otherwise
	*/
	public static function isIsSourcedModuleOperational(){
		if(self::$_isIsSourcedModuleOperational == -1){
			self::$_isIsSourcedModuleOperational = array_key_exists('perso_issourced', WT_Module::getActiveModules());
		}
		return self::$_isIsSourcedModuleOperational;
	}
	
	/**
	 * Returns a randomy generated token of a given size
	 *
	 * @param int $length Length of the token, default to 32
	 * @return string Random token
	 */
	public static function generateRandomToken($length=32) {
		$chars = str_split('abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
		$len_chars = count($chars);
		$token = '';
		
		for ($i = 0; $i < $length; $i++)
			$token .= $chars[ mt_rand(0, $len_chars - 1) ];
		
		# Number of 32 char chunks
		$chunks = ceil( strlen($token) / 32 );
		$md5token = '';
		
		# Run each chunk through md5
		for ( $i=1; $i<=$chunks; $i++ )
			$md5token .= md5( substr($token, $i * 32 - 32, 32) );
		
			# Trim the token
		return substr($md5token, 0, $length);		
	} 
	
	/**	  
	 * Encrypt a text, and encode it to base64 compatible with URL use
	 * 	(no +, no /, no =)
	 *
	 * @param string $data Text to encrypt
	 * @return string Encrypted and encoded text
	 */
	public static function encryptToSafeBase64($data){
		$key = 'STANDARDKEYIFNOSERVER';
		if($_SERVER['SERVER_NAME'] && $_SERVER['SERVER_SOFTWARE'])
			$key = md5($_SERVER['SERVER_NAME'].$_SERVER['SERVER_SOFTWARE']);
		$iv = mcrypt_create_iv(self::ENCRYPTION_IV_SIZE, MCRYPT_RAND);
		$id = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC,$iv);
		$encrypted = base64_encode($iv.$id);
		// +, / and = are not URL-compatible
		$encrypted = str_replace('+', '-', $encrypted);
		$encrypted = str_replace('/', '_', $encrypted);
		$encrypted = str_replace('=', '*', $encrypted);
		return $encrypted;
	}
	
	/**
	 * Decode and encrypt a text from base64 compatible with URL use
	 *
	 * @param string $encrypted Text to decrypt
	 * @return string Decrypted text
	 */
	public static function decryptFromSafeBase64($encrypted){
		$key = 'STANDARDKEYIFNOSERVER';
		if($_SERVER['SERVER_NAME'] && $_SERVER['SERVER_SOFTWARE'])
			$key = md5($_SERVER['SERVER_NAME'].$_SERVER['SERVER_SOFTWARE']);
		$encrypted = str_replace('-', '+', $encrypted);
		$encrypted = str_replace('_', '/', $encrypted);
		$encrypted = str_replace('*', '=', $encrypted);
		$encrypted = base64_decode($encrypted);
		$iv_dec = substr($encrypted, 0, self::ENCRYPTION_IV_SIZE);
		$encrypted = substr($encrypted, self::ENCRYPTION_IV_SIZE);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv_dec);
		return  preg_replace('~(?:\\000+)$~','',$decrypted);
	}
	
}

?>