<?php
/**
 * this class handles the Google and Google Sitemaps output
 * in details:
 *   - generate google sitemaps files
 *   - generate google sitemaps index files
 * 
 * This code is partially based on the GsgXml class developed by
 * 		Zervaas Enterprises (www.zervaas.com.au)
 * 		http://sourceforge.net/projects/zervsitemapgen/
 *  
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-08-20
 */
class GSitemapsWriter {
	var $baseUrl = '';					// holds the baseUrl of the sitemaps
	var $baseOutputDir = '';			// holds the base directory where the generated files will be written into
	var $compressOutput = false;		// if true, output will be gzcompressed
	var $sitemapsIndex = array();		// if != null, sitemapsIndex files will be generated instead
	var $useDateTimeFormat = true;		// if false, added urls with lastmod timestamp will be converted to lastmod date only - otherwise lastmod datetime   
	var $strict = false;				// be strict: return warnings and don't go further
	var $strictSimpleSitemaps = false;	// create only one Google Sitemaps file without Sitemaps Index 
	var $files = array();				// contains the file information used by _getFiles; 2dimensional (1st key: strategy key, 2nd key: number of file)
	var $baseFilename = 'gsitemaps';	// base part of the sitemaps filename
	var $maxNumbOfUrls = 50000;			// number of url entries that will be written into a file
	var $initDone = false;				// indicates if changing of the initialisation settings are allowed or not (will be set to true when the first url is added)
	var $sitemapsIndexFilename = null;
	var $stylesheetUrl = '';			// contains the url of the directory where the gs stylesheet is stored

	/**
	 * this creates an instance of the GSitemapsWriter class
	 *    by default - the sitemap will be uncompressed and 
	 * 	  only a Google sitemaps file will be generated
	 *    long DateTime format will be used (with time information)
	 * @param String baseUrl the baseurl of all urls
	 * @param String baseOutputDir the directory where the generated file(s) will be written into
	 * @return object instance of GSitemapsWriter class
	 */
    function GSitemapsWriter($baseUrl, $baseOutputDir, $baseFilename = 'gsitemaps', $maxNumbOfUrls = 50000) {
        $this->baseUrl = strtolower($baseUrl);
        $this->baseOutputDir = $baseOutputDir;
		if ($baseFilename != '') $this->baseFilename = $baseFilename;
		if ($maxNumbOfUrls != '' && is_numeric($maxNumbOfUrls)) $this->maxNumbOfUrls = $maxNumbOfUrls;
		$this->sitemapsIndex['strategy'] = 'incomming';
    }
    
    /**
     * set output mode to gzencoded
     *  => only available before first url has been added
     * @return void 
     */
    function initCompressOutput() {
    	if (! $this->initDone) $this->compressOutput = true;
    }
    
    /**
     * set global DateTime format to short format
     */
    function initUseShortDateTimeFormat() {
    	if (! $this->initDone) $this->useDateTimeFormat = false;
    }
    
    /**
     * add stylesheet headers to generated files
     *  => only available before first url has been added
     * 
     * @param String stylesheetUrl the base url where the gss files are stored into
     * @return void 
     */
    function initAddStylesheetHeaders($stylesheetUrl) {
    	if (! $this->initDone) $this->stylesheetUrl = $stylesheetUrl;
    }
    
    /**
     * init function: set strict mode - break on warnings
     * @return void
     */
    function initStrictOnWarning() {
    	if (! $this->initDone) $this->strict = true;
    }
    /**
     * init function: set sitemaps strict mode: write only one simple G sitemaps file; 
     * 		if full - return error
     * will remove already set Sitemaps Index values!!!
     * 
     * @return void
     */
    function initStrictOnSimpleSitemaps() {
    	if (! $this->initDone) {
    		$this->strictSimpleSitemaps = true;
	    	unset($this->sitemapsIndex);
	    	$this->sitemapsIndex = null;
    	}
    }
    function initSetSitemapsIndexFilename($absFilename) {
    	if (! $this->initDone) {
			$this->sitemapsIndexFilename = $absFilename;
    	}    	
    }
    
