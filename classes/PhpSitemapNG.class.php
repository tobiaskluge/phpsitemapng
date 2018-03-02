<?php
/**
 * this class handles the main functionality of phpSitemapNG
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
 
/* include ontology description */
require_once(dirname(__FILE__)."/Ontology.php");
require_once(PSNG_CLASS_SETTINGSHANDLER);
require_once(PSNG_CLASS_HELPERCLASS);
require_once(PSNG_CLASS_GUI);
require_once(PSNG_CLASS_STORAGE);
require_once(PSNG_CLASS_STORAGE_SESSION);
 
/* grab ressources */
@set_time_limit(0);
@ini_set('memory_limit', '128M');
@ini_set('allow_url_fopen','1');


class PhpSitemapNG {
	var $settings; 			/* settings object */
	var $gui; 				/* gui object */
	var $storage;			/* storage object */

	/**
	 * create an instance of phpSitemapNG
	 */
    function PhpSitemapNG() {
    	$this->settings =& new SettingsHandler(PSNG_SESSION_NAME, PSNG_SETTINGS_FILE); // create settings object
		HelperClass::init($this->settings);	// set some default settings
    	$this->gui = new Gui($this->settings, $this->storage); // create gui class
    	$this->storage = new StorageSession();	// create storage object (only supported @ the moment: storage in session)
    }
    
