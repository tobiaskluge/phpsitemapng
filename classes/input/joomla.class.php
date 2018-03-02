<?php
/**
 * This is the phpSitemapNG input plugin specialized for Pascal Erdmann, www.nznp.com
 * 
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-12-17
 */
 
/* Changelog

xx.xx.xxxx - Inital Release 0.1

28.02.2006 - Version 0.15
- add option for mod_rewrite / no mod_rewrite
- Priority now can have up to 3 digits behind the seperator (before only 1)
- Get creation date instead of modified date if content never modified (30.11.1999 issue)
- add output of version number in setup page

*/
class joomla extends Input {
	var $settings; // this is an array that contains the internal plugin settings
	var $storage;  // this contains the reference to the storage object
	/**
	 * create an instance - NO parameters are allowed! 
	 * (this is due to java-bean-like on-the-fly instanciation of classes)
	 * 
	 * @return plugin new object of this class 
	 */
    function joomla() {
        // Allowed values for ChangeFrequency
        $this->cf_values = array('always','hourly','daily','weekly','monthly','yearly','never');


    }
    
    /**
     * returns a list of description about this plugin
     * in detail:
     * <ul>
     * 	<li><b>name</b> - name of the plugin</li>
     *  <li><b>short_description</b> - a short description (max. 100 chars) that 
     * 		will be displayed at the overview page</li>
     *  <li><b>long_description</b> - a longer description</li>
     *  <li><b>url</b> - the url where the plugin can be downloaded and more 
     * 		information are available</li>
     *  <li><b>version</b> - version information</li>
     *  <li><b>author</b> - name or organisation who created this plugin</li>
     * </ul>
     * 	
     * this information is currently used only to give the user more information about
     * the plugin
     * 
     * @return array description of this plugin
     */
    function getInfo() {
    	return array(
    		'name' => 'Joomla! V1.0.X Plugin (BETA)',
    		'short_description' => 'This plugin is special for Joomla!-Powered Sites.',
    		'long_description' => 'This plugin should work with Joomla! Version 1.0.X . '.
                'The next generation of Joomla! (V1.1) will have a lot of new stuff, so I think, this Plugin will not work with that version.',
    		'url' => 'http://enarion.net/google/phpsitemapng/',
    		'version' => '0.15',
    		'author' => 'Tobias Kluge, enarion.net, Ulrich Bruns, sz-haldern.de'
		);
    }
		