    /**
     * set output files to Google Sitemaps index files
     *  => only available before first url has been added
     * will NOT be set when initStrictSimpleSitemaps hasn't been invoked so fare
     * 
     * @param String groupby which field the sitemaps index should be grouped by (available: lastmod, changefreq, incomming(ordering after incomming urls; default))  
     * @param String sitemapsIndexBase this will be used as base for the entries written to the generated sitemaps index file (default: baseUrl)
     * @param int maxNumbOfUrls the maximal number of urls that will be added to a sitemaps file (default: 50 000)
     * @return void 
     */
    function initGenerateSitemapsIndex($groupby = '') {
    	if (!$this->initDone && !$this->strictSimpleSitemaps) {
    		$this->strictSimpleSitemaps = false;
	    	$this->sitemapsIndex = array();
			switch ($groupby) {
				case 'changefreq':
					$this->sitemapsIndex['strategy'] = 'changefreq';
					break;
	
				case 'lastmod':
					$this->sitemapsIndex['strategy'] = 'lastmod';
					break;
					
				case 'incomming':
				default:
					$this->sitemapsIndex['strategy'] = 'incomming';
					break;
			}
    	}
    }
    
    /**
     * add url to Google sitemap
     * 
     * @param String url url of the entry to add 
     * @param int lastmod timestamp(int value)
     * @param String changefreq the Google changefreq strings
     * @param float priority the priority of the added url
     * @return String empty String if succeed, reason if failed  
     */
    function addURL($url, $lastmod='', $changefreq='', $priority='') {
    	if (! $this->initDone) $this->initDone = true; // disallow init functions
    	
    	// check parameters first before working with them:
    	$error = $this->_checkSitemapsValue(& $url, &$lastmod, & $changefreq, & $priority); // TODO check if reference parameters are working => updated in function
    	if (isset($error) && $error != '') return $error;
    	
    	// select file that should be used to add this url
    	$fileInfo = $this->_getFileKeys($url, $lastmod, $changefreq, $priority);
		if (is_string($fileInfo)) return $fileInfo; // error occured    	
    	$strategy_key = $fileInfo['strategy_key'];
    	$number = $fileInfo['number'];

    	// write content
    	$this->_writeStringIntoFile(
			$this->files[$strategy_key][$number]['filehandle'],
			$this->_getSitemapsEntry($url, $lastmod, $changefreq, $priority)
		);
		    	
		// update file counter		
		$this->files[$strategy_key][$number]['count']++;

    	// check if we have to update lastmod date (urlinfo contains lastmod date)
		if ($lastmod > $this->files[$strategy_key][$number]['lastmod']) {
			$this->files[$strategy_key][$number]['lastmod'] = $lastmod;
		}
    	
    	// that's it, but we're tidy - so check if we have to close the file (enough files)    	
    	if ($this->files[$strategy_key][$number]['count'] >= $this->maxNumbOfUrls) {
			$this->_closeSitemapsFile($strategy_key, $number);    	
		}
    	
    }
    
    /**
     * return an array of files that have been created
     * 
     * @return array of files that have been generated (key: filename, value: fileInfo-array)
     */
    function getFiles() {
    	$result = array();
    	foreach ($this->files as $strategy_key => $fileNumbers) {
    		foreach ($fileNumbers as $number => $fileInfo) {
//    			print_r($fileInfo);
    			// clean fileInfo entry before we're adding this to the result array
    			// (is a copy, not a reference - so we can use unset!)
    			unset($fileInfo['filehandle']);
    			$result[$fileInfo['filename']] = $fileInfo;
    		}
    	}
    	return array();
    }
    
