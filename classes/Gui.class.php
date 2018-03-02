<?php
/**
 * this class handles the output of phpSitemapNG
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

// this class uses the LayoutEngine developed by enarion.net for phpSitemapNG 1.x
require_once(PSNG_CLASS_LAYOUTENGINE);

require_once(PSNG_CLASS_PLUGIN);
require_once(PSNG_CLASS_PLUGINHANDLER);

class Gui {
	var $layout;
	var $settings;
	var $storage;
	
	/** 
	 * create an instance of the gui class
	 */
    function Gui(& $settings, & $storage) {
    	$this->settings =& $settings;
    	$this->storage =& $storage;
    	
    	$this->layout = new LayoutEngine("phpSitemapNG");
    	
    	// setup some values
		$this->layout->addContentFooter('<div align="center"><p>Copyright by enarion.net. This script is licensed under GPL and can be downloaded from
						<a target="_blank" href="http://enarion.net/google/">enarion.net/google/</a></p></div>');
		$this->layout->setTitle("control your sitemap files");
		$this->layout->setCharSet("iso-8859-1");
	// TODO define more css
		$this->layout->addCss('.error {color:#cc0000; font-weight: bold; }');
		$this->layout->addCss('.warning {color:#000000; font-weight: italic; }');
		$this->layout->addCss('.info {color:#000000; font-weight: normal; }');
		$this->layout->addCss('.success {color:#009900; font-weight: bold; }');
		$this->layout->addCss('body {color:#000000; font-family:helvetica; background-color:#ebb150; }');
		$this->layout->addCss('.body {position: absolute; top: 80px; left: 200px;}');
		$this->layout->addCss('.menu {position: absolute; top: 20px; left: 10px;}');
		$this->layout->addCss('.content_footer {position: absolute; top: 200px; left: 10px; width:140px; font-size:90%;}');
		$this->layout->switchOffBuffer(); 	// use unbuffered output mode
		print $this->layout->getHeaderLayout();

		$this->layout->addContentHeader('<a href="'.$this->settings->getValue(PSNG_SCRIPT).'?'.PSNG_ACTION.'='.PSNG_ACTION_SETTINGS.'" title="Setup page">Settings</a>');
		$this->layout->addContentHeader('<a href="'.$this->settings->getValue(PSNG_SCRIPT).'?'.PSNG_ACTION.'='.PSNG_ACTION_PLUGIN_OVERVIEW.'" title="Plugin section">Plugins</a>');
		$this->layout->addContentHeader('<a href="'.$this->settings->getValue(PSNG_SCRIPT).'?'.PSNG_ACTION.'='.PSNG_ACTION_RUN.'" title="Run input plugins">Run</a>');
		$this->layout->addContentHeader('<a href="'.$this->settings->getValue(PSNG_SCRIPT).'?'.PSNG_ACTION.'='.PSNG_ACTION_EXPORT.'" title="export to Google Sitemaps file">Export</a>');
	
		$this->layout->addContentHeader('<div align="left"><p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Donate the ongoing development of phpSitemapNG">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
			</form></p></div>');
			
		$this->layout->addText('<div class="body">');	
    }
    
    /**
     * displays the default screen of phpSitemapNG
     */
    function displayOverview() {
    	$this->layout->addText("This is phpSitemapNG!<br>\n".
    		'<p>You can <ul>
    			<li>setup some minor settings at the moment - select the <b>Settings</b> link</li>
    			<li>setup plugins that will grab all urls for the sitemap (so-called input plugins), compute the urls that  
    				have been found by the input plugins (socalled compute plugins) and generate output from these
    				urls (socalled output plugins) - press the <b>Plugins</b> link</li> 
				<li>Once you have setup some plugins you can run all input plugins - press the <b>Run</b> link</li>
				<li>And when the input plugins have found some urls, invoke the output plugins - press the <b>Export</b> link</li>
    			</ul></p>
				<p>Please note that this is a development release - there might and will be bugs (minor or major). If you encounter such
					(potential) problems and bugs - please report them at the <a href="http://forum.enarion.net/" target="_blank">phpSitemapNG forum</a>!<br/>
					There is no warranty for problems or erors that this software produces - you use this on your own risk!</p>
    			<p>Please visit the homepage of phpSitemapNG at <a href="http://enarion.net/google/" target="_blank">enarion.net/google/</a> to be informed
    				about new versions and additional plugins.<br/>
    				If you would like develop plugins for phpSitemapNG - have a look at the homepage of phpSitemapNG.
    			</p>
    			',
			'Welcome!');   	
    }
    
    /**
     * displays the setup page of phpSitemapNG2
     */
    function displaySetup() {
    	$this->layout->addText('', 'Global Setup');
		// add default values for emtpy fields:
		$tmpdir = $this->settings->getValue(PSNG_SETTINGS_TMPDIR);
		if ($tmpdir == '') {
			$tmpdir = realpath(($this->settings->getValue(PSNG_DIRECTORY_BASE).'/tmp/.')).'/';
		}
		$this->settings->setValue(PSNG_SETTINGS_TMPDIR, $tmpdir);

		// not editable at the moment, because hardcoded settings file in ontology
		$filename = $this->settings->getValue(PSNG_SETTINGS_FILENAME);
		if ($filename == '') {
			$filename = $tmpdir . "settings.inc.php";
		}
		$this->settings->setValue(PSNG_SETTINGS_FILENAME, $filename);
		if ($this->settings->getValue(PSNG_SETTINGS_USEFILES) == '')
				$this->settings->setValue(PSNG_SETTINGS_USEFILES, true);
		
		if ($this->settings->getValue(PSNG_SETTINGS_SESSIONS) == '')
				$this->settings->setValue(PSNG_SETTINGS_SESSIONS, true);

    	$this->layout->addText('<form action="'.$this->settings->getValue(PSNG_SCRIPT).'">');
		$this->layout->addText('<input type="hidden" name="'.PSNG_ACTION.'" value="'.PSNG_ACTION_SETTINGS_STORE.'" />');
		$this->layout->addText('<input type="Submit" value="Submit Settings" name="'.PSNG_ACTION_SETTINGS.'" />');
		// common settings
    	$this->layout->addText('','Common');
		$this->layout->addText('Temp Directory: <input type="Text" name="'.PSNG_SETTINGS.'['.PSNG_SETTINGS_TMPDIR.']" size="60" value="'.$this->settings->getValue(PSNG_SETTINGS_TMPDIR).'" />');
		// settings setup
    	$this->layout->addText('','Settings');
		$this->layout->addText('<input type="checkbox" disabled="disabled" readonly="readonly" name="'.PSNG_SETTINGS.'['.PSNG_SETTINGS_SESSIONS.']" value="'.PSNG_TRUE.'" '.(($this->settings->getValue(PSNG_SETTINGS_SESSIONS) === PSNG_TRUE)?'checked':'').'>Use sessions<br/>');
		$this->layout->addText('<input type="checkbox" disabled="disabled" readonly="readonly" name="'.PSNG_SETTINGS.'['.PSNG_SETTINGS_USEFILES.']" value="'.PSNG_TRUE.'" '.(($this->settings->getValue(PSNG_SETTINGS_USEFILES) === PSNG_TRUE)?'checked':'').'>Store settings into file '.
				'<input type="Text" disabled="disabled" readonly="readonly" name="'.PSNG_SETTINGS.'['.PSNG_SETTINGS_FILENAME.']" size="60" value="'.$this->settings->getValue(PSNG_SETTINGS_FILENAME).'" />'
				);
    	$this->layout->addText('</form>');
    }
     
    /**
     * run all input plugins instances and display result
     */
	function displayRunInput($plugins) {
    	require_once(PSNG_CLASS_PLUGIN_INPUT);
    	require_once(PSNG_CLASS_DELEGATE);
	require_once(PSNG_CLASS_DELEGATE_ADDTOSTORAGE);

	// reset storage
	$this->storage->resetURLs();
    	
    	$this->layout->addText('', 'Invoking all input plugin instances');
    	if (is_array($plugins[PSNG_PLUGIN_TYPE_INPUT])) {
			foreach ($plugins[PSNG_PLUGIN_TYPE_INPUT] as $className => $plug) {
				foreach ($plug as $title => $pluginInfo) {
			    	$plugin = PluginHandler::getPlugin($this->settings->getValue(PSNG_DIRECTORY_CLASSES).'/input/', $pluginInfo[PSNG_PLUGIN_CLASS]);
			    	if (!is_object($plugin))  { 
			    		$this->layout->addError("Couldn't instantiate an object for class $className!");
			    		return;
			    	}
			    	$addToStorage = new AddToStorage();
			    	$defParams = array(PSNG_URLINFO_PLUGIN_ADDED => $className, PSNG_URLINFO_GROUP => $pluginInfo[PSNG_URLINFO_GROUP]);
			    	$addToStorage->init(array (PSNG_DELEGATE_DEFAULTPARAMS => $defParams, PSNG_PLUGIN_INIT_STORAGE => & $this->storage));
			    	if ($plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $pluginInfo, PSNG_PLUGIN_INIT_STORAGE => & $addToStorage))) {
						$this->layout->addText('<p><b>Starting plugin ' . $pluginInfo[PSNG_PLUGIN_TITLE] . '</b><br />Class: '.ucfirst($pluginInfo[PSNG_PLUGIN_CLASS])."<br />\n");
						$numbResult = $plugin->run();
						$this->layout->addText('Number of added urls: ' . $numbResult.'</p>');
						$plugin->tearDown();
			    	} else {
			    		$this->layout->addError("Error in initialisation of class $className!");
			    	}    	    		
				}
		    }
    	}
    	$this->layout->addText('','Finished invoking of all plugin instances');
	} 
	
	/**
	 * run all output plugin instances and display result
	 */
	function displayRunOutput($plugins) {
    	require_once(PSNG_CLASS_PLUGIN_OUTPUT);
    	require_once(PSNG_CLASS_DELEGATE);
    	require_once(PSNG_CLASS_DELEGATE_GETFROMSTORAGE);

    	$this->layout->addText('', 'Invoking all output plugin instances');
    	if (is_array($plugins[PSNG_PLUGIN_TYPE_OUTPUT])) {
	    	foreach ($plugins[PSNG_PLUGIN_TYPE_OUTPUT] as $className => $plug) {
	    		foreach ($plug as $title => $pluginInfo) {
			    	$plugin = PluginHandler::getPlugin($this->settings->getValue(PSNG_DIRECTORY_CLASSES).'/output/', $pluginInfo[PSNG_PLUGIN_CLASS]);
			    	if (!is_object($plugin))  { 
			    		$this->layout->addError("Couldn't instantiate an object for class $className!");
			    		return;
			    	}
			    	$getFromStorage = new GetFromStorage();
			    	$getFromStorage->init(array (PSNG_DELEGATE_DEFAULTPARAMS => array(), PSNG_PLUGIN_INIT_STORAGE => & $this->storage));
			    	
	    	    	if ($plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $pluginInfo, PSNG_PLUGIN_INIT_STORAGE => & $getFromStorage))) {
						$this->layout->addText('<p><b>Starting plugin ' . $pluginInfo[PSNG_PLUGIN_TITLE] . '</b><br />Class: '.ucfirst($pluginInfo[PSNG_PLUGIN_CLASS])."<br />\n");
						$numbResult = $plugin->run();
						$this->layout->addText('Number of computed urls: ' . $numbResult.'</p>');
	    	    	} else {
			    		$this->layout->addError("Error in initialisation of class $className!");
	    	    	}    	    		
	    		}
		    }
    	}
    	$this->layout->addText('','Finished invoking of all plugin instances');
	} 

	/**
	 * displays an overview of all available plugins
	 */
    function displayPluginOverview() {
    	require_once(PSNG_CLASS_PLUGIN_INPUT);
    	require_once(PSNG_CLASS_PLUGIN_OUTPUT);
    	require_once(PSNG_CLASS_PLUGIN_COMPUTE);

		$plugins = array();
    	$classDir = $this->settings->getValue(PSNG_DIRECTORY_CLASSES);
    	$plugins[PSNG_PLUGIN_TYPE_INPUT] = PluginHandler::getPlugins($classDir.'/input/','Input');
    	$plugins[PSNG_PLUGIN_TYPE_COMPUTE] = PluginHandler::getPlugins($classDir.'/compute/','Compute');
    	$plugins[PSNG_PLUGIN_TYPE_OUTPUT] = PluginHandler::getPlugins($classDir.'/output/','Output');

		$this->layout->addText('<h2>Available plugins</h2>');
    	// output all plugin classes
		foreach ($plugins as $typ => $plugs) {
    		switch ($typ) {
    			case PSNG_PLUGIN_TYPE_INPUT: $typ_str = "Input"; break;
    			case PSNG_PLUGIN_TYPE_OUTPUT: $typ_str = "Output"; break;
    			case PSNG_PLUGIN_TYPE_COMPUTE: $typ_str = "Compute"; break;
    		}
			if (is_array($plugs) && count($plugs) > 0) {
				$this->layout->addText('', $typ_str);				
		    	foreach ($plugs as $sP) {
		    		$info = $sP->getInfo();
		    		$this->layout->addText("<p> ". $info['name'] .' '. $info['version'] . '&nbsp; <a href="'.$this->settings->getValue(PSNG_SCRIPT).
										'?'.PSNG_ACTION.'='.PSNG_ACTION_PLUGIN_NEWINSTANCE.
										'&'.PSNG_PLUGIN_TYPE.'='.$typ.
										'&'.PSNG_PLUGIN_CLASS.'='.get_class($sP).
										'" title="Create a new instance">[new instance]</a>'."<br>\n".
										'<font size="-1">'. $info['short_description']  ."</font></p>\n");
		    	} // foreach
			} // if
    	}
		$this->layout->addText('<h2>Instances</h2>');
    	// output all plugin instances
    	$types = $this->settings->getValue(PSNG_SETTINGS_PLUGIN);
    	if (!is_null($types) && count($types) > 0) { // are there any types?
	    	foreach ($types as $type => $classes) {
	    		switch ($type) {
	    			case PSNG_PLUGIN_TYPE_INPUT: $typ_str = "Input"; break;
	    			case PSNG_PLUGIN_TYPE_OUTPUT: $typ_str = "Output"; break;
	    			case PSNG_PLUGIN_TYPE_COMPUTE: $typ_str = "Compute"; break;
	    		}
		    	if (!is_null($classes) && count($classes) > 0) { 				// are there any classes?
					$this->layout->addText('', $typ_str);				
			    	foreach ($classes as $class => $instances) { 				// iterate over classes
				    	if (!is_null($instances) && count($instances) > 0) { 	// are there any instances? 
				    		$this->layout->addText("<p><u>".ucfirst($class)."</u></p>");				// yes, there are => output current class
			    			foreach ($instances as $pluginid => $instance) {		// iterate over instances of current class and type
				    			if (is_null($instance[PSNG_PLUGIN_CLASS])) continue;		// do not display unset values (senseless?)
				    			if ($instance[PSNG_PLUGIN_ID] == '') $instance[PSNG_PLUGIN_ID] = $pluginid; // we have to set the plugin id manually
				    			$this->layout->addText("<p> ".$instance[PSNG_PLUGIN_TITLE]." - plugin of class: " . 
				    							$instance[PSNG_PLUGIN_CLASS] . '&nbsp; ' .
		    									'<a href="'.$this->settings->getValue(PSNG_SCRIPT).
												'?'.PSNG_ACTION.'='.PSNG_ACTION_PLUGIN_SETUP.
												'&'.PSNG_PLUGIN_TYPE.'='.$instance[PSNG_PLUGIN_TYPE].
												'&'.PSNG_PLUGIN_CLASS.'='.$instance[PSNG_PLUGIN_CLASS].
												'&'.PSNG_PLUGIN_ID.'='.$instance[PSNG_PLUGIN_ID].'">[settings]</a> ' .
		    									'<a href="'.$this->settings->getValue(PSNG_SCRIPT).
												'?'.PSNG_ACTION.'='.PSNG_ACTION_PLUGIN_REMOVE.
												'&'.PSNG_PLUGIN_TYPE.'='.$instance[PSNG_PLUGIN_TYPE].
												'&'.PSNG_PLUGIN_CLASS.'='.$instance[PSNG_PLUGIN_CLASS].
												'&'.PSNG_PLUGIN_ID.'='.$instance[PSNG_PLUGIN_ID].'">[delete]</a> ' .
												"<br></p>\n");
			    			}    		
		    			}    		
		    		}
		    	}
	    	}
    	}    	
    }
    
    /**
     * 
     */
    function displayPluginSetup($className, $settings) {
    	require_once(PSNG_CLASS_PLUGIN_INPUT);
    	require_once(PSNG_CLASS_PLUGIN_OUTPUT);
    	require_once(PSNG_CLASS_PLUGIN_COMPUTE);
    	require_once(PSNG_CLASS_DELEGATE);
		require_once(PSNG_CLASS_DELEGATE_ADDTOSTORAGE);
		require_once(PSNG_CLASS_DELEGATE_GETFROMSTORAGE);
    	
    	// get include dir according to given type => could be done better :/		
    	switch ($settings[PSNG_PLUGIN_TYPE]) {
    		case PSNG_PLUGIN_TYPE_COMPUTE: 	$dir = 'compute'; break;
    		case PSNG_PLUGIN_TYPE_INPUT: 	$dir = 'input'; break;
    		case PSNG_PLUGIN_TYPE_OUTPUT: 	$dir = 'output'; break;
    		case 'compute' : $dir = 'compute'; $settings[PSNG_PLUGIN_TYPE] = PSNG_PLUGIN_TYPE_COMPUTE; break;
    		case 'input' : $dir = 'input'; $settings[PSNG_PLUGIN_TYPE] = PSNG_PLUGIN_TYPE_INPUT; break;
    		case 'output' : $dir = 'output'; $settings[PSNG_PLUGIN_TYPE] = PSNG_PLUGIN_TYPE_OUTPUT; break;
    	}
    	$plugin = PluginHandler::getPlugin($this->settings->getValue(PSNG_DIRECTORY_CLASSES).'/'.strtolower($dir).'/', $className);
    	if (!is_object($plugin))  { 
    		$this->layout->addError("Couldn't instantiate an object for class $className of type $type!");
    		return;
    	}
    	
    	
    	$addToStorage = new AddToStorage();
    	$defParams = array(PSNG_URLINFO_PLUGIN_ADDED => $settings[PSNG_PLUGIN_TYPE], PSNG_URLINFO_GROUP => $settings[PSNG_URLINFO_GROUP]);
    	$addToStorage->init(array (PSNG_DELEGATE_DEFAULTPARAMS => $defParams, PSNG_PLUGIN_INIT_STORAGE => & $this->storage));

		$getFromStorage = new GetFromStorage();
		$getFromStorage->init(array (PSNG_DELEGATE_DEFAULTPARAMS => array(), PSNG_PLUGIN_INIT_STORAGE => & $this->storage));

    	$type = $settings[PSNG_PLUGIN_TYPE];
    	unset($settings[PSNG_PLUGIN_TYPE]);
    	$pluginid = $settings[PSNG_PLUGIN_ID];
    	unset($settings[PSNG_PLUGIN_ID]);
    	$urlinfoGroup = $settings[PSNG_URLINFO_GROUP];
    	unset($settings[PSNG_URLINFO_GROUP]);
    	$pluginTitle = $settings[PSNG_PLUGIN_TITLE];
    	unset($settings[PSNG_URLINFO_TITLE]);
    	switch ($settings[PSNG_PLUGIN_TYPE]) {
    		case PSNG_PLUGIN_TYPE_INPUT: 
    			$plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $settings, PSNG_PLUGIN_INIT_STORAGE => & $addToStorage)); 
    			break;
    		case PSNG_PLUGIN_TYPE_OUTPUT:
    			$plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $settings, PSNG_PLUGIN_INIT_STORAGE => & $getFromStorage));
    			break;
    	
    		case PSNG_PLUGIN_TYPE_COMPUTE:
    		// TODO what to do with compute plugins? need both (add, get)!
    			$plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $settings, PSNG_PLUGIN_INIT_STORAGE => & $addToStorage));
    			break;
    	}
    	$plugin->init(array(PSNG_PLUGIN_INIT_SETTINGS => $settings, PSNG_PLUGIN_INIT_STORAGE => & $getFromStorage));
    	$setupCode = $plugin->getSetupHtml();

