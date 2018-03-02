<?php
/**
 * this is the crawler engine developed by enarion.net and
 * used in phpSitemapNG 1.x
 * 
 * TODO handle getting and sending of cookies 
 *			Format - in header: "Set-Cookie: $cookie_name=$cookie_value; path=$cookie_path"
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

class CrawlerHandler {
	var $host = '';
	var $protocol = '';
	var $forbiddenKeys = array ();
	var $forbidden_dir = array ();
	var $forbidden_files = array ();

	var $maxFilesize = 100000; 	// 100 kByte
	var $maxGetfileTime = 5; 	// 5 seconds time to read content of file
	var $socketTimeout = 3;		// 3 seconds waiting to establish socket connection
	var $fileCounter = 0;
	var $url = '';
	var $withWWW = false;
	var $cur_item = 0;
	var $keys = array ();

	var $files = array ();
	var $visitedUrls = array ();
	var $todo = array ();
	var $deadline;
	
	var $storage;	// reference to the AddToStorage delegate

	function CrawlerHandler($host, $deadline = 0, $params = array()) {
		// setup crawler parameters
		if (count($params)>0) {
			foreach ($params as $name => $value) {
				if ($name == 'maxFilesize') {
					$this->maxFilesize = $value;		
				} elseif ($name == 'maxGetFileTime') {
					$this->maxGetFileTime = $value;
				} elseif ($name == 'socketTimeout' ) {
					$this->socketTimeout = $value;
				}
			}
		}
		// setup crawler with url and host
		$url = parse_url($host);
		if ($url != false) {
			if ($url[scheme] != "") {
				$this->protocol = $url[scheme];
			} else {
				$this->protocol = "http";
			}
			$this->host = $url[host];
			if (substr($this->host, 0, 3) == 'www')
				$this->withWWW = true;
		}

		$this->url = $this->protocol.'://'.$this->host.'/';
		$this->todo[] = $this->url;
		$this->deadline = $deadline;
	}
	
    /**
     * empty space, shut down this object
     */
    function tearDown() {
    	// lazy, only unset big variables
    	unset($this->files);
    	$this->files = null;
    	unset($this->visitedUrls);
    	$this->visitedUrls = null;
    	unset($this->todo);
    	$this->todo = null;
    }
	

	/**
	 * crawles all files that are in the todo list
	 * algorithm: breadth first search (former algorithm: dfs)
	 */
	function start() {
		reset($this->todo);
		while (($this->deadline == 0) || (($this->deadline - $this->microtime_float()) > 0)) {
			$url = array_pop($this->todo);
			if (is_null($url) || $url == '') break;
			$this->_getFilesForURL($url);
		}
		ksort($this->files);
		reset($this->files);
		return count($this->files);
	}

	function microtime_float() {
		list ($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	function getTodo() {
		return $this->todo;
	}

	function getFiles() {
		return $this->files;
	}

	function getDone() {
		return $this->visitedUrls;
	}

	function setTodo($todo) {
		$this->todo = $todo;
	}

	function setFiles($files) {
		if (is_array($files))
			$this->files = $files;
	}

	function setDone($done) {
		$this->done = $done;
	}

	/**
	 * returns number of files
	 */
	function size() {
		return count($this->files);
	}

	function hasFinished() {
		return (count($this->todo) == 0);
	}
	/**
	 * returns true when the current item is not the last item
	 * behaves like in java
	 */
	function hasNext() {
		if ($this->size() > $this->cur_item)
			return true;
		return false;
	}

	/**
	 * returns the current item
	 * behaves like in java
	 */
	function getNext() {
		if ($this->hasNext()) {
			$tmp = $this->files[$$this->cur_item];
			$this->cur_item++;
			return $tmp;
		}
		return null;
	}

	/**
	 * adds list of links extracted from this file $url
	 */
	function _getFilesForURL($url) {
		$this->visitedUrls[] = $url;

//		debug($url, '<b>Scanning url</b>');
		// if allready in list of files, return
		if (in_array($url, $this->files)) {
//			debug($url, "File already in list of files");
			return;
		}

		// check for non local file links that refers to another host
		if (!($this->_isLocal($url))) {
//			debug($url, 'The url does not match the current host '.$this->host.', only relative links are allowed at the moment!');
			return;
		}

		// fetch content for given url
		$res = $this->_getURL($url);

		// extract headers
		$info = $this->_handleHeaders($res['header']);		
		$res = $res['content'];

		// check if file really exists
		if ($info['http_status'] == '404') {
//			debug($url, "Url does not exist (http 404)");
			$this->storage->fire(array(
					PSNG_URLINFO_URL => $url, 
					PSNG_URLINFO_LASTMOD => $info['lastmod'], 
					PSNG_URLINFO_CHANGEFREQ => $info['changefreq'], 
					PSNG_URLINFO_PRIORITY => $info['priority'],
					PSNG_URLINFO_ENABLED => 0));

			return;
		}
				
		// if not allready in list of files and this is not a redirect (location would be set), add it 
		if (!in_array($url, $this->files) && ($info['location'] == '')) {
			if (isset($this->storage)) {
				// !!! add to storage object !!!
				$this->storage->fire(array(
						PSNG_URLINFO_URL => $url, 
						PSNG_URLINFO_LASTMOD => $info['lastmod'], 
						PSNG_URLINFO_CHANGEFREQ => $info['changefreq'], 
						PSNG_URLINFO_PRIORITY => $info['priority'],
						PSNG_URLINFO_ENABLED => 1
				));
			}
			$this->files[] = $url;
			$this->fileCounter++;
//			debug($url, 'Successful added url');
		} elseif ($info['location'] == '') {
//			debug($url, "File already in list of files");
			return;
		} else {
//			debug($url, "Url is only a redirect (http 302)");
				$this->storage->fire(array(
						PSNG_URLINFO_URL => $url, 
						PSNG_URLINFO_LASTMOD => $info['lastmod'], 
						PSNG_URLINFO_CHANGEFREQ => $info['changefreq'], 
						PSNG_URLINFO_PRIORITY => $info['priority'],
						PSNG_URLINFO_ENABLED => 0));
		}


		// check location tag (when got a 302 response from webserver)
		$result = array ();
		if ($info['location'] != '') {
			$res = '<a href="'.$info['location'].'"> </a>';
		} else {
			echo('Computing '.$url."<br>\n");
		}

		// remove html comments
		$a_begin = 0;
		while (true) {
			$a_begin = strpos($res, '<!--', $a_begin);
			if ($a_begin === FALSE) break; // no comment tag found, break

			$a_end = strpos($res, '-->', $a_begin +3);
			if ($a_end === FALSE) break; // no comment end tag found, break

			$a_end += 3;
			$res = substr_replace($res, '', $a_begin, ($a_end - $a_begin));
		}

		// contribution by vvkov
//		preg_match_all("/<[Aa][ \r\n\t]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\n\r\t]*([^ \"'>]+)[^>]*>/",$res ,$urls);
		$urls = array();
		preg_match_all("/<[Aa][^>]*[Hh][Rr][Ee][Ff]=['\"]([^\"'>]+)[^>]*>/",$res ,$urls); // update by TK, 2005-07-27
    	$urls_count = count( $urls[1] );
    	
    	$ts_begin = $this->microtime_float();    	
    	while ((($ts_middle = ($this->microtime_float()-$ts_begin)) < $this->maxGetFileTime) && $urls_count > 0 ) {      
        	$thisurl =  trim(str_replace('&amp;', '&', $urls[1][--$urls_count]));
			if ($thisurl == '' || (strcasecmp(substr($thisurl, 0, strlen('javascript:')), 'javascript:') == 0))	continue;
			// debug('_'.$thisurl.'_','Extracted url');

			$absUrl1 = $this->_absolute($thisurl, $url);
			//debug('_'.$absUrl1.'_', 'After _absolute');
			$absUrl2 = $this->_removeForbiddenKeys($absUrl1);

			// remove "//"
			$start = (strpos($absUrl2, '//') + 3);
			$end = strpos($absUrl2, '?', $start);
			if ($end === FALSE)	$end = strlen($absUrl2);
			$absUrl = substr($absUrl2, 0, $start).str_replace('//', '/', substr($absUrl2, $start, ($end - $start))).substr($absUrl2, $end);
			//debug($absUrl, "Computed absUrl");

			if ($this->_isLocal($absUrl)) {
				$result[] = $absUrl;
			}

			// just break this loop when a timeout occurs
			if (($this->deadline != 0) && (($this->deadline - $this->microtime_float()) < 0)) {
				debug('', "global timeout");
				break;
			}
		}

		$result = array_unique($result);
		foreach ($result as $id => $file) {
			if (!in_array($file, $this->visitedUrls) && !in_array($file, $this->files)) {
				// check forbidden files
				if ($this->checkFileName($file)) continue;
				// check forbidden directories
				if ($this->checkDirectoryName($file)) continue;
				//debug($file, 'Adding URL to todo list');

				// add file to todo list
				array_push($this->todo, $file);
			} // else: file already in list
		}

		return true;
	}

	function _isLocal($givenURL) {
		if (preg_match(',^(ftp://|mailto:|news:|javascript:|telnet:|callto:),i', $givenURL)) return false;

		$url = parse_url($givenURL);
		$ret = ($url[host] == $this->host);
		return $ret;
	}
	
	/**
	 * WAS: only allowed masking char: * (before and/or after search string)
	 * 
	 * TODO check this with more data
	 */
	function checkFileName($filename) {
		$filename = substr($filename, strrpos($filename, '/') + 1);
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
				*/
				if ($pos === FALSE)	continue;
				return true;
			}
		}
		return false;
	}

	function checkDirectoryName($directory) {
		$directory = substr($directory, 0, strrpos($directory, '/') + 1); // with last "/"
		if (is_array($this->forbidden_dir) && count($this->forbidden_dir) > 0) {
			foreach ($this->forbidden_dir as $id => $dir) {
				if ($dir == '')	continue;
				$pos = strpos($directory, $dir);
				/*	    		$dir_search = '';
						  		if (!(($as = strpos($dir, '*')) === FALSE)) {
						  			$dir_search = str_replace('*', '', $dir);
					  				if ($as == 0) $pos = @strpos($directory, $dir_search, (strlen($directory)-strlen($dir_search)));
					  				if ($as == strlen($dir_search)) $pos = (@strpos($directory, $dir_search) != 0);
						  		} else {
									$pos = ($directory === $dir);
						  		}
				*/ // echo "directory: $directory, dir: $dir, dir_search: $dir_search, pos: $pos<br>\n";
				if ($pos === FALSE)	continue;
				return true;
			}
		}
		return false;
	}

	function _handleHeaders($header) {
		$res = array();
		// TODO what about http result? after 'HTTP/' => split(" " ...) => [1]
		if (count($header) == 0) return $res; // empty header
		foreach ($header as $key => $value) {
			if ($key == '' && substr($value, 0, strlen('HTTP/'))) {
				$s = split(" ", $value);
				$res['http_status'] = $s[1];
			} elseif ($key == "Last-Modified") {
				$res['lastmod'] = strtotime(trim($value)); // no dynamic (php/other script) generated page
			} elseif ($key == "Date") {
				$res['date'] = strtotime(trim($value));
			} elseif ($key == "Content-Length") {
				$res['size'] = trim($value);
			} elseif ($key == "Location") {
				$res['location'] = trim($value);
			} elseif ($key == "Pragma") {
				$pragma = trim($value);
				if ($pragma == "no-cache") { // handle non-cached files -> normaly dynamic created pages
					if (!isset ($res['lastmod'])) $res['lastmod'] = $res['date'];
					$res['changefreq'] = 'always';
				}
			}
		}
		if ($res['date'] != '' && $res['lastmod'] == '') $res['lastmod'] = $res['date']; 
//		debug($header, 'Header');
//		debug($res, 'Extracted information from headers');
		return $res;
	}

	function _removeForbiddenKeys($url) {
		$paramsStart = strpos($url, '?');
		if ($paramsStart !== FALSE)  { // url has no parameters, don't search for keys
			foreach ($this->forbiddenKeys as $id => $key) {
				if ($key == '') continue; // empty key => ignore it
				$start = strpos($url, $key, $paramsStart);
				while ($start != FALSE) {
					$end = strpos($url, '&', $start);
					if ($end !== FALSE) {
						$url = substr($url, 0, $start).substr($url, $end);
					} else {
						$url = substr($url, 0, $start);
					}
					$start = strpos($url, $key, $paramsStart);
				} // else: does not contain key
			}
		}
		// remove anchor links : beginning with # to the end of the url
		// echo "$url<br>\n";
		if (strpos($url, '#') !== FALSE) {
			$url = substr($url, 0, strpos($url, '#'));
		}
		// remove empty & and ?
		while (substr($url, strlen($url) - 1) == "&") {
			$url = substr($url, 0, strlen($url) - 1);
		}
		while (substr($url, strlen($url) - 1) == "?") {
			$url = substr($url, 0, strlen($url) - 1);
		}
		return $url;
	}

	function _getURL($urlString) {
		$url = parse_url($urlString);
		$host = $url["host"];
		$port = $url["port"];
		if ($port == '') {
			if ($url[scheme] == "https") {
				$port = "443";
			} else {
				$port = "80";
			}
		}
		//		debug($url, 'Parsed URL');
		$errno = '';
		$errstr = '';
		$fp = fsockopen($host, $port, $errno, $errstr, $this->socketTimeout);
		if ($fp === FALSE) {
			debug($errstr, 'Could not open connection for '.$urlString.' (host: '.$host.', port:'.$port.'), Errornumber: '.$errno);
			return array('header' => array(), 'content' => '');
		}
		$query_encoded = '';
		if ($url[query] != '') {
			$query_encoded = '?';
			foreach (split('&', $url['query']) as $id => $quer) {
				$v = split('=', $quer);
				if ($v[1] != '') {
					$query_encoded .= $v[0].'='.rawurlencode($v[1]).'&';
				} else {
					$query_encoded .= $v[0].'&';
				}
			}
			$query_encoded = substr($query_encoded, 0, strlen($query_encoded) - 1);
			$query_encoded = str_replace('%2B','+', $query_encoded);
		}

		$get = "GET ".$url[path].$query_encoded." HTTP/1.1\r\n";
		$get .= "Host: ".$host."\r\n";
		$get .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; phpSitemapNG ".PSNG_VERSION.")\r\n";
		$get .= "Referer: ".$url[scheme].'://'.$host.$url[path]."\r\n";
		$get .= "Connection: close\r\n\r\n";
		// debug(str_replace("\n", "<br>\n", $get), 'GET-Query');
		socket_set_blocking($fp, true);
		fwrite($fp, $get);

		$res = '';
		$head_done = false;
		$ts_begin = $this->microtime_float();
		// source for chunk-decoding: http://www.phpforum.de/archiv_13065_fsockopen@end@chunked@geht@nicht_anzeigen.html
		
		// get headers
		$currentHeader = '';		
		while ( '' != ($line=trim(fgets($fp, 1024))) ) {
			if ( false !== ($pos=strpos($line, ':')) ) {
				$currentHeader = substr($line, 0, $pos);
				$header[$currentHeader] = trim(substr($line, $pos+1));
			} else {
				@$header[$currentHeader] .= $line;
			}
		}

		// check for chunk encoding
		if (isset($header['Transfer-Encoding']) && $header['Transfer-Encoding'] == 'chunked') {
			$chunk = hexdec(fgets($fp, 1024));
		} else {
			$chunk = -1;
		}
		
		// check file size
		if (isset($header['Content-Length']) && $header['Content-Length'] > $this->maxFilesize) {
//			info($size, "File size ". $header['Content-Length'] . " of ".$urlString." exceeds file size limit of ".PSNG_CRAWLER_MAX_FILESIZE." byte!");
			fclose($fp);
			return array('header' => $header, 'content' => '');
		}		
				
		// get content		
		$res = '';
		$size = 0;
		while ($chunk != 0 && !feof($fp)) {
		    if ($chunk > 0){
		         $part = fread($fp, $chunk);
		         $chunk -= strlen($part);
		         $size += strlen($part);
		         $res .= $part;
			//echo "part: $part<br>\n";
		         if ($chunk == 0){
		             if (fgets($fp, 1024) != "\r\n") ; //echo "Error: chunk-encoding error<br>\n"; // debug('Error in chunk-decoding');		
		             $chunk = hexdec(fgets($fp, 1024));
		         }
		    } else {
		         $res .= fread($fp, 1024);
		         $size += 1024;
		    }
		    // check if current filesize exceeds max file size
		    if ($size > $this->maxFilesize) break;
			// handle local timeout for fetching file
			if (($ts_middle = $this->microtime_float() - $ts_begin) > $this->maxGetFileTime) break;
			// handle global timeout: 
			if (($this->deadline != 0) && (($this->deadline - $this->microtime_float()) < 0)) break;
		}
		fclose($fp);
		// store current/computed filesize
		$header['Content-Length'] = $size;
		
		return array('header' => $header, 'content' => $res);
	}

	// taken from: http://www.php-faq.de/q/q-regexp-links-absolut.html
	function _absolute($relative, $absolute) {
		// Link ist schon absolut
		if (preg_match(',^(https?://|ftp://|mailto:|news:|javascript:|telnet:|callto:),i', $relative)) {
			// hostname is not the same (with/without www) than the one used in the link
			if (substr($relative, 0, 4) == 'http') {
				$url = parse_url($relative);
				if ($url[host] != $this->host && ((("www.".$url[host]) == $this->host) && $this->withWWW == true || ($url[host] == ("www.".$this->host)) && $this->withWWW == false)) {
					$r = $relative;
					$relative = str_replace($url[host], $this->host, $relative); // replace hostname that differes from local
				}
				// is pure hostname without path - so add a /
				if ($url[path] == '' && substr($relative, -1) != '/')
					$relative .= '/';
			}
			return $relative;
		}

		// parse_url() nimmt die URL auseinander
		$url = parse_url($absolute);

		// dirname() erkennt auf / endende URLs nicht
		if ($url['path'] { strlen($url['path']) - 1 } == '/')
			$dir = substr($url['path'], 0, strlen($url['path']) - 1);
		else
			$dir = dirname($url['path']);

		// absoluter Link auf dem gleichen Server
		if ($relative{0} == '/') {
			$relative = substr($relative, 1);
			$dir = '';
		}

		// set it to default host // TK
		if ($url['host'] != $this->host && (strpos($url['host'], $this->host) != FALSE || strpos($this->host, $url['host']) != FALSE)) {
			$url['host'] = $this->host;
		}

		// Link fängt mit ./ an
		if (substr($relative, 0, 2) == './')
			$relative = substr($relative, 2);

		// Referenzen auf höher liegende Verzeichnisse auflösen
		else
			while (substr($relative, 0, 3) == '../') {
				$relative = substr($relative, 3);
				$dir = substr($dir, 0, strrpos($dir, '/'));
			}

		// volle URL zurückgeben
		return sprintf('%s://%s%s/%s', $url['scheme'], $url['host'], $dir, $relative);
	}

	/* better compare function: contains */
	function _fl_contains($key, $array) {
		if (is_array($array) && count($array) > 0) {
			foreach ($array as $id => $val) {
				$pos = @ strpos($key, $val);
				if ($pos === FALSE)	continue;
				return true;
			}
		}

		return false;
	}

	function setStorage(& $storage) {
		$this->storage = & $storage;
	}

	/**
	 * set list of forbidden directories
	 */
	function setForbiddenDirectories($directories = array ()) {
		$this->forbidden_dir = $directories;
	}

	/**
	 * set list of forbidden files
	 */
	function setForbiddenFiles($files = array ()) {
		$this->forbidden_files = $files;
	}

	function setForbiddenKeys($keys) {
		$this->forbiddenKeys = $keys;
		//    	if(!in_array($key, $this->forbiddenKeys)) $this->forbiddenKeys[] = $key;
	}
}
?>