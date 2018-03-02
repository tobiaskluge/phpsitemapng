<?php
/**
 * this class implements a delegate that is used to separate the plugins 
 * 	from the storage object
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
class AddToStorage extends Delegate {
	var $storage; // contains a reference to the storage object
	var $defaultValues;
	/**
	 * instantiate object of class Delegate
	 */
    function AddToStorage() {
    	$this->defaultValues = array();
    }
    
    /**
     * initialize the delegate
	 * @param array params list of initialisation parameters
	 * @return boolean true if succeed, false if failed
     */
    function init($params) {
    	$this->storage = & $params[PSNG_PLUGIN_INIT_STORAGE];
    	$this->defaultValues = $params[PSNG_DELEGATE_DEFAULTPARAMS];
    	return true;
    }
    
    /**
     * invokes the delegate function
     * 
     * @param array params the parameters for the function
     * @return mixed the result of the function
     */
    function fire($params) {
    	// set default parameters
    	foreach ($this->defaultValues as $key => $value) {
    		if (!isset($params[$key])) {
    			$params[$key] = $value;
    		}
    	}
    	// add this entry to storage object
    	return $this->storage->addURL($params);
    }
    
    /**
     * clean up
     */
    function tearDown() {
    	unset($this->storage);
    	$this->storage = null;	
    }
}
?>