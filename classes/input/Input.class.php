<?php
/**
 * this is a common input plugin class
 * 
 * it derives from class Plugin and doesn't add 
 * special functions
 * 
 * this is necessary for the PluginHandler class to get and
 * setup the necessary input plugin classes
 * 
 * All input plugins have to extend this class!!!
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
class Input extends Plugin {
    function Input() {
    }

	/* 
	 * same functions like Plugin class, 
	 * see file Plugin.class.php for details 
	 */
}
?>