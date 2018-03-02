<?php
/**
 * this class handles our settings - like a registry
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

class SettingsHandler {
	// this array contains the settings
	var $settings;
	// this boolean is set to true when session variables are used
	var $useSession;
	// this boolean is set to true when storage into file is used
	var $useSettingsFile;
	var $filename;	

	/**
	 * create an instance of our settings handler
	 * 
	 * @param array request the superglobal request array 
	 * @param string sessionName the name of the session (optional); if empty, no session will be used
	 */
    function SettingsHandler($sessionName = '', $filename = '') {
    	$this->settings = array();
    	
    	// read from settings file
    	if ($filename != '') {
    		$this->filename = $filename;
    		$this->useSettingsFile = true;

    		// if necessary: include settings from file
    		$FILE_SETTINGS = array();
    		@include_once($this->filename);
    		if ($FILE_SETTINGS[PSNG_SETTINGS_USEFILES]) {
    			$this->settings = HelperClass::array_merge_recursive2($this->settings, $FILE_SETTINGS);
    		}    		
    	}
    	
    	// setup session
    	if ($sessionName != '') {
    		$this->useSession = true;
			session_name($sessionName);
			session_start();
			$sessSettings = $_SESSION[PSNG_SETTINGS];
			if (isset($sessSettings) && count($sessSettings) > 0) {
				$this->settings = HelperClass::array_merge_recursive2($this->settings, $sessSettings);
			}
    	}
    }
    
    /**
     * get the value for given settings name
     * 
     * @param string settingsName the name of the settings key
     * @return mixed the value for the given settingsName
     */
    function getValue($settingsName) {
    	if ($settingsName == '') return '';
    	return $this->settings[$settingsName];
    }
    
    /**
     * set the value for a given settingsName to a new value
     * 
     * @param string settingsName the name of the settings key
     * @param mixed newValue the new value that should be stored
     * @return mixed the old value 
     */
    function setValue($settingsName, $newValue) {
    	if ($settingsName == '') return '';
    	$returnValue = $this->settings[$settingsName];
    	$this->settings[$settingsName] = $newValue;
    	return $returnValue;    	
    }
    
    /**
     * remove given setting from settingshandler
     * 
     * @param string settingsName the name of the settings key
     * @return boolean true if unset succeed, false if failed and/or not exists 
     */
    function unsetValue($settingsName) {
    	if ($settingsName == '') return false;
    	if (isset($this->settings[$settingsName])) {
    		unset($this->settings[$settingsName]);
    		return true;
    	}
    	return false;
	}

	/**
	 * shutdown settings handler
	 */
	function tearDown() {
		// store data into session
		$_SESSION[PSNG_SETTINGS] = $this->settings;
		
		// store settings into settings file
		if ($this->filename != '') {
			global $openFile_error;
			
			$file = HelperClass::openFile($this->filename, true);
			if ($file === false) {
				//TODO layout::output $openFile_error;
				echo "<b>Couldn't open file " . $this->filename . " for writing!</b> Please check permissions!<br>\n"; 
				echo "Error: $openFile_error<br>\n";
				return;
			}
			fputs($file, "<?php\n");
if (is_array($this->settings)) {
			foreach ($this->settings as $key => $val) {
				fputs($file, HelperClass::getEntryForKey($key, $val, 'FILE_SETTINGS'));
			}
}
			fputs($file, "?>\n");
			fclose($file);
		}		
	}

/* not available at the moment : */
	/**
	 * change session usage
	 * 
	 * @param boolean flag true to use sessions, false to not use sessions
	 * @return boolean true if succeed, false if failed
	 */
/*	function useSession($flag) {
		if ($this->useSession != $flag) {
			$this->useSession = $flag;
		}
		return true;	
	}
*/
	
	/**
	 * change settings file usage
	 * 
	 * @param boolean flag true to use sessions, false to not use sessions
	 * @param string filename where to store the settings
	 * @return boolean true if succeed, false if failed
	 */
/*	function useFile($flag, $filename) {
		if ($flag == true && $filename == '') return false; // change nothing
		$this->filename = ($flag == false) ? '' : $filename;
		$this->useSettingsFile = $flag;
		return true;		
	}
*/	
}
?>