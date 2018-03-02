<?php
/**
 * this input plugin class scans the local filesystem for classes
 * 
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
 
include (PSNG_PATH_PLUGIN_INPUT.'enarion/FilesystemHandler.class.php');

class Filesystem extends Input {
	var $storage; 	// reference to AddToStorage delegate
	var $settings;	// this array contains the settings
	var $enit_fsh; 	// filesystem handler object
	var $size;

	/**
	 * create instance
	 */	
    function Filesystem() {
    	$this->settings = array();
    }

    /**
     * see Plugin.class.php::getInfo() for details
     */
    function getInfo() {
    	return array(
    		'name' => 'Enarion.net Filesystem',
    		'short_description' => 'This plugin scans the filesystem for files. Fast and easy, but not for dynamic content.',
    		'long_description' => '',
    		'url' => 'http://enarion.net/google/phpsitemapng/',
    		'version' => '1.0',
    		'author' => 'enarion.net'
		);
    }
		
    /**
     * see Plugin.class.php::getSetupHtml() for details
     */
    function getSetupHtml() {
    	// check if empty settings => set default settings
    	if (is_null($this->settings) || count($this->settings) == 0) {
    		// set page root
    		$this->settings['page_root'] = substr($_SERVER[SCRIPT_FILENAME],0,strpos($_SERVER[SCRIPT_FILENAME],$_SERVER[SCRIPT_URL]));
			if ($this->settings['page_root'] == '') $this->settings['page_root'] = $_SERVER[DOCUMENT_ROOT];
	
			// set apropriate url
			$this->settings['website'] = 'http://'.$_SERVER[HTTP_HOST] . '/';
			
    		// set initial disallow directories
			$this->settings['disallow_dir'] = array();
			$this->settings['disallow_dir'][] = "admin";
			$this->settings['disallow_dir'][] = "include";
			$this->settings['disallow_dir'][] = "inc";
			$this->settings['disallow_dir'][] = "logs";
			$this->settings['disallow_dir'][] = "cgi-bin";
    		
    		// set initial disallow files
    		$this->settings['disallow_file'] = array();
			$this->settings['disallow_file'][] = ".xml";
			$this->settings['disallow_file'][] = ".inc";
			$this->settings['disallow_file'][] = ".old";
			$this->settings['disallow_file'][] = ".save";
			$this->settings['disallow_file'][] = ".txt";
			$this->settings['disallow_file'][] = ".js";
			$this->settings['disallow_file'][] = "~";
			$this->settings['disallow_file'][] = ".LCK";
			$this->settings['disallow_file'][] = ".zip";
			$this->settings['disallow_file'][] = ".ZIP";
			$this->settings['disallow_file'][] = ".avi";
			$this->settings['disallow_file'][] = ".mpg";
			$this->settings['disallow_file'][] = ".doc";
			$this->settings['disallow_file'][] = ".bmp";
			$this->settings['disallow_file'][] = ".BMP";
			$this->settings['disallow_file'][] = ".jpg";
			$this->settings['disallow_file'][] = ".jpeg";
			$this->settings['disallow_file'][] = ".JPG";
			$this->settings['disallow_file'][] = ".GIF";
			$this->settings['disallow_file'][] = ".PNG";
			$this->settings['disallow_file'][] = ".png";
			$this->settings['disallow_file'][] = ".gif";
			$this->settings['disallow_file'][] = ".CSV";
			$this->settings['disallow_file'][] = ".csv";
			$this->settings['disallow_file'][] = ".css";
			$this->settings['disallow_file'][] = ".class";
			$this->settings['disallow_file'][] = ".jar";
			$this->settings['disallow_file'][] = ".swf";
    		
    	}
    	$res = '';
    	
    	$res .= '    	
    	<table border="0" cellpadding="5" cellspacing="0" width="495">
	  <tr>
	  	<td width="200" valign="top"><label for="ipage_root" accesskey="R">Directory</label></td>
		<td width="396">
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[page_root]" id="ipage_root" align="LEFT" size="50" value="'.$this->settings['page_root'].'"/>
			<br/><font size="-1">path on <b>local file system</b> of your webserver</font>					
		</td>
	  </tr>	
	  <tr>
	  	<td valign="top"><label for="iwebsite" accesskey="W">Url</label></td>
		<td>
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[website]" id="iwebsite" align="LEFT" size="50" value="'.$this->settings['website'].'"/>
			<br /><font size="-1">Apropriate url that matches the path specified in Directory</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td valign="top"><label for="idisallow_dir" accesskey="D">Exclude directories</label></td>
		<td>
			<font size="-1">directories containing these substrings will not be scanned for files; use line break to separate entries</font><br />
			<textarea name="'.PSNG_PLUGIN_INIT_SETTINGS.'[disallow_dir]" cols="40" rows="10" id="idisallow_dir">';
		if (!is_null($this->settings['disallow_dir']) && count($this->settings['disallow_dir'])>0) {
			foreach ($this->settings['disallow_dir'] as $key => $val) {
				$res .= trim($val) . "\n";
			}
			// remove last \n
			$res = substr($res, 0, strlen($res)-1);			
		}
		$res .= '</textarea>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="idisallow_file" accesskey="F">Exclude files</label></td>
		<td>
			<font size="-1">files containing these substrings will not be added to site index; use line break to separate entries</font><br/>
			<textarea name="'.PSNG_PLUGIN_INIT_SETTINGS.'[disallow_file]" cols="40" rows="10" id="idisallow_file">';
		if (!is_null($this->settings['disallow_file']) && count($this->settings['disallow_file'])>0) {
			foreach ($this->settings['disallow_file'] as $key => $val) {
				$res .= trim($val) . "\n";
			}
			// remove last \n
			$res = substr($res, 0, strlen($res)-1);			
		}
		$res .= '</textarea>
		</td>
	  </tr>
	</table>';
    	return $res;	 	
    }
    
    /**
     * see Plugin.class.php::init() for details
     */
    function init($setupInformations) {    	
    	$setupInformation = $setupInformations[PSNG_PLUGIN_INIT_SETTINGS];
    	if (!is_null($setupInformation) && count($setupInformation)>0) {
    		$this->settings['website'] = $setupInformation['website'];
    		if (substr($this->settings['website'], strlen($this->settings['website'])-1) != '/') $this->settings['website'] .= '/'; 
    		$this->settings['page_root'] = $setupInformation['page_root'];
    		$this->settings['disallow_dir'] = HelperClass::toArray($setupInformation['disallow_dir']);
    		$this->settings['disallow_file'] = HelperClass::toArray($setupInformation['disallow_file']);
    	}
    	$this->storage = & $setupInformations[PSNG_PLUGIN_INIT_STORAGE];
    	
    	return true;
    } 
    
    /**
     * see Plugin.class.php::run() for details
     */
    function run() {
    	// create and setup the filesystem scanner object 
    	$this->enit_fsh = new FilesystemHandler($this->settings['page_root'], 0, $this->settings['website']);
    	$this->enit_fsh->setStorage($this->storage);
    	$this->enit_fsh->setTodo(array($this->settings['page_root']));
    	$this->settings['disallow_dir'][] = PSNG_DIRECTORY;
		$this->enit_fsh->setForbiddenDirectories($this->settings['disallow_dir']);
		$this->enit_fsh->setForbiddenFiles($this->settings['disallow_file']);
		
		// start filesystem scanner object
		$this->size = $this->enit_fsh->start();

		return $this->size;		
    }
    
    /**
     * see Plugin.class.php::tearDown() for details
     */
	function tearDown() {
		if (!is_null($this->enit_fsh)) {
			$this->enit_fsh->tearDown();
		}
		unset($this->enit_fsh); // php4
		$this->enit_fsh = null; // php5
		unset($this->settings); // php4
		$this->settings = null; // php5
	}
}
?>