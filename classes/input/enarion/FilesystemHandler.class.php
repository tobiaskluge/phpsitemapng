<?php
/**
 * this is a filesystem handler that scans the filesystem for files
 * that match the given restrictions
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

class FilesystemHandler {
	var $files = array();
	var $done = array();
	var $todo = array();
	
	var $forbidden_dir = array();
	var $forbidden_files = array();
	
	var $filesystem_base = '';
	var $directory_offset = '';
	var $cur_item = 0;
	var $deadline;
	var $numbOfResult;	
	
	var $storage;		// reference to storage delegate
	 
	/**
	 * public constructor, set initial values
	 */
    function FilesystemHandler($filesystem_base,  $deadline = 0, $directory_offset = '') {
		$this->filesystem_base = $filesystem_base;
		$this->directory_offset = $directory_offset;
		$this->deadline = $deadline; 
    }    
    
    /**
     * empty space, shut down this object
     */
    function tearDown() {
    	// lazy, only unset big variables
    	unset($this->files);
    	$this->files = null;
    	unset($this->done);
    	$this->done = null;
    	unset($this->todo);
    	$this->todo = null;
    }
    
    /**
     * returns number of files
     */
    function size() {
    	return (count($this->files)>0) ? count($this->files) : $this->numbOfResult;
    }
    
    /**
     * returns true when the current item is not the last item
     * behaves like in java
     */
    function hasNext() {
    	if ($this->size() > $this->cur_item) return true;
    	return false;
    }
    
    function hasFinished() {
    	return (count($this->todo) == 0);
    }
    
    /**
     * returns the current item
     * behaves like in java
     */    
    function getNext() {
    	if ($this->hasNext()) {
    		$tmp = $this->files[$this->cur_item];
    		$this->cur_item++;
    		return $tmp;
    	}
    	return null;    		
    }
    
	function getTodo() {
		return $this->todo;
	}

	function getFiles() {
		return $this->files;
	}
	
	function getDone() {
		return $this->done;
	}

	function setTodo($todo) {
		$this->todo = $todo;
	}

	function setFiles($files) {
		$this->files = $files;
	}
	
	
	function setDone($done) {
		$this->done = $done;
	}
		
	function setStorage(& $storage) {
		$this->storage = & $storage;
	}
	
    /**
     * set list of forbidden directories
     */
    function setForbiddenDirectories($directories = array()) {
    	$this->forbidden_dir = $directories;	
    }
	
	/**
	 * set list of forbidden files
	 */
	function setForbiddenFiles($files = array()) {
		$this->forbidden_files = $files;	
	}    

	/**
	 * scans filesystem for files that match the setted conditions
	 * 
	 * return number of files that have been found
	 */
    function start() {
     	reset($this->todo);
     	while(($this->deadline == 0) || (($this->microtime_float() - $this->deadline) <= 0)) {
     		$url = array_pop($this->todo);
     		if (is_null($url) || $url == '') break;
     		$this->_getFiles($url);
     	}
     	$this->files = $this->_changeOffset($this->done, $this->filesystem_base, $this->directory_offset);
		reset($this->files);
		ksort($this->files);
		reset($this->files);
    	return $this->size();
    }
    
	function microtime_float() {
   		list($usec, $sec) = explode(" ", microtime());
   		return ((float)$usec + (float)$sec);
	}    

    /**
     * return last modification time
     */
    function getLastModificationTime($filename) {
    	$lastmod = @filemtime($filename);
    	// if filemtime failed (for any reason), set to empty string (= unset)
		if (!((!is_null($lastmod)) && is_integer($lastmod) && $lastmod > 0)) $lastmod = ''; 
    	
		return $lastmod;
    	
    }
  
  
