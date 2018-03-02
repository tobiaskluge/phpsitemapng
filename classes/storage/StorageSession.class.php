<?php
/**
 * this storage class puts all information into a session variable
 *   - offers some functions to add, read and remove urls
 *   - implements some common plugin functionality
 * 
 * TODO check against Storage class for not implemented functions
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
class StorageSession extends Storage {
	var $urls;

    function StorageSession() {
		session_start();
		if (isset($_SESSION['urls'])) {
			$this->urls = $_SESSION['urls'];
		} else {
			$this->urls = array();
		}	
    } 
	
	/**
	 * init this instance
	 * 
	 * @param connectionSettings array contains the settings necessary to let the backend connect to its software
	 */
	function init($connectionSettings)  {	
	}
	
	
	/**
	 * this function returns a list of urls for the given baseUrl
	 * 	if baseUrl is empty/unset all urls from the database will be returnd
	 * 
	 * @param string baseUrl domainname from where the files should be returned
	 * 
	 * @return array list of urlinfo
	 */
	function getUrls($baseUrl = '') {
		$res = array();
		
		if ($baseUrl != '') {
			$baseUrl = HelperClass::extractHost($baseUrl);
			
			foreach ($this->urls as $url => $urlinfo) {
				if (HelperClass::extractHost($urlinfo[PSNG_URLINFO_URL]) == $baseUrl) {
					$res[] = $urlinfo;
				}
			}
		} else {
			$res = $this->urls;
		}	
		return $res;
	}

	/**
	 * this function adds a urlinfos to the database
	 *    already existing urls will be updated
	 * 
	 * @return 1 if succeed, 0 otherwise
	 */
	function addURL($urlInfo) {
		if($urlInfo[PSNG_URLINFO_URL] != '') {
			$this->urls[$urlInfo[PSNG_URLINFO_URL]] = $urlInfo;
			return 1;
		}
		return 0;
	}
	
	/**
	 * this function adds a list of urlinfos to the database
	 *    already existing urls will be updated
	 * 
	 * 
	 * @return number of urls that have been added
	 */
	function addURLs($urlInfos) {
		$result = 0;
		foreach ($urlInfos as $key => $urlInfo) {
			$result += $this->addURL($urlInfo);
		}
		return $result;
	}
	
	/**
	 * this function deletes the list of given urlInfos
	 * 
	 * TODO implement me!
	 * 
	 * @return number of urls that have been added
	 */
	function deleteURLs($urlInfos) {
		// TODO implement me!
		return -1;
	}

	/**
	 * this function deletes the list of urlInfos
	 * 
	 * @return number of urls that have been added
	 */
	function resetURLs() {
		$i = count($this->urls);
		unset ($this->urls);
		return $i;
	}

	/**
	 * shut down storage backend
	 */	
	function tearDown() {
		$_SESSION['urls'] = $this->urls;
	}
}
?>