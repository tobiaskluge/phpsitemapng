<?php
/**
 * this file defines the constants used by phpSitemapNG
 * 		This has to be used to have a common understanding when developing
 * 		plugins and modifications
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

define("PSNG_VERSION", '1.6.1');
define("PSNG_ACTION", 'psng_action');
define("PSNG_SESSION_NAME", 'psng_session');
define("PSNG_SCRIPT", 'psng_script');

define("PSNG_SETTINGS_FILE", realpath(dirname(__FILE__).'/../tmp/').'/settings.inc.php');

define("PSNG_TRUE", 'psng_true');
define("PSNG_FALSE", 'psng_false');

/* settings */
define("PSNG_SETTINGS", 'psng_settings');
define("PSNG_SETTINGS_PLUGIN", 'psng_settings_plugin');
define("PSNG_SETTINGS_SESSIONS", 'psng_settings_sessions');
define("PSNG_SETTINGS_USEFILES", 'psng_settings_usefiles');
define("PSNG_SETTINGS_FILENAME", 'psng_settings_filename');
define("PSNG_SETTINGS_TMPDIR",   'psng_settings_tmpdir');

/* actions */
define("PSNG_ACTION_PLUGIN_OVERVIEW", 'psng_action_plugin_overview');
define("PSNG_ACTION_PLUGIN_REMOVE", 'psng_action_plugin_remove');
define("PSNG_ACTION_RUN", 'psng_action_run');
define("PSNG_ACTION_EXPORT", 'psng_action_export');
define("PSNG_ACTION_SETTINGS", 'psng_action_settings');
define("PSNG_ACTION_SETTINGS_STORE", 'psng_action_settings_store');

/* plugin specific constants */
define("PSNG_PLUGIN", 'psng_plugin');
define("PSNG_PLUGIN_ID", 'psng_plugin_id');
define("PSNG_PLUGIN_CLASS", 'psng_plugin_class');
define("PSNG_PLUGIN_TITLE", 'psng_plugin_title');
define("PSNG_PLUGIN_INSTANCES", 'psng_plugin_instances'); // settings => array of plugin instances

define("PSNG_PLUGIN_TYPE", 'psng_plugin_type');				// type of a plugin
define("PSNG_PLUGIN_TYPE_INPUT", 'psng_plugin_type_input');	// type is input plugin
define("PSNG_PLUGIN_TYPE_OUTPUT", 'psng_plugin_type_output');	// type is output plugin
define("PSNG_PLUGIN_TYPE_COMPUTE", 'psng_plugin_type_compute');	// type is compute plugin

define("PSNG_PLUGIN_INIT_STORAGE", 'psng_plugin_init_storage');		// holds reference to storage object, given to plugin in init()
define("PSNG_PLUGIN_INIT_SETTINGS", 'psng_plugin_init_settings'); 	// request/form array => transfer information from plugin to PSNG_CORE, used in setupHTML and init

/* delegate specific constants */
define("PSNG_DELEGATE_DEFAULTPARAMS", 'psng_delegate_defaultparams'); // default parameters that will be used by the AddToStorage delegate
define("PSNG_DELEGATE_OUTPUTWEBSITE", 'psng_delegate_outputwebsite'); // key for website parameter of GetFromStorage delegate

