<?php
/**
 * This is an example of a phpSitemapNG input plugin
 *    this is explaned in detail at
 * 		http://enarion.net/google/phpsitemapng/development/architecture/plugin_example/ 
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.1 from 2005-08-16
 */
class UrlInput extends Input {
	var $settings; // this is an array that contains the internal plugin settings
	var $storage;  // this contains the reference to the storage delegate
	
	/**
	 * create an instance - NO parameters are allowed! 
	 * (this is due to java-bean-like on-the-fly instanciation of classes)
	 * 
	 * @return plugin new object of this class 
	 */
    function UrlInput() {
    	$this->settings = array();
    }
    
    /**
     * returns a list of description about this plugin
     * in detail:
     * <ul>
     * 	<li><b>name</b> - name of the plugin</li>
     *  <li><b>short_description</b> - a short description (max. 100 chars) that 
     * 		will be displayed at the overview page</li>
     *  <li><b>long_description</b> - a longer description</li>
     *  <li><b>url</b> - the url where the plugin can be downloaded and more 
     * 		information are available</li>
     *  <li><b>version</b> - version information</li>
     *  <li><b>author</b> - name or organisation who created this plugin</li>
     * </ul>
     * 	
     * this information is currently used only to give the user more information about
     * the plugin
     * 
     * @return array description of this plugin
     */
    function getInfo() {
    	return array(
    		'name' => 'Enarion.net UrlInput',
    		'short_description' => 'This plugin adds a specified list of urls to the storage.',
    		'long_description' => '',
    		'url' => 'http://enarion.net/google/phpsitemapng/',
    		'version' => '1.0',
    		'author' => 'enarion.net'
		);
    }
		
    /**
     * this function returns the html code needed to setup this plugin
     * 
     * to do so you have to use input/textarea fields that will be displayed
     * to the user in an embedded form handled by phpSitemapNG.
     * 
     * You have to set the name to PSNG_PLUGIN_INIT_SETTINGS[name_of_setting]
     * The value of PSNG_PLUGIN_INIT_SETTINGS is set by phpSitemapNG and only
     * information stored in this array can and will be sent back to the plugin!
     * This is necessary because the setup functions and run functions use the
     * same settings! 
     * 
     * You are not allowed to use other tags than 
     * 	&lt;table&gt;, &lt;tr&gt;, &lt;td&gt;, &lt;input&gt;, 
     * 	&lt;textarea&gt;, &lt;p&gt;, &lt;br&gt;
     * - all other tags will be removed for security reason!
     * 
     * If you need a special tag - please get in contact with the author!
     * 
     *
     * @return string html code of the setup page for this plugin 
     */
    function getSetupHtml() {    
    	$res = '<p>Specify the urls that will be added to phpSitemapNG in this text area, use line breaks as seperator<br/>'."\n";
		$res .= '<textarea name="'.PSNG_PLUGIN_INIT_SETTINGS.'[urls_to_add]" cols="40" rows="10">';
		if (!is_null($this->settings['urls_to_add']) && count($this->settings['urls_to_add'])>0) {
			foreach ($this->settings['urls_to_add'] as $key => $val) {
				$res .= trim($val) . "\n";
			}
			// remove last \n
			$res = substr($res, 0, strlen($res)-1);			
		}
		$res .= '</textarea>'. "\n";
    	
    	return $res;    		
    }
    
    /**
     * Initialize the plugin, this is invoked after a plugin is created.
     * 
     * @param array setupInformation a list of information to setup this plugin 
     * that are in detail:
     * <ul>
     *   <li><b>PSNG_PLUGIN_INIT_SETTINGS</b> - the values of the getSetupHtml() 
     * 			function when this is an instance that has been set up by 
     * 			the user; this is empty if this is an instance that will
     * 			be set in this run</li>
     *   <li><b>PSNG_PLUGIN_INIT_STORAGE</b> - this contains a reference to an storage object</li>
     * </ul> 
     *
     * @return boolean true if succeed, false if failed 
     */
    function init($setupInformation) {
    	if (!is_null($setupInformation[PSNG_PLUGIN_INIT_SETTINGS]) && count($setupInformation[PSNG_PLUGIN_INIT_SETTINGS])>0) {
    		$this->settings['urls_to_add'] = HelperClass::toArray($setupInformation[PSNG_PLUGIN_INIT_SETTINGS]['urls_to_add']);
    	}
    	$this->storage = & $setupInformation[PSNG_PLUGIN_INIT_STORAGE]; // necessary because we're only getting a reference to an object!!!
    	
    	return true;    	
    } 
    
    /**
     * execute this plugin
     * this is invoked after an creation of the plugin and initialisation
     *
     * @return boolean true if finished successful, false otherwise
     */
    function run() {
    	$res = 0;
    	if (count($this->settings['urls_to_add']) > 0) {
    		foreach($this->settings['urls_to_add'] as $key => $url) {
	    		if ($this->storage->fire(array(
	    			PSNG_URLINFO_URL => $url, 
					PSNG_URLINFO_ENABLED => 1))) {
					$res++;
				} // if		
    		} // foreach
    	} // if
    	return $res;   	
    }

	/**
	 * shut down plugin
	 * this is invoked when phpSitemapNG is shut down or this plugin has to be shutdown
	 * 
	 * unset temporary values, store current unsaved information here!	 * 
	 * @return void
	 */	
	function tearDown() {
		unset($this->settings);
		unset($this->storage);
	}
}
?>