/* some private functions */

	/* this function changes a substring($old_offset) of each array element to $offset */
	function _changeOffset($array, $old_offset, $offset) {
	  $res = array();
	  if (is_array($array) && count($array) > 0) {
	    foreach ($array as $id => $val) {
	      $res[] = str_replace($old_offset, $offset, $val);
	    }
	  }
	  return $res;
	}

	/**
	 * future: only allowed masking char: * (before and/or after search string)
	 * 
	 * TODO check this with more data
	 */
	function checkFileName($filename) {
		$filename = substr($filename, strrpos($filename, '/')+1);
	    if (is_array($this->forbidden_files) && count($this->forbidden_files) > 0) {
	    	foreach ($this->forbidden_files as $id => $file) {
	    		if ($file == '') continue;
	    		$pos = strpos($filename, $file);
/*	    		$file_search = '';
		  		if (!(($as = strpos($file, '*')) === FALSE)) {
		  			$file_search = str_replace('*', '', $file);
	  				if ($as == 0) $pos = @strpos($filename, $file_search, (strlen($filename)-strlen($file_search)));
	  				if ($as == strlen($file_search)) $pos = (@strpos($filename, $file_search) != 0);
		  		} else {
					$pos = ($filename === $file);
		  		}
*/		  		if ($pos === FALSE) continue;
		  		return true;
	    	}
	  	}
	  	return false;		
	}

	function checkDirectoryName($directory) {
		$directory = substr($directory, 0, strrpos($directory, '/')); // with last "/"
				// dirname($directory); // 
	    if (is_array($this->forbidden_dir) && count($this->forbidden_dir) > 0) {
	    	foreach ($this->forbidden_dir as $id => $dir) {
	    		if ($dir == '') continue;
	    		$pos = strpos($directory, $dir);
/*	    		$dir_search = '';
		  		if (!(($as = strpos($dir, '*')) === FALSE)) {
		  			$dir_search = str_replace('*', '', $dir);
	  				if ($as == 0) $pos = @strpos($directory, $dir_search, (strlen($directory)-strlen($dir_search)));
	  				if ($as == strlen($dir_search)) $pos = (@strpos($directory, $dir_search) != 0);
		  		} else {
					$pos = ($directory === $dir);
		  		}
		  		// echo "directory: $directory, dir: $dir, dir_search: $dir_search, pos: $pos<br>\n";
*/		  		if ($pos === FALSE) continue;
		  		return true;
	    	}
	  	}
	  	return false;		
	}

	/**
	 * searches for files and adds them to $this->files; adds directories to $this->todo
	 * algorithm: breadth first search (former algorithm: dfs)
	 */
	function _getFiles($directory) {
	   	if($dir = opendir($directory)) {	
	       while(false !== ($file = readdir($dir))) {
	       		if ($file == '..' || $file == '.' || $file[0] == '.') continue;
	       		
	       		//TODO maybe adapt this to php running on windows 
	       		if (substr($directory, -1) != '/') {
	       			$filename = $directory .'/' . $file;
	       		} else { 
		       		$filename = $directory . $file;
	       		}
	       		
			   // If $file is a directory, add it to the todo list
				 if(@is_dir($filename)) {
				 	$filename = $filename .'/';
					if ($this->checkDirectoryName($filename)) continue;
					array_push($this->todo, $filename);
				} else {  // is a file, add it to done list
					if ($this->checkFileName($filename)) continue;
					if (isset($this->storage)) {
						// store urlinfo into storage object
						$this->storage->fire(array(
								PSNG_URLINFO_URL => str_replace($this->filesystem_base, $this->directory_offset, $filename), 
								PSNG_URLINFO_FILENAME => $filename,
								PSNG_URLINFO_LASTMOD => $this->getLastModificationTime($filename),
								PSNG_URLINFO_ENABLED => 1
						));
						$this->numbOfResult++;
					} else {
						// store url into result array (deprecated)
			    		array_push($this->done, $filename);
					}
				}
	       }
	       closedir($dir);
	       return true;
	   }
	}
    
}
?>