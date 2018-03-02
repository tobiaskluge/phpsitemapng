<?php
/**
 * This is the interface of the common plugin class used by phpSitemapNG
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-08-14
 */
class Plugin {

	/**
	 * create an instance - NO parameters are allowed! 
	 * (this is due to java-bean-like on-the-fly instanciation of classes)
	 * 
	 * @return plugin new object of this class 
	 */
    function Plugin() {
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
    } 
    
    /**
     * execute this plugin
     * this is invoked after an creation of the plugin and initialisation
     *
     * @return int number of urls that have been handled by this plugin
     */
    function run() {    	
    }

	/**
	 * shut down plugin
	 * this is invoked when phpSitemapNG is shut down or this plugin has to be shutdown
	 * 
	 * unset temporary values, store current unsaved information here!	 * 
	 * @return void
	 */	
	function tearDown() {
	}
}
?>