    /**
     * this function returns the html code needed to setup this plugin
     * 
     * to do so you have to use input/textarea fields that will be displayed
     * to the user in an embedded form handled by phpSitemapNG.
     * 
     * You have to set the name to PSNG_PLUGIN_INIT_SETTINGS[name_of_setting]
     * The value of PSNG_PLUGIN_INIT_SETTINGS is set by phpSitemapNG and only
     * information stored in this array can and will be sent back to the plugin!
     * This is necessary because the setup functions and run functions use the
     * same settings! 
     * 
     * You are not allowed to use other tags than 
     * 	&lt;table&gt;, &lt;tr&gt;, &lt;td&gt;, &lt;input&gt;, 
     * 	&lt;textarea&gt;, &lt;p&gt;, &lt;br&gt;
     * - all other tags will be removed for security reason!
     * 
     * If you need a special tag - please get in contact with the author!
     * 
     *
     * @return string html code of the setup page for this plugin 
     */
    function getSetupHtml() {
        //  In the configuration.php of joomla are the most recent settings we need like:
        //  db_user, db_password, db_host, db_db, sitename, sef
        //
        //  sef=search engine friendly
        //      URLS are convertet to another format that SE's could read better
        $Info = $this->getInfo();
    	$res = '';
    	$res .= '<h2 align="center">Joomla!-Plugin Setup<br />'."\n";
    	$res .= '<font size="-2">For Joomla! V1.0.X</font><br />'."\n";
    	$res .= '<font size="-2">Plugin Version ' . $Info['version'] . '</font></h2>'."\n";

       // Setting default values if no values are saved    
        if (is_null($this->settings) || ($this->settings['joomla_config'] == '')) {
			// set "normal" path to the joomla!-configuration file
			$this->settings['joomla_config'] = $_SERVER['DOCUMENT_ROOT'] . '/configuration.php';
			$this->settings['jomdeb'] = '0';                     //disable debug mode
			$this->settings['hits'] = '1';                       //enable calculate priority
			$this->settings['changef'] = 'weekly';               //set changef to weekly
			$this->settings['mod_rewrite'] = '1';                //Enable option for Mod_Rewrite
			$res .= '<p style="text-align:center;color:green;"><b>Some default values have been set for you !</b></p>'."\n";
        }  

    	$res .= '<p style="font-size:small;">Some values that may help you: <br />'."\n";
    	$res .= '<b>&bull; Document Root of '. $_SERVER[HTTP_HOST] .' :</b>'."\n";
        $res .= $_SERVER['DOCUMENT_ROOT'] . '<br />' . "\n";
        $res .= '<p><b><font size="-1">Local-Path to configuration.php: </font></b><br/>'."\n";
        $res .= '<input style="margin-left:20px;" type="text" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[joomla_config]" value="' . $this->settings['joomla_config'] . '" size="60" /></p>' . "\n";

        // Code for "Debug enable/disable"
        $res .= '<p><font size="-1"><b>Debug-mode: </b></font></b><br/>'."\n";        
        $jomenable='';
        $jomdisable='checked="checked"';    
        if ($this->settings['jomdeb']==1) {
                $jomenable='checked="checked"';
                $jomdisable='';
        }
        $res .= '<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[jomdeb]" value="1" ' .$jomenable. '> enabled <br />'."\n";
        $res .= '<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[jomdeb]" value="0" ' .$jomdisable. '> disabled<br />'."\n";

        // Code for "Use Hits for priority"
        $res .= '<p><font size="-1"><b>Use Hits for calculating priority: </b></font></b><br/>'."\n";        
        $res .= '<font size="-2">If this is disabled, the priority is set to 0.5 for every entry</font><br />'."\n";
        $hits_on='';
        $hits_off='checked="checked"';    
        if ($this->settings['hits']==1) {
                $hits_on='checked="checked"';
                $hits_off='';
        }
        $res .= '<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[hits]" value="1" ' .$hits_on. '> enabled <br />'."\n";
        $res .= '<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[hits]" value="0" ' .$hits_off. '> disabled<br />'."\n";
        
        
        // Code for "mod_rewrite option"
        $res .= '<p><font size="-1"><b>mod_rewrite: </b></font></b><br/>'."\n";        
        $res .= '<font size="-2">If you use the build-in SEF from Joomla!, <i>normally</i> you have the MOD_Rewrite enabled on your Webserver.</font><br />'."\n";
 
        if ($this->settings['mod_rewrite'] == 1) {
            $chk=' checked="checked" ';
        } else {
            $chk = ' ';
        }
        
        $res .= '<input type="checkbox" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[mod_rewrite]" value="1" '.$chk.'/> mod_rewrite is enabled on my server'."\n";
        
        // Code for "Changefrequency"
        $res .= '<p><font size="-1"><b>How often do your content change/update: </b></font></b><br/>'."\n";        
        $res .= '<font size="-2">This value is used for every link</font><br />'."\n";
        foreach ($this->cf_values as $cf) {
            $markchecked = '';
            if ($cf==$this->settings['changef']) {
                $markchecked = ' checked="checked"';
            }
            $res .= '<input type="radio" name="'.PSNG_PLUGIN_INIT_SETTINGS.'[changef]" value="'. $cf .'" ' .$markchecked. '> '. $cf .'<br />'."\n";          
        }

     	return $res;    		
    }

