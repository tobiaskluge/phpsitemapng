<?php
/**
 * this class handles the interaction with Google and Google Sitemaps
 * in details:
 *   - generate google sitemaps files
 *   - generate google sitemaps index files
 *   - return google the appropriate sitemaps / sitemaps index files
 * 
 * TODO do not buffer the file output anymore!!! (rewrite GsgXml)
 * 
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.1 from 2005-08-18
 */
class GoogleSitemaps extends Output {
	var $storage;
	var $settings;

	/**
	 * setup an instance of Google Sitemaps output plugin class 
	 */
    function GoogleSitemaps() {
    	require_once(PSNG_PATH_PLUGIN_OUTPUT.'/enarion/GSitemapsWriter.class.php');
    }

    /**
     * see Plugin.class.php::getInfo() for details
     */
    function getInfo() {
    	return array(
    		'name' => 'Enarion.net GoogleSitemaps Export',
    		'short_description' => 'This plugin generates Google Sitemaps files',
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
    		// set output dir
			if ($this->settings['output_dir'] == '') $this->settings['output_dir'] = $_SERVER['DOCUMENT_ROOT'];
			if (!($this->settings['output_dir'] != '/' || $this->settings['output_dir'] != '\\')) $this->settings['output_dir'] .= '/';
			$this->settings['output_dir'] .= 'gsitemaps/'; //realpath(dirname(__FILE__).'/../../tmp/gsitemaps/.').'/';
			
			// set filename of sitemaps index file
			if ($this->settings['sitemaps_indexfilename'] == '') $this->settings['sitemaps_indexfilename'] = $_SERVER['DOCUMENT_ROOT'];
			if (!(substr($this->settings['sitemaps_indexfilename'],-1) == '/' || substr($this->settings['sitemaps_indexfilename'],-1) == '\\')) $this->settings['sitemaps_indexfilename'] .= '/';
			$this->settings['sitemaps_indexfilename'] .= 'sitemap_index.xml';
			 
			// set apropriate url
			$this->settings['website'] = 'http://'.$_SERVER['HTTP_HOST'] . '/';
    		// set base filename of generated sitemaps files
			if ($this->settings['baseFilename'] == '') $this->settings['baseFilename'] = 'gsitemaps';
			// set kind
			if ($this->settings['sitemapsIndex'] == '') $this->settings['sitemapsIndex'] = 'true';
			// set strategy
			if ($this->settings['sitemaps_strategy'] == '') $this->settings['sitemaps_strategy'] = 'lastmod';

    		// set date only output
			if ($this->settings['dateonly'] == '') $this->settings['dateonly'] = false;
    		// set add stylesheet headers
			if ($this->settings['stylesheetheaders'] == '') $this->settings['stylesheetheaders'] = true;
			// set max urls per file
			if ($this->settings['sitemaps_numberOfFiles'] == '') $this->settings['sitemaps_numberOfFiles'] = 50000;
			
    		// set compress output value
			if ($this->settings['compress_output'] == '') $this->settings['compress_output'] = true;
    		// set ping google
			if ($this->settings['ping'] == '') $this->settings['ping'] = false;
			
		}
    	$res = '';
    	
    	$res .= '    	
	  <table border="0" cellpadding="5" cellspacing="0" width="600">
	  <tr>
	  	<td width="200" valign="top"><label for="ioutput_dir" accesskey="D">Directory</label></td>
		<td width="396">
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[output_dir]" id="ioutput_dir" align="LEFT" size="50" value="'.$this->settings['output_dir'].'"/>
			<br/><font size="-1">directory on the filesystem where the generated sitemaps files will be stored into</font>					
		</td>
	  </tr>	
	  <tr>
	  	<td valign="top"><label for="iwebsite" accesskey="U">Url of domain</label></td>
		<td>
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[website]" id="iwebsite" align="LEFT" size="50" value="'.$this->settings['website'].'"/>
			<br /><font size="-1">url of website for that the sitemap will be generated</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td valign="top"><label for="ibaseFilename" accesskey="F">Base filename</label></td>
		<td>
			<input class="required" type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[baseFilename]" id="ibaseFilename" align="LEFT" size="50" value="'.$this->settings['baseFilename'].'"/>
			<br /><font size="-1">base filename of generated sitemaps files, e.g. gsitemaps will result in gsitemaps0.xml, gsitemaps1.xml, ...</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td valign="top"><label for="icompress_output" accesskey="C">Compression</label></td>
		<td>
			<input ' . (function_exists('gzencode')?'':'disabled') . ' type="checkbox" '. (($this->settings['compress_output'] != '') ? 'checked':'') .' name="'.PSNG_PLUGIN_INIT_SETTINGS.'[compress_output]"  id="icompress_output" align="LEFT" value="true"/>Compress sitemap 
			'.(function_exists('gzencode')?'':'(not available within your php installation (need gzip functionality enabled)!)').'
			<br/><font size="-1">(with gzip; necessary if uncompressed sitemap is larger than 10 MB)</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td valign="top"><label for="icompress_output" accesskey="N">Number of entries</label></td>
		<td>
			<input type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemaps_numberOfFiles]" id="isitemaps_numberOfFiles" align="LEFT" size="50" value="'.$this->settings['sitemaps_numberOfFiles'].'"/>
			<br /><font size="-1">the number of entries that will be written into one Google Sitemaps file(max is 50.000)</font>
		</td>
	  </tr>	  		
	  <tr>
	  	<td valign="top"><label for="idateonly" accesskey="I">Date/Time</label></td>
		<td>
			<input type="checkbox" '. (($this->settings['dateonly'] != '') ? 'checked':'') .' name="'.PSNG_PLUGIN_INIT_SETTINGS.'[dateonly]"  id="idateonly" align="LEFT" value="true"/>Write date only 
			<br/><font size="-1">do not write time information - enable this when getting invalid date error from Google</font>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="iping" accesskey="I">Ping</label></td>
		<td>
			<input type="checkbox" '. (($this->settings['ping'] != '') ? 'checked':'') .' name="'.PSNG_PLUGIN_INIT_SETTINGS.'[ping]"  id="iping" align="LEFT" value="true"/>Ping Google 
			<br/><font size="-1">Google will be informed about the updated sitemaps file(s)</font>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="istylesheetheaders" accesskey="I">Stylesheet headers</label></td>
		<td>
			<input type="checkbox" '. (($this->settings['stylesheetheaders'] != '') ? 'checked':'') .' name="'.PSNG_PLUGIN_INIT_SETTINGS.'[stylesheetheaders]"  id="istylesheetheaders" align="LEFT" value="true"/>Add stylesheet headers 
			<br/><font size="-1">get a nice layout when accessing the generated files with a browser</font>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="isitemapsIndex" accesskey="I">Sitemaps Index</label></td>
		<td>
			<p>
				<input type="checkbox" '. (($this->settings['sitemapsIndex'] != '') ? 'checked':'') .' name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemapsIndex]"  id="isitemapsIndex" align="LEFT" value="true"/>Generate Sitemaps index file 
				<br/><font size="-1">Necessary if there would be more than 50.000 urls in a Google Sitemaps file</font>
			</p>			
			<p> <b>Location of Index file:</b><br/>
				<input type="Text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemaps_indexfilename]" id="isitemaps_indexfilename" align="LEFT" size="50" value="'.$this->settings['sitemaps_indexfilename'].'"/>
				<br /><font size="-1">location of the sitemaps index file</font>
			</p>			
			<p>
				<font size="-1">select strategy that will be used to generate the sitemaps files</font><br/>
				<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemaps_strategy]" value="changefreq" '.(($this->settings['sitemaps_strategy']=='changefreq')?'checked':'').'> Change frequency<br>
				<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemaps_strategy]" value="lastmod" '.(($this->settings['sitemaps_strategy']=='lastmod')?'checked':'').'> Last modification date<br>
				<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[sitemaps_strategy]" value="incomming" '.(($this->settings['sitemaps_strategy']=='incomming')?'checked':'').'> Incomming urls			
			</p>			
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
    		$this->settings['output_dir'] = $setupInformation['output_dir'];
    		$this->settings['baseFilename'] = $setupInformation['baseFilename'];
    		$this->settings['compress_output'] = $setupInformation['compress_output'] != '';
    		$this->settings['sitemapsIndex'] = $setupInformation['sitemapsIndex'] != '';
    		if(!is_numeric($setupInformation['sitemaps_numberOfFiles']) || $setupInformation['sitemaps_numberOfFiles'] > 50000 || $setupInformation['sitemaps_numberOfFiles'] < 1) {
    			$this->settings['sitemaps_numberOfFiles'] = 50000;
    		} else {
    			$this->settings['sitemaps_numberOfFiles'] = $setupInformation['sitemaps_numberOfFiles'];
    		}
    		$this->settings['sitemaps_strategy'] = $setupInformation['sitemaps_strategy'];
    		$this->settings['dateonly'] = $setupInformation['dateonly'] != '';
    		$this->settings['stylesheetheaders'] = $setupInformation['stylesheetheaders'] != '';
    		$this->settings['ping'] = $setupInformation['ping'] != '';
    		$this->settings['sitemaps_indexfilename'] = $setupInformation['sitemaps_indexfilename'];

    	}
    	$this->storage = & $setupInformations[PSNG_PLUGIN_INIT_STORAGE];
    	return true;
    } 
    
    /**
     * see Plugin.class.php::run() for details
     */
    function run() {
    	global $openFile_error;
    	// TODO change this to a better delegate function => better when computing huge about of pages
		$urls = $this->storage->fire(array(PSNG_DELEGATE_OUTPUTWEBSITE => $this->settings['website']));

		$gsw = new GSitemapsWriter($this->settings['website'], $this->settings['output_dir'], $this->settings['baseFilename'], $this->settings['sitemaps_numberOfFiles']);
		$numb = 0;
		
		if ($this->settings['sitemapsIndex'] != true) {
			$gsw->initStrictOnSimpleSitemaps();
		} else { // sitemaps index
			$gsw->initGenerateSitemapsIndex($this->settings['sitemaps_strategy']);
		}
		if ($this->settings['compress_output']) $gsw->initCompressOutput();
		if ($this->settings['dateonly']) $gsw->initUseShortDateTimeFormat();
		if ($this->settings['stylesheetheaders']) $gsw->initAddStylesheetHeaders($this->settings['website'].$settings['urlOffset']);
		if ($this->settings['sitemaps_indexfilename'] != '') $gsw->initSetSitemapsIndexFilename($this->settings['sitemaps_indexfilename']);
		
		foreach($urls as $id => $urlInfo) {
			$res = $gsw->addURL($urlInfo[PSNG_URLINFO_URL], $urlInfo[PSNG_URLINFO_LASTMOD], $urlInfo[PSNG_URLINFO_CHANGEFREQ], $urlInfo[PSNG_URLINFO_PRIORITY]);
			if ($res != '') {
				echo "Error while adding url ".$urlInfo[PSNG_URLINFO_URL].": " . $res . "<br>\n";
			} else {	
				$numb++;
			}
		}
		
		$files = $gsw->getFiles(); // get list of files that have been created
//		print_r($files);
		
		if (count($files)>1) echo "Created " . count($files) . " Sitemap files.<br>\n";
		
		echo $gsw->tearDown(); // tear down, close open files
		
		// TODO add sitemap.php functionality
		 
		// TODO add ping functionality
		
    	return $numb;	
    }
    

    /**
     * see Plugin.class.php::tearDown() for details
     */
	function tearDown() {
		unset($this->storage); // php4
		$this->storage = null; // php5
		unset($this->settings); // php4
		$this->settings = null; // php5
	}
}
?>