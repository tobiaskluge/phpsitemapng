<?php
/**
 * this (stub) class implements some static array helper functions
 * 
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
class ArrayHelper {

	/**
	 * not necessary, static functions only
	 */
    function ArrayHelper() {
    }
    
	/**
	 * returns a string of all entries of given array with given delim 
	 * 
	 * useful for displaying an array - e.g. in an editbar, ... 
	 */
	static function arrToStringReadable($array, $delim) {
		$res = "";
		if (is_array($array)) {
			$i = 0;
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$res .= "$key: ".ArrayHelper::arrToStringReadable($val, $delim);
				} else {
					$res .= "$key: $val";
				}
				if ($i < (count($array) - 1))
					$res .= $delim;
				$i ++;
			}
		}
		return $res;
	}
 	   
}
?>