    /**
     * handle input that we got from user
     * => select the (gui) function we have to invoke
     * 
     * TODO clean input array
     */
    function handleInput($input) {
//    	$action = 		;
//    	$pluginClass = 	$input[PSNG_PLUGIN_CLASS];
//		$pluginId = 	$input[PSNG_PLUGIN_ID];
    	
    	switch($input[PSNG_ACTION]) {  
    		// handle plugin actions: add, update  		
    		case PSNG_ACTION_PLUGIN_SETUP:
				$pluginSettings = $input[PSNG_PLUGIN_INIT_SETTINGS];

    			// invoked after plugin setupHTML() => store plugin settings 
				if (!is_null($pluginSettings) && count($pluginSettings)>0) {
					$plugins = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
					if (is_null($plugins)) $plugins = array();
	
					if ($pluginSettings[PSNG_PLUGIN_CLASS] != ''){
						if ($pluginSettings[PSNG_PLUGIN_ID] == '') { // no id, new => push, et pluginid
							if (!is_array($plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]])) 
								$plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]] = array();
							$pluginid = count($plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]]);	
							while(true) {
								if (isset($plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]][$pluginid])) {
									$pluginid++;
									continue;
								}
								$pluginSettings[PSNG_PLUGIN_ID] = $pluginid;
								$plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]][$pluginid] = $pluginSettings;
								break;
							}
						} else { // update pluginid
							$plugins[$pluginSettings[PSNG_PLUGIN_TYPE]][$pluginSettings[PSNG_PLUGIN_CLASS]][$pluginSettings[PSNG_PLUGIN_ID]] = $pluginSettings;
						}
						$this->settings->setValue(PSNG_SETTINGS_PLUGIN, $plugins);
					}
					// display plugin overview
	    			$this->gui->displayPluginOverview($input[PSNG_PLUGIN_TYPE]);
					
				} // not invoked after setupHTML() => create new instance or load already existing instance and display setupHTML content of plugin
				  elseif (!is_null($input[PSNG_PLUGIN_CLASS])) { // class known
					if (!is_null($input[PSNG_PLUGIN_ID])) { // id known => load saved settings
						$instances = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
						$settings = $instances[$input[PSNG_PLUGIN_TYPE]][$input[PSNG_PLUGIN_CLASS]][$input[PSNG_PLUGIN_ID]];
					}
					if (is_null($settings) || count($settings) == 0) { // problematic when type not set?
						$settings = array(PSNG_PLUGIN_TYPE => $input[PSNG_PLUGIN_TYPE], PSNG_PLUGIN_CLASS => $input[PSNG_PLUGIN_CLASS], PSNG_PLUGIN_ID => $input[PSNG_PLUGIN_ID]);
					}
					$this->gui->displayPluginSetup($input[PSNG_PLUGIN_CLASS], $settings);
					
				} else {
	    			$this->gui->displayPluginOverview($input[PSNG_PLUGIN_TYPE]);
				  }				
				break;

			// delete plugin
			case PSNG_ACTION_PLUGIN_REMOVE:
				if($input[PSNG_PLUGIN_TYPE] != '' && $input[PSNG_PLUGIN_CLASS] != '' && $input[PSNG_PLUGIN_ID] != '') {
					$instances = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
					unset($instances[$input[PSNG_PLUGIN_TYPE]][$input[PSNG_PLUGIN_CLASS]][$input[PSNG_PLUGIN_ID]]);
					$this->settings->setValue(PSNG_SETTINGS_PLUGIN, $instances);
					$this->gui->addText("Plugin deleted");
				} else {
					$this->gui->addError("Couldn't delete plugin for type " . $input[PSNG_PLUGIN_TYPE] . ", class " . $input[PSNG_PLUGIN_CLASS] . ", id " . $input[PSNG_PLUGIN_ID]);
				}
				$this->gui->displayPluginOverview($input[PSNG_PLUGIN_TYPE]);
				break;
				
    		// view plugin section
    		case PSNG_ACTION_PLUGIN_OVERVIEW:
    			$this->gui->displayPluginOverview($input[PSNG_PLUGIN_TYPE]);
    			break;

			// create new instance of plugin
    		case PSNG_ACTION_PLUGIN_NEWINSTANCE:
    			$this->gui->displayPluginSetup($input[PSNG_PLUGIN_CLASS], array(PSNG_PLUGIN_TYPE => $input[PSNG_PLUGIN_TYPE]));
    			break;

			// display settings page
    		case PSNG_ACTION_SETTINGS:
    			$this->gui->displaySetup();
    			break;
    		
    		// stores settings from user input
    		case PSNG_ACTION_SETTINGS_STORE:
    			$settings = $input[PSNG_SETTINGS];
    			// TODO check settings
				if ($settings[PSNG_SETTINGS_USEFILES] == '') $settings[PSNG_SETTINGS_USEFILES] = PSNG_FALSE;
				if ($settings[PSNG_SETTINGS_SESSIONS] == '') $settings[PSNG_SETTINGS_SESSIONS] = PSNG_FALSE;
    			// update settings object according to settings from user
    			//$this->settings->useFile($settings[PSNG_SETTINGS_USEFILES] == PSNG_TRUE, $settings[PSNG_SETTINGS_FILENAME]);
    			//$this->settings->useSession($settings[PSNG_SETTINGS_SESSIONS] == PSNG_TRUE);
    			
    			// store settings to settings object
    			foreach ($settings as $key => $value) {
    				$this->settings->setValue($key, $value);
    			}
    			$this->gui->displayOverview();    			
    			break;
    			
			// run all input plugin instances    			
    		case PSNG_ACTION_RUN:
    			$plugins = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
			$this->gui->displayRunInput($plugins);
    			break;

			// run all output plugin instances    		
    		case PSNG_ACTION_EXPORT:
    			$plugins = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
				$this->gui->displayRunOutput($plugins);
    			break;
    		
    		// display default page: overview
			case '':
    		default:
    			$this->gui->displayOverview(); 
    			break;    		
    	}
    	    	
    }   
    
    /**
     * this is invoked when phpSitemapNG is going to be shut down
     */
    function tearDown() {
    	// teardown settings 
    	$this->settings->tearDown();    	
		// teardown gui    	
    	$this->gui->tearDown();
		// teardown storage    	
    	$this->storage->tearDown();
    	// we do not have to teardown plugins because they're already teardowned after the invocation of their run() function
    } 
}
?>