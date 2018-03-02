<?php
 /**
 * this class provides some logging functions like
 * 	=> log info, error, warning
 * all output will be written to the layout class
 * 
 * FIXME rewrite static code and usage of inner object variables!!! Current implementation will cause errors! 
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
 
/* requires ontology file */
require_once(dirname(__FILE__)."/Ontology.php");
/* requires logger class */
require_once(PSNG_CLASS_LOGGER);
 
class Logger {
	var $layout;			/* instance of layout engine class */
    var $settings;			/* instance of settings class */
    
    
    /**
     * create instance of logger class
     */
    function Logger($layoutEngine, $settings) {
    	$this->layout = $layoutEngine;
    }
    
	/**
	 * log info
	 * 
	 */
	static function info($param, $msg = '') {	
		if ($param == "" && $msg == "") return;
			
		if (is_array($param)) {
			$this->layout->addInfo(ArrayHelper::arrToStringReadable($param, "<br>\n"),$msg);
		} else {
			$this->layout->addInfo($param, $msg);
		}	
	}

	/**
	 * log debug
	 * 
	 */
	static function debug($param, $msg = '') {
		global $settings, $LAYOUT;
	
		if ($settings->getValue(PSNG_DEBUG) === true) {
			if ($param == "" && $msg == "")
				return;
			if (is_array($param)) {
				$layout->addDebug(ArrayHelper::arrToStringReadable($param, "<br>\n"),$msg);
			} else {
				$LAYOUT->addDebug($param, $msg);
			}
		}
	}
	
	/**
	 * log warning
	 * 
	 */
	static function warning($param, $msg = '') {	
		if ($param == "" && $msg == "")	return;
		if (is_array($param)) {
			$this->layout->addWarning(ArrayHelper::arrToStringReadable($param, "<br>\n"),$msg);
		} else {
			$this->layout->addWarning($param, $msg);
		}
	}
    
}
?>