//TODO: Test    	$setupCode = strip_tags($setupCode, array('<table>','<tr>','<td>','<input>','<textarea>','<p>','<br>'));

    	// TODO add legend around
    	$this->layout->addText('<form action="'.$this->settings->getValue(PSNG_SCRIPT).'">');
		$this->layout->addText('<input type="hidden" name="'.PSNG_ACTION.'" value="'.PSNG_ACTION_PLUGIN_SETUP.'" />');
		$this->layout->addText('<input type="hidden" name="'.PSNG_PLUGIN_TYPE.'" value="'.$type.'" />');
		$this->layout->addText('<input type="hidden" name="'.PSNG_PLUGIN_INIT_SETTINGS.'['.PSNG_PLUGIN_TYPE.']" value="'.$type.'" />');
		$this->layout->addText('<input type="hidden" name="'.PSNG_PLUGIN_INIT_SETTINGS.'['.PSNG_PLUGIN_CLASS.']" value="'.get_class($plugin).'" />');
		$this->layout->addText('<input type="hidden" name="'.PSNG_PLUGIN_INIT_SETTINGS.'['.PSNG_PLUGIN_ID.']" value="'.$pluginid.'" />');
		$this->layout->addText('<b>Title of plugin:</b> <input type="text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'['.PSNG_PLUGIN_TITLE.']" value="'.$pluginTitle.'" />');
		$this->layout->addText('<b>Group of result:</b> <input type="text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'['.PSNG_URLINFO_GROUP.']" value="'.$urlinfoGroup.'" />');
		$this->layout->addText('<input type="Submit" value="Submit Settings" name="'.PSNG_ACTION_PLUGIN_SETUP.'" />');
    	$this->layout->addText($setupCode);
    	$this->layout->addText('</form>');
    }
    
    function addText($msg, $title = '') {
    	return $this->layout->addText($msg, $title);
    }

    function addError($msg, $title = '') {
    	return $this->layout->addError($msg, $title);
    }
    
	function addInfo($msg, $title = "") {
		return $this->layout->addInfo($msg, $title);
	}     

	function addDebug($msg, $title = "") {
		return $this->layout->addDebug($msg, $title);
	}     
	
	function tearDown() {
		$this->layout->addText('</div>'); // class="body"	
		print $this->layout->getFooterLayout();
	}
    
}
?>