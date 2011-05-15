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

	/**
	 * Debug tool: prompt a Javascript pop-up with a text
	 *
	 * @param string $text Text to display
	 */
	static public function promptAlert($text){
		echo WT_JS_START;
		echo 'alert("',htmlspecialchars($text),'")';
		echo WT_JS_END;
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
			return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
		}
		return false;
		
	}
	
}

?>