    /**
     * see Plugin.class.php::init() for details
     */
    function init($setupInformations) {   
    	$setupInformation = $setupInformations[PSNG_PLUGIN_INIT_SETTINGS];
    	if (!is_null($setupInformation) && count($setupInformation)>0) {
            $this->settings['joomla_config'] = $setupInformation['joomla_config'];
            $this->settings['jomdeb'] = $setupInformation['jomdeb'];
            $this->settings['hits'] = $setupInformation['hits'];
            $this->settings['changef'] = $setupInformation['changef'];
            $this->settings['mod_rewrite'] = $setupInformation['mod_rewrite'];
        }
        
    	// precompute given parameters - you have to copy them at least to the $this->settings array
    	$this->storage = & $setupInformations[PSNG_PLUGIN_INIT_STORAGE];
    	return true;
    } 
	

    
    /**
     * execute this plugin
     * this is invoked after an creation of the plugin and initialisation
     *
     * @return boolean true if finished successful, false otherwise
     */
    function run() {
        // Get/Set debugging mode
        $jomdeb = False;
        if ($this->settings['jomdeb']=='1') {
            $jomdeb=True;
            echo '<font size="-1" color="green"><b>Debug Output enabled.</b></font><br />'."\n";
        }
        
    	// Let us read the configuration
        if ($jomdeb) {
            echo '<font size="-1" color="green">Joomla! configuration file: <b>' . $this->settings['joomla_config'] . '</b></font><br />'."\n";
        }
        require_once($_SERVER['DOCUMENT_ROOT'].'/configuration.php');
        if (!isset($mosConfig_live_site)) {
          echo "Error! No config values found!";
          return false;
        }
        
        // Ok... now lets connect to the Database
        // At this stage of development (V1.0.7 of Joomla) only mysql databases are supported
        if (!function_exists('mysql_connect')) {
          echo "<font color=\"red\">ERROR - MySql Function not loaded !!!</font><br />\n";
          return false;
        }
        
        $dbcon=mysql_connect ( $mosConfig_host , $mosConfig_user , $mosConfig_password);
        if ($dbconn===False) {
          echo "<font color=\"red\">ERROR - Could not connect to Database !!!</font><br />\n";
          return false;
        }
        
        if (!mysql_select_db($mosConfig_db, $dbcon)) {
          echo "<font color=\"red\">ERROR - Could not select Database</font><br />\n";
          return false;
        }
        
        if ($jomdeb) {
          echo '<p><b><font size="-1" color="green">Database connection established.</b><br />'."\n";
          echo "I used:<br />\n";
          echo "- User: $mosConfig_user<br />\n";
          echo "- Password: Length=".strlen($mosConfig_password)."<br />\n";
          echo "- Host: $mosConfig_host <br />\n";
          echo "- DB: $mosConfig_db <br /></font>\n";
          echo "</p>\n";
        }
   
   
        // This query gets the Content of Joomla as sorted list by hits
        // I want to use the hits for selecting the priority     
        $query="SELECT ID, MODIFIED, CREATED, HITS ".
            "FROM ".$mosConfig_dbprefix."content ".
            "WHERE STATE=1 and ACCESS<1 ".
            "ORDER BY HITS DESC;";
     
        // Execute the query
        $result=mysql_query($query,$dbcon);
        if ($result === False) {
            if ($jomdeb) {
              echo "<font color=\"red\">ERROR - Query not ok !!!<br />\n";
              echo "Querystring: $query <br />";
              echo "Errormsg MySQL: ".mysql_error($dbcon)." <br />";
            }
            return false;
        }
        $rowcount=mysql_num_rows($result);
        if ($jomdeb) {
            echo '<font size="-1" color="green"><b>I found '. $rowcount .' content items.</b></font><br />'."\n";
        }


        $i=0;       //Counter needed for calculating priority
        while ($row = mysql_fetch_assoc($result)) {
            if ($this->settings['hits']==1) {
              $priority = round(1-($i/$rowcount),3);
            } else {
              $priority = "0.5";       //default priority
            }
            $i++;

            $link = 'index.php?option=com_content&task=view&id='.$row['ID'];
            if ($mosConfig_sef) {
              if ($this->settings['mod_rewrite'] == 1) {
                $link = $this->sefReltoAbs($link, $mosConfig_live_site, True);
              } else {
                $link = $this->sefReltoAbs($link, $mosConfig_live_site, False);  
              }
            }
            
            //Get the "Changefrequency" value
            if (!is_null($this->settings['changef'])) {
                $changefreq = $this->settings['changef'];
            } else {
                $changefreq = 'weekly';
            }
            
            // Watch for modified/creation date
            if ($row['MODIFIED'] == "0000-00-00 00:00:00") {  //Content created but never modified
                $lastmod = strtotime($row['CREATED']);
                if ($jomdeb) {
                    echo '<br /><div style="margin-left:10px;color:green;">'."\n";
                    echo 'Taken creation date instead of modified date at entry # ' . $i . '<br />'."\n";
                    echo "</div>\n";
                }
            } else {
              $lastmod = strtotime($row['MODIFIED']);
            }
            
            if ($jomdeb) {
                echo '<br /><div style="margin-left:10px;color:green;">'."\n";
                echo "<b># $i</b><br />\n";
                echo '   Link:     '. $link .'<br />'."\n";
                echo "   Lastmod:  ".date("d.m.Y H:i:s",$lastmod)." (Timestamp: $lastmod)<br />\n";
                echo "   Priority: $priority <br />\n";
                echo "   Changef:  $changefreq <br />\n";
                echo "</div>\n";
            }
            
            // Fire the Data to SitemapNG
            
            $this->storage->fire(array(
                PSNG_URLINFO_URL => $link,
                PSNG_URLINFO_LASTMOD => $lastmod,
      			PSNG_URLINFO_CHANGEFREQ => $changefreq,  	// change frequency - optional, uncomment this line to set it
      			PSNG_URLINFO_PRIORITY => $priority,		// priority - optional, uncomment this line to set it
    			PSNG_URLINFO_ENABLED => 1));         
        }
              
      
    	$numbUrls = $i;

        //Freedom for my Memory !!! ;-)
    	mysql_free_result($result);
    	mysql_close($dbcon);

        return $numbUrls;
    }

