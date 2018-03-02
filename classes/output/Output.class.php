<?php
/**
 * This is the interface of the common output plugin class used by phpSitemapNG
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

class Output extends Plugin {

    function Output() {
    }
    
    /**
     * this function returns a short info text (length less than 200 chars)
     * 
     * @return array description of this plugin
     */
    function getInfo() {
    }
		
    /**
     * this function returns the html code needed setup this plugin
     *
     * @return string html code of the setup page for this plugin 
     */
    function getSetupHtml() {    	
    }
    
    /**
     * the edited result from the html code of the setup page
     * that has been received from the browser of the user
     *
     * this information will also be used to setup an instance of this
     * plugin
     *
     * @param array setupInformation contains settings set by the user in the html code returned by getSetupHtml() 
     */
    function init($setupInformation) {    	
    } 
    
    /**
     * run this plugin
     *
     * @return boolean true if finished successful, false otherwise
     */
    function run() {    	
    }

	/**
	 * shut down plugin
	 * 
	 * @return void
	 */	
	function tearDown() {
	}    
}
?>