    /**
     * tear down GSitemaps writer class
     *  
     * @return void 
     */
    function tearDown() {
    	// var_dump($this);
    	$result = "";
	// create sitemaps index file
	if ($this->strictSimpleSitemaps != true) {
		//echo "creating sitemaps index file now!<br>\n";
		$result = $this->_createSitemapsIndexFile(); // TODO handle error when sitemaps index cannot be written!
	}		

    	// close open files
    	foreach ($this->files as $strategy_key => $fileNumbers) {
    		foreach ($fileNumbers as $number => $fileInfo) {
    			if ($fileInfo['status'] != 'closed') {
    				$this->_closeSitemapsFile($strategy_key, $number);
    			}
    		}
    	}

	return $result;
   }
   
   /**
    * creates a sitemaps index files for current content
    * 
    * @return boolean true if succeed, false otherwise
    */
   function _createSitemapsIndexFile() {
   		if (isset($this->sitemapsIndexFilename) && strlen($this->sitemapsIndexFilename) > 0 ) {
    			// use the sitemaps file that has been set in init method
    			$filename_abs = $this->sitemapsIndexFilename;
			if ($this->compressOutput && substr($filename_abs,-3) != '.gz') $filename_abs .= '.gz';
   		} else {
   			// use default sitemaps file
    			$filename = $this->baseFilename . '_index.xml';
	    		if ($this->compressOutput) $filename .= '.gz';
	    		$filename_abs = $this->baseOutputDir . '/' . $filename;
   		}
   		echo "<p>sitemaps index file: $filename_abs</p>\n";

	// try to open file    	
		// check if file exists - if yes, perform tests:
		if (file_exists($filename_abs)) {
			// check if file is accessable
			if (!is_writable($filename_abs)) return "File $filename_abs is not writable";
		} // file does not exist, try to create file
   	
    	if ($this->compressOutput) {
    		$filehandle = @gzopen($filename_abs, 'wb');
    	} else {
    		$filehandle = @fopen ($filename_abs, 'w+');
    	}
		
		if ($filehandle === FALSE) {
			@fclose($filehandle);
			if (!file_exists($filename_abs)) {
				return "File $filename_abs does not exist and I do not have the rights to create it!";
			}		
			return "File $filename_abs could not be opened, don't know why";
		}

		$headers = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		if ($this->stylesheetUrl != '') $headers .= '<?xml-stylesheet type="text/xsl" href="'.$this->_xmlEscape($this->stylesheetUrl).'gss.xsl"?>'."\n";
		$headers .= '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">'."\n";
        if (defined('PSNG_VERSION')) $headers .= '<!-- Created with phpSitemapNG '.PSNG_VERSION.' and GoogleSitemapsExport plugin -->' . "\n";
		
        $headers .= '<!-- Sitemap Index created ' . date('Y-m-d\TH:i:s') . substr(date("O"),0,3) . ":" . substr(date("O"),3) .
        				 ' with strategy '.$this->sitemapsIndex['strategy'].'-->'."\n";

   		// compute url offset between sitemaps index file and output dir:
   		$baseIndexDir = PSNG_PATH_DOCUMENTROOT; //dirname($filename_abs).'/'; // have to have the same root directories
		$urlOffset = substr($this->baseOutputDir, strlen($baseIndexDir));   		
//   		echo "baseIndexDir: $baseIndexDir, baseOutputDir: ".$this->baseOutputDir.", urlOffset: $urlOffset<br>\n";
		// compute baseurl for each sitemaps file
		$baseurl = $this->baseUrl;
		if ((substr($baseurl,-1) != '/')) $baseurl .= '/';
		if (substr($urlOffset,0,1) == '/') $urlOffset = substr($urlOffset, 1);
		$baseurl .= $urlOffset;
		if (substr($baseurl,-1) != '/') $baseurl .= '/';
			
		$this->_writeStringIntoFile($filehandle, $headers);

    	foreach ($this->files as $strategy_key => $fileNumbers) {
    		foreach ($fileNumbers as $number => $fileInfo) {
    				//$url = $this->baseUrl . $this->files[$strategy_key][$number]['filename'];
    				$url = $baseurl . $this->files[$strategy_key][$number]['filename'];
    				$lastmod = $this->files[$strategy_key][$number]['lastmod'];
		        	$lastmod = ($this->useDateTimeFormat) ?
                     (date('Y-m-d\TH:i:s', $lastmod) . substr(date("O", $lastmod),0,3) . ":" . substr(date("O",$lastmod),3))
                     : date('Y-m-d', $lastmod);
    				
    				$content = '
   <sitemap>
      <loc>'.$this->_xmlEscape($url).'</loc>
      <lastmod>'.$this->_xmlEscape($lastmod).'</lastmod>
   </sitemap>'."\n";
					$this->_writeStringIntoFile($filehandle, $content);
    		}
    	}

		$footer = '</sitemapindex>';
		$this->_writeStringIntoFile($filehandle, $footer);

    	if ($this->compressOutput) {
    		@gzclose($filehandle);
    	} else {
    		@fclose($filehandle);
    	}

		   	
   }
    