    /**
     * see Plugin.class.php::tearDown() for details
     */
	function tearDown() {
		unset($this->settings); // php4
		$this->settings = null; // php5
	}
		
/**********************************************/
/* Function taken from sef.php / Joomla 1.0.5 */
/**********************************************/
	
/**
* @version $Id: sef.php 1822 2006-01-14 21:22:37Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
	
/**
 * Converts an absolute URL to SEF format
 * @param string The URL
 * @return string
 */
 
/**
    * Added a parameter for Live-Site
    * Added a parameter for Mod_Rewrite Option
**/

function sefRelToAbs( $string,$mosConfig_live_site,$modrewrite=True ) {
	$mosConfig_live_site;
    $mosConfig_sef = 1;

	if ($mosConfig_sef && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),'index.php')) {
		// Replace all &amp; with &
		$string = str_replace( '&amp;', '&', $string );
		/*
		Home
		index.php
		*/
		if ($string=='index.php') {
			$string='';
		}

		$sefstring = '';
		if ( (eregi('option=com_content',$string) || eregi('option=content',$string) ) && !eregi('task=new',$string) && !eregi('task=edit',$string) ) {
			/*
			Content
			index.php?option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart&year=$year&month=$month&module=$module
			*/
			$sefstring .= 'content/';
			if (eregi('&task=',$string)) {
				$temp = split('&task=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&sectionid=',$string)) {
				$temp = split('&sectionid=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&id=',$string)) {
				$temp = split('&id=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&Itemid=',$string)) {
				$temp = split('&Itemid=', $string);
				$temp = split('&', $temp[1]);

				if ( $temp[0] !=  99999999 ) {
					$sefstring .= $temp[0].'/';
				}
			}
			if (eregi('&limit=',$string)) {
				$temp = split('&limit=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&limitstart=',$string)) {
				$temp = split('&limitstart=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&lang=',$string)) {
				$temp = split('&lang=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= 'lang,'.$temp[0].'/';
			}
			if (eregi('&year=',$string)) {
				$temp = split('&year=', $string);
				$temp = split('&', $temp[1]);
				$sefstring .= $temp[0].'/';
			}
			if (eregi('&month=',$string)) {
				$temp = split('&month=', $string);
				$temp = split('&', $temp[1]);
				
				$sefstring .= $temp[0].'/';
			}
			if (eregi('&module=',$string)) {
				$temp = split('&module=', $string);
				$temp = split('&', $temp[1]);
				
				$sefstring .= $temp[0].'/';
			}
			// Handle fragment identifiers (ex. #foo)
			if (eregi('#', $string)) {
				$temp = split('#', $string, 2);
				$string = $temp[0];
				// ensure fragment identifiers are compatible with HTML4
				if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $temp[1])) {
					$fragment = '#'. $temp[1];
					$sefstring .= $fragment .'/';
				}
			}

			$string = $sefstring;
		} else if (eregi('option=com_',$string) && !eregi('task=new',$string) && !eregi('task=edit',$string)) {
			/*
			Components
			index.php?option=com_xxxx&...
			*/
			$sefstring 	= 'component/';
			$temp 		= split("\?", $string);
			$temp 		= split('&', $temp[1]);

			foreach($temp as $key => $value) {
				$sefstring .= $value.'/';
			}
			$string = str_replace( '=', ',', $sefstring );
		}

		// comment line below if you dont have mod_rewrite
		// return $mosConfig_live_site.'/'.$string;

		// allows SEF without mod_rewrite
		// uncomment Line 348 and comment out Line 354	
	
		// comment out line below if you dont have mod_rewrite
		
		if ($modrewrite) {
    		return $mosConfig_live_site.'/'.$string;
    	} else {
          	return $mosConfig_live_site.'/index.php/'.$string;
        }
	} else {
		return $string;
	}
}
}
?>