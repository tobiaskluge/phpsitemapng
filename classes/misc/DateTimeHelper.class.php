<?php
/**
 * this is a helper class for common used date / time functions
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
class DateTimeHelper {

	/**
	 * you shouldn't instantiate this class since this class only provides static functions!
	 */
    function DateTimeHelper() {
    }
    
    /**
	 * return time in iso long format with time and timezone offset for given timestamp
	 */
	/*static*/ function getDateTimeISO($timestamp) {
		return date("Y-m-d\TH:i:s", $timestamp) . substr(date("O"),0,3) . ":" . substr(date("O"),3);
	}
	
	/**
	 * return time in iso long format with date only for given timestamp
	 */
	/*static*/ function getDateTimeISO_short($timestamp) {
		return date("Y-m-d", $timestamp);
	}
	 
	/**
	 * returns current time in milliseconds
	 * 
	 * 
	 * source: http://de2.php.net/microtime
	 */
	/*static*/ function microtime_float(){
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
    
}
?>