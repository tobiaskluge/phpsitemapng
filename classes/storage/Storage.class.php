<?php
/**
 * This is the interface of the common storage class used by phpSitemapNG
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
class Storage {

	/**
	 * create an instance - NO parameters are allowed! 
	 * (this is due to java-bean-like on-the-fly instanciation of classes)
	 * 
	 * @return object new object of Storage class 
	 */
    function Storage() {
    }
	
	/**
	 * init this instance
	 * 
	 * @param array connectionSettings necessary to connect this storage
	 */
	function init($connectionSettings)  {		
	}
	
	
	/**
	 * this function returns a list of urls for the given baseUrl
	 * 	if baseUrl is empty/unset all urls from the database will be returnd
	 *
	 * @param string baseUrl url from where the urls should be returned 
	 * @return array list of urlentries
	 */
	function getUrls($baseUrl = '') {
	}

	/**
	 * this function adds a urlinfos to the database
	 *    already existing urls will be updated
	 * 
	 * @param array urlInfo in format of urlInfo that will be added 
	 * @return 1 if succeed, 0 otherwise
	 */
	function addURL($urlInfo) {
		
	}
	
	/**
	 * this function adds a list of urlinfos to the database
	 *    already existing urls will be updated
	 * 
	 * @param array urlInfos list of urlInfos to be added 
	 * @return number of urls that have been added
	 */
	function addURLs($urlInfos) {
	}
	
	/**
	 * this function deletes the list of given urlInfos
	 *
	 * @param array urlInfos list of urlInfos to be deleted 
	 * @return number of urls that have been added
	 */
	function deleteURLs($urlInfos) {
	}

	/**
	 * shut down storage backend
	 * 
	 * @return void
	 */	
	function tearDown() {
	}
}
?>