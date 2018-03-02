<?php
/**
 * this class has some static functions that are 
 *   a) in a common interest
 *   b) doesn't fit in another class
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-08-12
 */
 
/* include ontology description */
require_once(dirname(__FILE__)."/../Ontology.php");

class HelperClass {

	/**
	 * not necessary!
	 */
    function HelperClass() {
    	
    }
    
    /**
     * set the settings apropriate to given values from php
     */
    function init(& $settingsHandlerRef) {
    	$settingsHandler =& $settingsHandlerRef;
		// setup url of phpSitemapNG installation
		if ($_SERVER[SCRIPT_URL] != '') {
			$settingsHandler->setValue(PSNG_SCRIPT, $_SERVER['SCRIPT_URL']);
		} elseif ($_SERVER[SCRIPT_NAME] != '') {
			$settingsHandler->setValue(PSNG_SCRIPT, $_SERVER['SCRIPT_NAME']);
		} elseif ($_SERVER[PATH_INFO] != '') {
			$settingsHandler->setValue(PSNG_SCRIPT, $_SERVER['PATH_INFO']);
		} elseif ($_SERVER[ORIG_PATH_INFO] != '') { // only for FastCGI ?
			$settingsHandler->setValue(PSNG_SCRIPT, $_SERVER['ORIG_PATH_INFO']);
		} else {
			error("Couldn't extract script name!");
		}
		$url = $settingsHandler->getValue(PSNG_SCRIPT);
		define("PSNG_URL", substr($url, 0, strrpos($url, '/')));
		
		// setup directories
		$basedir = dirname(__FILE__).'/../../';
		$settingsHandler->setValue(PSNG_DIRECTORY_BASE, $basedir);
		define("PSNG_DIRECTORY", $basedir);
		$settingsHandler->setValue(PSNG_DIRECTORY_CLASSES, $basedir . 'classes/');
		define("PSNG_PATH_DOCUMENTROOT", $_SERVER['DOCUMENT_ROOT']);
    }

	/**
	 * splits a given string at given deliminator
	 * 
	 * @param string str string to split
	 * @param string delim deliminator where to split
	 * 
	 * @return array contains elements
	 */    
    function toArray($str, $delim = "\n") {
		$res = array ();
		$res = explode($delim, $str);
	
		for ($i = 0; $i < count($res); $i ++) {
			$res[$i] = trim($res[$i]);
		}
	
		return $res;
	}
	
	/**
	 * extracts host from given url (value between http:// and first /)
	 *   alternative: use parseurl()
	 * 
	 * sample: url is http://www.enarion.net, result is www.enarion.net
	 * 
	 * @param string url from where the full domain/host name should be extracted
	 * @return string full domain/host name 
	 */
	function extractHost($url) {
		$b = strpos($url, "//");
		if ($b === false) return ""; // does not contain a valid url
		$b = $b + 2;
	    $e = strpos($url, "/", $b);
	    if ($e === false) $e = strlen($url);
	    $host = substr($url, $b, $e-$b);
		return $host;	
	}
	
	/**
	 * extracts domain name and extension for given url
	 * 
	 * sample: url is http://www.enarion.net, result is enarion.net
	 * 
	 * @param string url from where the domain name should be extracted
	 * @return string domain name of the given url 
	 */
	function extractDomain($url) {
		$host = HelperClass::extractHost($url);
		if ($host == "") return ""; // error
		
		$expl = explode(".", $host);
		return $expl[count($expl)-2].".".$expl[count($expl)-1];
	}	
	
	/**
	 * changes str that it can be stored into a file that can be read with
	 * an include() operation
	 * 
	 * @param string str content that should be "encoded" 
	 * @return string "encoded" string
	 */
	function stringToVariableName($str) {
		return ((!get_magic_quotes_gpc()) ? addslashes($str) : $str);
	}
	
	/**
	 * returns a filehandle if file is accessable
	 * an error will be indicated in the global variable $openFile_error
	 * 
	 * @param string filename the name and location of the file that should be opened
	 * @param boolean writable (optional, default: false) true to open the file for write access
	 * @return filehandle of the file if no error occured
	 */
	function openFile($filename, $writable = false) {
		global $openFile_error;
		$openFile_error = "";
		// check if file exists - if yes, perform tests:
		if (file_exists($filename)) {
			// check if file is accessable
			if (!is_readable($filename)) {
				$openFile_error = "File $filename is not readable";
				return false;
			}
			if ($writable && !is_writable($filename)) {
				$openFile_error = "File $filename is not writable";
				return false;
			}
		} else {
			// file does not exist, try to create file
		}		
		$accessLevel = 'r+';
		if ($writable === true) {
			$accessLevel = 'w+';
		}
	
		$filehandle = @fopen($filename, $accessLevel);
		if ($filehandle === FALSE) {
			$openFile_error = "File $filename could not be opened, don't know why";
			@fclose($filehandle);
			
			if (!file_exists($filename)) {
				$openFile_error = "File $filename does not exist and I do not have the rights to create it!";
			}		
			return false;
		}
		return $filehandle;
	}
	
	/**
	 * returns an entry for given key and value that can be stored into settings file
	 * and read with include()
	 * computes result recursive
	 * 
	 * @param string key name of key
	 * @param mixed valu string or array
	 * @return string representation of entry
	 */
	function getEntryForKey($key, $val, $base) {
		if (is_array($val)) {
			// we have to compute result recursive
			$ret = '';
			foreach ($val as $key2 => $val2) {
				$ret .= HelperClass::getEntryForKey($key2, $val2, $base."['" . HelperClass::stringToVariableName($key) . "']");
			}
			return $ret;
		}
		// we're at the end of the (current) recursion, return result
		return '$' . $base . "['" . HelperClass::stringToVariableName($key) . "'] = '" . HelperClass::stringToVariableName($val) . "';\n";
	}
	
	/**
	 * source: http://de2.php.net/manual/en/function.array-merge-recursive.php - brian at vermonster dot com
	 * 
	 * array_merge_recursive2()
	 *
	 * Similar to array_merge_recursive but keyed-valued are always overwritten.
	 * Priority goes to the 2nd array.
	 *
	 * @static yes
	 * @public yes
	 * @param $paArray1 array
	 * @param $paArray2 array
	 * @return array
	 */
	function array_merge_recursive2($paArray1, $paArray2)
	{
	   if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
	   foreach ($paArray2 AS $sKey2 => $sValue2)
	   {
	       $paArray1[$sKey2] = HelperClass::array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
	   }
	   return $paArray1;
	}
	
}
?>