    /**
     * implements the strategy to select the appropriate file to write values into
     * 		thanks to God for His help and wisdom that made this function so easy! 
     * 
     * @return mixed 2dimensional array($strategy_key, $number) if succeed, string containing description if failed
     */
    function _getFileKeys($url, $lastmod, $changefreq, $priority) {
    	// TODO what is if strategy keys are empty? eg. unset lastmod
    	$strategy_key = 'incomming'; // defaut: incomming strategy
    	$number = null;	// default: compute number 
    	
    	if ($this->strictSimpleSitemaps) { // use strict simple sitemaps (only 1 sitemaps file => error when full!)
    		$strategy_key = 'simpleSitemaps';
    		$number = 0;
    	}
    	
    	if (is_null($this->sitemapsIndex) || $this->sitemapsIndex['strategy'] == 'incomming') { 
			$strategy_key = 'incomming';
    	}   	

    	if ($this->sitemapsIndex['strategy'] == 'changefreq') {
    		$strategy_key = $changefreq;
    	}

    	if ($this->sitemapsIndex['strategy'] == 'lastmod') {
    		$strategy_key = date("Ym", $lastmod); // use year and month as strategy key
    	}
    	
    	// final checks
		if (is_null($this->files[$strategy_key])) { // initialize stategy array (common)
			$this->files[$strategy_key] = array();
		}
    	if (isset($number)) { // we have a fixed number that is not allowed to change:
    		if (is_null($this->files[$strategy_key][$number])) {
    			$res = $this->_getSitemapsFile($strategy_key . "-" . $number);
    			if (is_array($res)) {
	    			$this->files[$strategy_key][$number] = $res;
    			} else {
    				return $res; // error
    			}
    		}
    		if ($this->files[$strategy_key][$number]['count'] >= $this->maxNumbOfUrls) { 
    			return "Google Sitemaps file is full, contains already " . $this->files[$strategy_key][$number]['count'] 
    					. " entries, but only " . $this->maxNumbOfUrls . "!";
    		}
    	} else { // compute $number
			if (count($this->files[$strategy_key]) == 0) { // initialize strategy key (special)
				$number = 0;
    			$res = $this->_getSitemapsFile($strategy_key . "-" . $number);
    			if (is_array($res)) {
	    			$this->files[$strategy_key][$number] = $res;
    			} else {
    				return $res; // error
    			}
    			// that's it, this entry is fine now
    		} else { // compute number and maybe add new file
				$numbers = array_keys($this->files[$strategy_key]);
	    		foreach ($numbers as $id => $numb) {
	    			 if ($this->files[$strategy_key][$numb]['count'] < $this->maxNumbOfUrls) {
	    			 	$number = $numb;
	    			 	break; // that's it, we have the number we need
	    			 }
	    		}
	    		if (is_null($number)) { // we have to compute the next available number
	    			$number = array_pop($numbers);
	    			do {
		    			$number++;
		    			if (is_null($this->files[$strategy_key][$number])) {
			    			$res = $this->_getSitemapsFile($strategy_key . "-" . $number);
			    			if (is_array($res)) {
				    			$this->files[$strategy_key][$number] = $res;
			    			} else {
			    				return $res; // error
			    			}
		    				break;
		    			}
					} while (true);
	    		}
    		}// compute number and maybe add new file
    	} // end of final check
    	
    	return array('strategy_key' => $strategy_key, 'number' => $number);
    } 
     
