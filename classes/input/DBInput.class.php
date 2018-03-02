<?php
/**
 * This is a sample phpSitemapNG input plugin that connects to a database
 * 
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-12-17
 */
class DBInput extends Input {
	var $settings; // this is an array that contains the internal plugin settings
	var $storage;  // this contains the reference to the storage object
	/**
	 * create an instance - NO parameters are allowed! 
	 * (this is due to java-bean-like on-the-fly instanciation of classes)
	 * 
	 * @return plugin new object of this class 
	 */
    function DBInput() {
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
    		'name' => 'Sample DB-Input',
    		'short_description' => 'This plugin connects to a database and feeds phpSitemapNG with it. You have to write the functionality to create the urls and feed them into phpSitemapNG',
    		'long_description' => '',
    		'url' => 'http://enarion.net/google/phpsitemapng/',
    		'version' => '1.0',
    		'author' => 'Tobias Kluge, enarion.net'
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
	$res = '';
	$res .= '<h2>Database</h2>'."\n";
    	$res .= '<p>Login<br/><input type="text" name='.PSNG_PLUGIN_INIT_SETTINGS.'[db_login]" value="'.$this->settings['db_login'].'" /></p>' ."\n";
    	$res .= '<p>Password<br/><input type="text" name='.PSNG_PLUGIN_INIT_SETTINGS.'[db_password]" value="'.$this->settings['db_password'].'" /></p>' ."\n";
    	$res .= '<p>Host<br/><input type="text" name='.PSNG_PLUGIN_INIT_SETTINGS.'[db_host]" value="'.$this->settings['db_host'].'" /></p>' ."\n";
    	$res .= '<p>Database name<br/><input type="text" name='.PSNG_PLUGIN_INIT_SETTINGS.'[db_database]" value="'.$this->settings['db_database'].'" /></p>' ."\n";

	// add more plugin specific code if necessary
     	return $res;    		
    }
    
    /**
     * see Plugin.class.php::init() for details
     */
    function init($setupInformations) {   
    	$setupInformation = $setupInformations[PSNG_PLUGIN_INIT_SETTINGS];
	if (!isset($this->settings['db_host'])) $this->settings['db_host'] = $setupInformation['db_host'];
	if (!isset($this->settings['db_login'])) $this->settings['db_login'] = $setupInformation['db_login'];
	if (!isset($this->settings['db_password'])) $this->settings['db_password'] = $setupInformation['db_password'];
	if (!isset($this->settings['db_database'])) $this->settings['db_database'] = $setupInformation['db_database'];
	// precompute given parameters - you have to copy them at least to the $this->settings array

    	$this->storage = & $setupInformations[PSNG_PLUGIN_INIT_STORAGE];
    	return true;
    } 

    
    /**
     * execute this plugin
     * this is invoked after an creation of the plugin and initialisation
     *
     * @return boolean true if finished successful, false otherwise
     */
    function run() {
	$numbUrls = 0;

	echo "<p>You have to add your own <i>connection code</i> that extracts the urls of your cms! Have a look at the file <i>classes/input/DBInput.php</i>.<br/> No values have been added nor changed.</p>";

	/*
	 * sample code - extract data from database, compute urls and feed them into phpSitemapNG
	 *
	 * some common problems:
	 * - php is getting really slow when you try to fetch a batch of 30 mb (or many many lines) and feed them into an array
	 * 	=> suggestion: do it incremental (mysql offers a LIMIT feature - give it a try
	 * 	potentional limitation: phpSitemapNG currently stores this information in an array, and this array in a session variable
	 * 		=> so you shouldn't add too much, but give it a try
	 * 	=> will change to: when you submit a url - the appropriate compute plugins and the output plugins will be called and computed automatically in the background (which will remove the array/session bottleneck)
	 */

	// connecto to MySQL database - you don't have to change this
/*	$link = mysql_connect($this->settings['db_host'], $this->settings['db_login'], $this->settings['db_password']) or die('Could not connect: ' . mysql_error());
	mysql_select_db($this->settings['db_database']) or die('Could not select database');


 	// adapt this extraction code to your website and your database

	$query = 'SELECT * FROM urls';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		// you might have to create the urls first, make shure you do it the same way your cms does (otherwise you might submit broken urls - which isn't a good idea)

		$this->storage->fire(array(
			PSNG_URLINFO_URL => $line['url']
			PSNG_URLINFO_LASTMOD => $url['unixtimestamp_of_last_update'],
//			PSNG_URLINFO_CHANGEFREQ => 'daily',  	// change frequency - optional, uncomment this line to set it
//			PSNG_URLINFO_PRIORITY => '0.6',		// priority - optional, uncomment this line to set it
			PSNG_URLINFO_ENABLED => 1));
		$numbUrls++;
	}

	// clean up - Free resultset
	mysql_free_result($result);
	mysql_close($link);
*/


    	return $numbUrls;
    }

    /**
     * see Plugin.class.php::tearDown() for details
     */
	function tearDown() {
		unset($this->settings); // php4
		$this->settings = null; // php5
	}

}
?>