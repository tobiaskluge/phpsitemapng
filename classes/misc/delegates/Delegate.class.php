<?php
/**
 * this class implements a delegate pattern
 * 
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-08-16
 */
class Delegate {
	/**
	 * instantiate object of class Delegate
	 */
    function Delegate() {
    }
    
    /**
     * initialize the delegate
	 * @param array params list of initialisation parameters
	 * @return boolean true if succeed, false if failed
     */
    function init($params) {
    	
    }
    
    /**
     * invokes the delegate function
     * 
     * @param array params the parameters for the function
     * @return mixed the result of the function
     */
    function fire($params) {
    	
    }
    
    /**
     * clean up
     */
    function tearDown() {
    }
    
}
?>