    /**
     * creates a file depending on given filenamekey, baseDirectory and baseFilename
     * 
     * 
     * @return mixed array with basic informations and the filehandle if succeed, string containing the error message if failed
     */
    function _getSitemapsFile($filenameKey) {
    	if ($this->strictSimpleSitemaps) { // only 1 sitemaps file
    		$filename = $filename = $this->baseFilename . '.xml';
    	} else { // sitemaps index
    		$filename = $this->baseFilename . '-' . $filenameKey . '.xml';
    	}
    	if ($this->compressOutput) $filename .= '.gz';
    	$filename_abs = $this->baseOutputDir . '/' . $filename;

	// try to open file    	
		// check if file exists - if yes, perform tests:
		if (file_exists($filename_abs)) {
			// check if file is accessable
			if (!is_writable($filename_abs)) return "File $filename_abs is not writable";
		} // file does not exist, try to create file

    	if ($this->compressOutput) {
    		$filehandle = gzopen($filename_abs, 'wb');
    	} else {
    		$filehandle = @fopen ($filename_abs, 'w');
    	}
		
		if ($filehandle === FALSE) {
			@fclose($filehandle);
			if (!file_exists($filename_abs)) {
				return "File $filename_abs does not exist and I do not have the rights to create it!";
			}		
			return "File $filename_abs could not be opened, don't know why";
		}
		
	// write xml headers
        $headers  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		if ($this->stylesheetUrl != '') $headers .= '<?xml-stylesheet type="text/xsl" href="'.$this->_xmlEscape($this->stylesheetUrl).'gss.xsl"?>'."\n";
        $headers .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" ' ."\n".
        		'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' ."\n" .
        		'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'."\n";
        
        if (defined('PSNG_VERSION')) $headers .= '<!-- Created with phpSitemapNG '.PSNG_VERSION.' and GoogleSitemaps Export plugin -->' . "\n";
        $headers .= '<!-- Sitemap created ' . date('Y-m-d\TH:i:s') . substr(date("O"),0,3) . ":" . substr(date("O"),3) . ' -->'."\n";
		// TODO add headers for xslt stylesheet information here		
    	
    	$this->_writeStringIntoFile($filehandle, $headers);    	

	// setup result
		$result = array();
		$result['count'] = 0;
		$result['filehandle'] = $filehandle;
		$result['filename_abs'] = $filename_abs;
		$result['filename'] = $filename;
		$result['lastmod'] = 0;
		$result['status'] = 'open';
		
		return $result;
    }
    
    /**
     * add xml footer, closes file 
     * @param string strategy_key array key for strategy
     * @param string number the number of the sitemaps file for given strategy key 
     * @return boolean true if succeed 
     */
    function _closeSitemapsFile($strategy_key, $number) {
    	$fh = $this->files[$strategy_key][$number]['filehandle'];
    	
    	$this->_writeStringIntoFile($fh, '</urlset>');    	
    	
    	if ($this->compressOutput) {
    		@gzclose($fh);
    	} else {
    		@fclose($fh);
    	}
    	
    	unset($this->files[$strategy_key][$number]['filehandle']);
    	$this->files[$strategy_key][$number]['filehandle'] = null;    	
    	$this->files[$strategy_key][$number]['status'] = 'closed';
    	
    	return true;
    }
    
