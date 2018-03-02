<?php
/**
 * this class handles plugins (returns instances for specified classes, ...)
 * 
 * 		information about the directory where the plugins are 
 * 		stored to and the common class of all plugins are 
 * 		used to get all available plugins
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
class PluginHandler {
    function PluginHandler() {
    }
    
    /**
     * this static function returns a list of plugins for given path
     * and name of the super class
     * 
     * @param string directory the directory where the plugins are stored in
     * @param string superClassName the name of the classes where the plugins are derived from
     * 
     * @return list of object of classes that extends the given super class
     */
    function getPlugins($directory, $superClassName) {
    	$pluginList = array();
    	
    	$classFiles = PluginHandler::_getClassFiles($directory);
    	
    	foreach ($classFiles as $classFile) {
    		// include class
    		include_once($classFile);
    		// create instance ?
    		$className = substr($classFile, strrpos($classFile, '/')+1, -strlen('.class.php')); 
    		if (!(class_exists($className))) {
    			continue;	
    		}
    		
    		$pluginObj = null;
    		@eval("\$pluginObj = new $className;");

    		// check if derives from superClassName	
    		if (is_subclass_of($pluginObj, $superClassName)) {
    			$pluginList[] = $pluginObj;	
    		}
    		
    	}
    	
    	return $pluginList;
    }
    
    /**
     * this function returns an instance of the given plugin class
     * 
     * @param string directory the directory where the source code file is stored
     * @param string className the name of the plugin class
     * 
     * @return object an instance of the requested class, null if an error occured 
     */
    function getPlugin($directory, $className) {
    	// problem: className contains an case intensitive name of the class, 
    	// but the file names are case sensitive (at least under linux)
    	// => we have to scan the files in the given directory for class files 
    	$classFiles = PluginHandler::_getClassFiles($directory);
    	
    	foreach($classFiles as $classFile) {
	    	if (strtolower(basename($classFile)) == strtolower($className.".class.php")) {
	    		include_once($classFile);
	    		$pluginObj = null;
	    		eval("\$pluginObj = new $className;");
	    		if (is_object($pluginObj) && get_class($pluginObj) == $className) {
	    			return $pluginObj;
	    		}	
	    	}
    	}
    	return null;
    }
    
    /**
     * private function that returns a list of files from filesystem
     * that have an extension ".class.php"
     * 
     * @param string directory the directory where the files are stored in 
     * @return array list of files with absolute filename
     */
    function _getClassFiles($directory) {
    	$classFiles = array();
	   	if($dir = opendir($directory)) {	
	       while(false !== ($file = readdir($dir))) {
	       		if ($file == '..' || $file == '.' || $file[0] == '.') continue;
	       		
	       		//TODO maybe adapt this to php running on windows 
	       		if (substr($directory, -1) != '/') {
	       			$filename = $directory .'/' . $file;
	       		} else { 
		       		$filename = $directory . $file;
	       		}
	       		
			   // check if filename is directory or file
				 if(@is_dir($filename)) {
				 	// do not scan (recursive) into directories -- for now
				 	continue;
				} else {  
					// this is a file, check name for extension .class.php
					if (substr($filename, (strlen($filename)-strlen('.class.php'))) == '.class.php') {
						array_push($classFiles, $filename);
					}
				} // if
	       } // while
	       closedir($dir);
	       return $classFiles;
	   }
	}
}
?>