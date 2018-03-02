<?php
/**
 * input plugin class - crawler that scans your website for urls
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

class Crawler extends Input {
	var $settings;	// array that contains the settings of this plugin
	var $storage; 	// instance of AddToStorage delegate
	var $enit_crawler; // instance of "real" crawler
	/**
	 * create an instance of the crawler class
	 */
    function Crawler() {
    	require_once ( PSNG_CLASS_DATETIMEHELPER );
		require_once ( PSNG_PATH_PLUGIN_INPUT.'enarion/CrawlerHandler.class.php' );
    }

    /**
     * see Plugin.class.php::getInfo() for details
     */
    function getInfo() {
    	return array(
    		'name' => 'Enarion.net Crawler',
    		'short_description' => 'This plugin crawls the website. Takes some time, but will compute dynamic content.',
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
			// set apropriate url
			$this->settings['website'] = 'http://'.$_SERVER[HTTP_HOST] . '/';

			// set maximum file size
			$this->settings['maxFilesize'] = '100000';
			// set maximum file computation time
			$this->settings['maxGetFileTime'] = 5;

    		// set initial disallow keys
			$this->settings['disallow_keys'] = array();
			$this->settings['disallow_keys'][] = 'PHPSESSID';
			$this->settings['disallow_keys'][] = session_name();
			
    		// set initial disallow directories
			$this->settings['disallow_dir'] = array();
			$this->settings['disallow_dir'][] = 'admin';
			$this->settings['disallow_dir'][] = 'include';
			$this->settings['disallow_dir'][] = 'logs';
			$this->settings['disallow_dir'][] = 'cgi-bin';
    		
    		// set initial disallow files
    		$this->settings['disallow_file'] = array();
			$this->settings['disallow_file'][] = '.xml';
			$this->settings['disallow_file'][] = '.inc';
			$this->settings['disallow_file'][] = '.old';
			$this->settings['disallow_file'][] = '.save';
			$this->settings['disallow_file'][] = '.txt';
			$this->settings['disallow_file'][] = '.js';
			$this->settings['disallow_file'][] = '~';
			$this->settings['disallow_file'][] = '.LCK';
			$this->settings['disallow_file'][] = '.zip';
			$this->settings['disallow_file'][] = '.ZIP';
			$this->settings['disallow_file'][] = '.bmp';
			$this->settings['disallow_file'][] = '.BMP';
			$this->settings['disallow_file'][] = '.jpg';
			$this->settings['disallow_file'][] = '.jpeg';
			$this->settings['disallow_file'][] = '.JPG';
			$this->settings['disallow_file'][] = '.GIF';
			$this->settings['disallow_file'][] = '.PNG';
			$this->settings['disallow_file'][] = '.png';
			$this->settings['disallow_file'][] = '.gif';
			$this->settings['disallow_file'][] = '.CSV';
			$this->settings['disallow_file'][] = '.csv';
			$this->settings['disallow_file'][] = '.css';
			$this->settings['disallow_file'][] = '.class';
			$this->settings['disallow_file'][] = '.jar';
			$this->settings['disallow_file'][] = '.swf';
    		
    	}
    	$res = '';
    	
    	$res .= '    	
    	<table border="0" cellpadding="5" cellspacing="0" width="495">
	  <tr>
	  	<td valign="top"><label for="iwebsite" accesskey="W">Website/URL to crawl</label></td>
		<td>
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[website]" id="iwebsite" align="LEFT" size="50" value="'.$this->settings['website'].'"/>
			<br /><font size="-1">url of your website</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td width="200" valign="top"><label for="imaxfilesize" accesskey="S">max Filesize</label></td>
		<td width="396">
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[maxFilesize]" id="imaxFilesize" align="LEFT" size="50" value="'.$this->settings['maxFilesize'].'"/>
			<br/><font size="-1">size of files that will be computed</font>					
		</td>
	  </tr>	
	  <tr>
	  	<td width="200" valign="top"><label for="imaxGetFileTime" accesskey="T">Get file time</label></td>
		<td width="396">
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[maxGetFileTime]" id="imaxGetFileTime" align="LEFT" size="50" value="'.$this->settings['maxGetFileTime'].'"/>
			<br/><font size="-1">longest duration of download time for one page, if download takes longer, stop and compute the content that has already been downloaded</font>					
		</td>
	  </tr>	
	  <tr>
	  	<td valign="top"><label for="idisallow_dir" accesskey="D">Exclude directories</label></td>
		<td>
			<font size="-1">directories containing these substrings will not be scanned for files and will not be added to site index; use line break to separate entries</font><br />
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
			<font size="-1">files containing these substrings will not be crawled for further links and not added to site index; use line break to separate entries</font><br/>
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
	  <tr>
	  	<td valign="top"><label for="idisallow_keys" accesskey="K">Exclude keys</label></td>
		<td>
			<font size="-1">from urls containing one of these keys this key will be excluded - you have to add the session key of your website here! </font><br />
			<textarea name="'.PSNG_PLUGIN_INIT_SETTINGS.'[disallow_keys]" cols="40" rows="10" id="idisallow_keys">';
		if (!is_null($this->settings['disallow_keys']) && count($this->settings['disallow_keys'])>0) {
			foreach ($this->settings['disallow_keys'] as $key => $val) {
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
    		$this->settings['maxFilesize'] = $setupInformation['maxFilesize'];
    		$this->settings['maxGetFileTime'] = $setupInformation['maxGetFileTime'];
    		$this->settings['disallow_dir'] = HelperClass::toArray($setupInformation['disallow_dir']);
    		$this->settings['disallow_file'] = HelperClass::toArray($setupInformation['disallow_file']);
    		$this->settings['disallow_keys'] = HelperClass::toArray($setupInformation['disallow_keys']);
    	} // else: create new instance
    	$this->storage = & $setupInformations[PSNG_PLUGIN_INIT_STORAGE];
    	
    	return true;
    } 
    
    /**
     * see Plugin.class.php::run() for details
     */
    function run() {
    	// create and setup the crawler object 
    	$deadline = DateTimeHelper::microtime_float() + 60*60*24; // only for now, TODO update to timed plugin
    	$params = array('maxFilesize' => $this->settings['maxFilesize'], 'maxGetFileTime' => $this->settings['maxGetFileTime']);
    	$this->enit_crawler = new CrawlerHandler($this->settings['website'], $deadline, $params);
		$this->enit_crawler->setForbiddenDirectories($this->settings['disallow_dir']);
		$this->enit_crawler->setForbiddenFiles($this->settings['disallow_file']);
		$this->enit_crawler->setForbiddenKeys($this->settings['disallow_keys']);
		$this->enit_crawler->setStorage($this->storage);		
		// start filesystem scanner object
		$size = $this->enit_crawler->start();
		return $size;		
    }

    /**
     * see Plugin.class.php::tearDown() for details
     */
	function tearDown() {
		if (!is_null($this->enit_crawler)) {
			$this->enit_crawler->tearDown();
		}
		unset($this->enit_crawler); // php4
		$this->enit_crawler = null; // php5
		unset($this->settings); // php4
		$this->settings = null; // php5
	}
}
?>