    /**
     * writes given string according to compression settings
     * into given file
     * @param filehandle filehandle the filehandle of the file where the content should be written into
     * @param String content the content that should be written into the file
     */
    function _writeStringIntoFile($filehandle, $content) {
    	if ($this->compressOutput) {
    		gzwrite($filehandle, $content);
    	} else {
    		fwrite ($filehandle, $content);
    	}
    	return true;
    }
    /**
     * check given params for correctness
     *   => caution: the parameters are REFERENCES, no copied variable values!
     * 
     * @return mixed string if an error occured, void if correct or warning occured (but strict mode is not set)
     */
    function _checkSitemapsValue(&$url, &$lastmod, &$changefreq, &$priority) {
    	// echo "url: $url, lastmod: $lastmod, changefreq: $changefreq, priority: $priority<br>\n";
    	if (isset($url) && $url != '') {
            if ($this->baseUrl != substr($url, 0, strlen($this->baseUrl))) 
            	return "URL base doesn't match! Should be: ". $this->baseUrl . " - is: " . substr($url, 0, strlen($this->baseUrl));
    	} else {
			return 'URL is empty!';
    	}
    	    		
   		if (isset($changefreq) && $changefreq != '' && !in_array($changefreq, array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'))) {
   			if($this->strict) {
   				return 'Changefreq is not valid! Should be one of (always, hourly, daily, weekly, monthly, yearly, never) - is: ' . $changefreq;
   			} else {
   				$changefreq = null;
   			}
   		}
   		if (isset($lastmod) && $lastmod != '') {
    		if (is_numeric($lastmod)) {
    			// this is fine
        	} else {
	   			if($this->strict) {
	   				return 'Lastmod is invalid! Should timestamp as int - is: ' . $lastmod;
	   			} else {
	   				$lastmod = null;
	   			}
    		}
   		}
        if (isset($priority) && $priority != '') {
	        if (is_numeric($priority) && $priority >= 0 && $priority <= 1) {
		        // ok it's valid, now normalize the value
		        $tmp = floor($priority / 0.001);
		        $tmp = $priority - $tmp * 0.001;
		        $priority -= $tmp;
		
		        $priority = $priority;
		    } else {
	   			if($this->strict) {
	   				return 'Priority is invalid! Should be a float between 0.000 and 1.000 - is: ' . $priority;
	   			} else {
			    	$priority = null;
	   			}
	        }
    	}
    	
    	return null;    	
    }    	
    	

	/**
	 * private function that returns an xml entry for a given url
	 *  
     * @param String url url of the entry to add 
     * @param String lastmod timestamp(int value) or already formated time as String value(short/long format)
     * @param String changefreq the Google changefreq strings
     * @param float priority the priority of the added url
     * @return String represents valid xml urlentry tag for given url
	 */    
    function _getSitemapsEntry($url, $lastmod=null, $changefreq=null, $priority=null) {
		$result = '';
        $result .= '<url>'."\n";
        $result .= sprintf('<loc>%s</loc>', $this->_xmlEscape($url));
        if (isset($lastmod)) {
        	$lastmod = ($this->useDateTimeFormat) ?
                     (date('Y-m-d\TH:i:s', $lastmod) . substr(date("O", $lastmod),0,3) . ":" . substr(date("O",$lastmod),3))
                     : date('Y-m-d', $lastmod);
    		$result .= sprintf('<lastmod>%s</lastmod>', $this->_xmlEscape($lastmod));
        }
        if (isset($changefreq)) {       		
            $result .= sprintf('<changefreq>%s</changefreq>', $this->_xmlEscape($changefreq));
            
        }
        if (isset($priority)) {
            $priorityStr = sprintf('<priority>%s</priority>', '%01.3f');
            $result .= sprintf($priorityStr, $priority);
        }
        $result .= '</url>';
        return $result;
    }
    
    /**
     * private function xmlEscape that escapes strings with the correct xml encoding
     * => TAKEN FROM GsgXml
     *
     * Escapes a string to be used as XML cdata. Borrowed from PHP
     * manual comments on htmlentities()
     *
     * @see     http://www.php.net/htmlentities
     *
     * @param   string  $str        The string to escape
     * @return  string              The escaped string
     */
    function _xmlEscape($str)
    {
        static $trans;
        if (!isset($trans)) {
            $trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
            foreach ($trans as $key => $value)
                $trans[$key] = '&#'.ord($key).';';
            // dont translate the '&' in case it is part of &xxx;
            $trans[chr(38)] = '&';
        }
        return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/","&#38;" , strtr($str, $trans));
    }
    
}
?>