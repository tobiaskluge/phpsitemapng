<?php
/**
 * start file of phpSitemapNG
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

require_once('classes/PhpSitemapNG.class.php');

// create an instance of phpSitemapNG
$psng = new PhpSitemapNG();

$input = $_REQUEST;
// TODO precompute and secure input from user!!!
 
$psng->handleInput($input);
// that's it, invoke tearDown to kill all objects :)
$psng->tearDown();
?>