/* urlinfo specific namespace */
define("PSNG_URLINFO_ENABLED", 		'psng_urlinfo_enabled'); 	// type: int, value: 0/1 - the url will be used by output plugins
define("PSNG_URLINFO_URL", 			'psng_urlinfo_url');		// type: string - the url of the website
define("PSNG_URLINFO_LASTMOD", 		'psng_urlinfo_lastmod');	// type: int, value: timestamp; last modification value
define("PSNG_URLINFO_CHANGEFREQ", 	'psng_urlinfo_changefreq');	// type: string - the changefreq string introduced by Google Sitemaps
define("PSNG_URLINFO_PRIORITY", 	'psng_urlinfo_priority');	// type: float, value: 0.0 to 1.0 - the priority value introduced by Google Sitemaps
define("PSNG_URLINFO_FILENAME",		'psng_urlinfo_filename');	// type: string - valid absolute path and filename to existing page
define("PSNG_URLINFO_TYPE", 		'psng_urlinfo_type');		// type: string - content type of url entry (eg application/pdf, application/html, ...)
define("PSNG_URLINFO_ADDED", 		'psng_urlinfo_added');		// type: int, value: timestamp; will be set when the url has been added for the first time
define("PSNG_URLINFO_LASTUPDATE",	'psng_urlinfo_lastupdate');	// type: int, value: timestamp; will be set when the url has been updated (new/updated content)
define("PSNG_URLINFO_TITLE", 		'psng_urlinfo_title');		// type: string - the title of the website extraced from html content
define("PSNG_URLINFO_GROUP", 		'psng_urlinfo_group');		// type: string - represents a group that can be used by plugins to select groups of urls  
define("PSNG_URLINFO_META_ROBOTS", 	'psng_urlinfo_meta_robots');// type: string - contains the robots tag from html website
define("PSNG_URLINFO_META_LANGUAGE",'psng_urlinfo_meta_language');// type: string - contains the language tag from html website
define("PSNG_URLINFO_META_KEYWORDS",'psng_urlinfo_meta_keywords');// type: string - contains the keywords tag from html website
define("PSNG_URLINFO_PAGESIZE", 	'psng_urlinfo_pagesize');	// type: int - contains pagesize in byte
define("PSNG_URLINFO_PAGEHASH", 	'psng_urlinfo_pagehash');	// type: string - contains hash value of page content
define("PSNG_URLINFO_PLUGIN_ADDED", 'psng_urlinfo_plugin_ADDED');// type: string - contains the class of the plugin that has added this page

/* define directory pathes */
define("PSNG_DIRECTORY_BASE", 'psng_directory_base');
define("PSNG_DIRECTORY_CLASSES", 'psng_directory_classes');
// define("PSNG_DIRECTORY", ); // initialized in HelperClass::init

/* class pathes */
define("PSNG_CLASS_SETTINGSHANDLER", dirname(__FILE__).'/misc/SettingsHandler.class.php');
define("PSNG_CLASS_HELPERCLASS", dirname(__FILE__).'/misc/HelperClass.class.php');
define("PSNG_CLASS_DATETIMEHELPER", dirname(__FILE__).'/misc/DateTimeHelper.class.php');
define("PSNG_CLASS_GUI", dirname(__FILE__).'/Gui.class.php');
define("PSNG_CLASS_LAYOUTENGINE", dirname(__FILE__)."/misc/LayoutEngine.class.php");
define("PSNG_CLASS_PLUGINHANDLER", dirname(__FILE__)."/misc/PluginHandler.class.php");
define("PSNG_CLASS_PLUGIN", dirname(__FILE__)."/misc/Plugin.class.php");
define("PSNG_CLASS_STORAGE", dirname(__FILE__)."/storage/Storage.class.php");
define("PSNG_CLASS_STORAGE_SESSION", dirname(__FILE__)."/storage/StorageSession.class.php");

define("PSNG_CLASS_PLUGIN_COMPUTE", dirname(__FILE__)."/compute/Compute.class.php");
define("PSNG_CLASS_PLUGIN_OUTPUT", dirname(__FILE__)."/output/Output.class.php");
define("PSNG_CLASS_PLUGIN_INPUT", dirname(__FILE__)."/input/Input.class.php");

define("PSNG_CLASS_DELEGATE", dirname(__FILE__)."/misc/delegates/Delegate.class.php");
define("PSNG_CLASS_DELEGATE_ADDTOSTORAGE", dirname(__FILE__)."/misc/delegates/AddToStorage.class.php");
define("PSNG_CLASS_DELEGATE_GETFROMSTORAGE", dirname(__FILE__)."/misc/delegates/GetFromStorage.class.php");

define("PSNG_PATH_PLUGIN_INPUT", dirname(__FILE__)."/input/");
define("PSNG_PATH_PLUGIN_OUTPUT", dirname(__FILE__)."/output/");
//define("PSNG_URL", ""); // defined in HelperClass::init()
?>