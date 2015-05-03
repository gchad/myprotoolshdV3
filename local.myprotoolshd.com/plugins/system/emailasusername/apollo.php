<?php
/**
* @version		$Id: LunarHotel base plugin class $
* @package		Joomla 1.6 Native version
* @copyright	Copyright (C) 2011 LunarHotel.co.uk. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

defined( '_JEXEC' ) or die( 'Restricted access' );



/* base class provides some basic functionality for plugins including:
	- simple property based access to get and post variables (merged together)
	- logging structure - openlog, closelog, log, deletelog - going to do away with html logging (file only)
	- function to determine if its running in admin
	- Seems plugin parameters can now be accessed via $this->params->toObject();
		may set this as a property anyway, for ease of access.
	- enqueue messages
	+ detect enable / disable of the plugin
	- Quick method of executing SQL
*/

class apolloPlugin extends JPlugin {
	
	// to access plugin parameters
	var $parameters;
	
	// get and post variables
	var $pageData;
	
	// for logging
	var $logFileHandle;
	
	// to see if we're in admin mode
	var $inAdmin;
	
	// some events
	var $eventDisable 	= false;
	
	function apolloPlugin(& $subject, $config) {
		
		parent::__construct($subject, $config);
		
		$JApplication=JFactory::getApplication();
		$userinfo=JFactory::getUser();
		
		
		// make the parameters easily accessible
		$this->parameters = $this->params->toObject();
		
		$this->openLog();
		$this->log("apollo constructor running");
		// get the post and get data
		$this->pageData = $this->getPageData();
		
		$this->log("setting in admin flag");
		// in admin is only true if the user has actually logged in
		$this->inadmin=$JApplication->isAdmin() && !$userinfo->guest;
		
		// we can call this without worrying about if we in admin or not, because it checks
		// before it runs anything.
		
		$this->log("Checking for disable event");
		// we now only need to check if its beeing unpublished because
		// before the plugin is uninstalled, its automatically unpublished. genius.
		$this->eventDisable = $this->isUnpublishing();
		
	}
	
	function __destruct() {
		$this->closelog();
	}
	
	
	// checks to see if the plugin is being disabled or uninstalled, and sets the eventDisable and
	// eventUninstall accordingly
	
	function isUnpublishing() {
		// first lets check to see whats going on.
		if($this->inadmin) {
			
			// we're in admin mode, so there's a chance the plugin is being disabled or uninstalled
			
			// there are two ways a plugin can be disabled, one thru the plugin list
			// and again in the plugin parameters page.
			
			
			// check to see fi its being unpublished via the plugin manager
			if(@$this->pageData->option == "com_plugins" && 
				(@$this->pageData->task == "plugins.unpublish" |
					@$this->pageData->task == "plugin.apply" |
						@$this->pageData->task == "plugin.save") ) {
						
				
				// now we need to see if its this plugin thats being disabled. Sadly, the plugin id isnt provided 
				// within the normal plugin data, so we'll have to look it up from the db
				
				if( $pluginid = $this->getThisPluginId() ) {
					// check to see if we're talking about the same plugin
					// first, grab the list of affected ids
					if($this->pageData->task=="plugins.unpublish") {
						// been disabled from the plugin list
						$ids = $this->pageData->cid;
						// when disabling via plugin param page, this value is set to 0
						// but when in the plugin list, its implied in plugins.unpublish
						$this->pageData->enabled = false;
						
					} elseif ( $this->pageData->task=="plugin.apply" ||
							$this->pageData->task == "plugin.save" ) {
						
						// been disabled from the plugin params
						$ids = Array ( $this->pageData->extension_id );
						// pageData->enabled will already be present, because its
						// submitted as part of the form, so below, eventDisable will
						// either be set to true, or false as appropriate
					}
					
					// now see if our id is in it.
					if( in_array($pluginid, $ids) ) {
						// then we are going to get disabled from the plugin list... boo hoo!
						return @!$this->pageData->jform->enabled;
					}
				} else {
					$this->log("Couldnt get plugin id for some reason",true);
				}
				
			} 
			
			//seems not, what about being uninstalled?
			if(@$this->pageData->option == "com_installer" && @$this->pageData->view=="manage" &&
				(@$this->pageData->task == "manage.remove" || @$this->pageData->task =="manage.unpublish") ) {
				// somethings being removed, lets see if its us...
				if( $pluginid = $this->getThisPluginId() ) {
					$ids = $this->pageData->cid;
					return in_array($pluginid, $ids);
				} else {
					$this->log("Couldnt get plugin id for some reason",true);
				}
			}
		}
		
		return false;
	}
	
	// get the id of this plugin from the extensions table (it doesnt appear to be accessible from anywhere else)
	function getThisPluginId() {
		$q="select extension_id from #__extensions where type=\"plugin\" and element=\"" . $this->_name
			. "\" and folder=\"" . $this->_type . "\"";
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		return $db->loadResult();
	}
	
	
	// gets the get and post variables and makes them accessibly via an object structure
	function getPageData() {
		// merge the post with get, so that nonces cant inject post information
		/*$application = JFactory::getApplication();
		$test = $application->input->get("option");
		die("Hello: " . print_r($test,true));*/
		return $this->parseArrayToObject( array_merge(JRequest::get("GET"), JRequest::get("POST") ) );
	}

	// provided by http://forum.weblivehelp.net/web-development/php-convert-array-object-and-vice-versa-t2.html
	function parseArrayToObject($array) {
	   $object = new stdClass();
	   if (is_array($array) && count($array) > 0) {
		  foreach ($array as $name=>$value) {
			 $name = strtolower(trim($name));
			 if (!empty($name)) {
				$object->$name = $value;
			 }
		  }
	   }
	   return $object;
	}
	
	function openlog() {
		if($this->parameters->logging) {
			// this will put the log file in /plugins/system/pluginname/pluginname.log.php
			
			$logfile=JPATH_PLUGINS . DS . $this->_type . DS . $this->_name . DS . $this->_name . ".log.php";
			// check to see if it exists, if it doesnt, well create a php file, with die(); as the first line
			// so that if anyone tries to read it, it will fail (obviously it will be called logfilename.php)
			
			jimport('joomla.filesystem.file');
			
			$exists=JFile::exists($logfile);
			$this->logFileHandle=fopen($logfile,"a+");
			
			if( !$exists ) {
				// put in the opening php tag and the die at the top, so that anyone trying to read the log file
				// just gets a die
				fwrite( $this->logFileHandle, "<?php\n die(\"Restricted access\");\n?>\n" );
			}
		}
	}
	
	function log( $message, $whack = false) { 
		if($this->parameters->logging) {
			$now=date("d-M-Y H:i:s");
			$url=JURI::current();
			// make the whacks quite prominent
			$whack= $whack ? "WHACK!" : "";
			$logentry=$whack . " " . $url . " " . $now . " " . $message . "\n";
			@fwrite($this->logFileHandle,$logentry);
		}
	}
	
	function closelog() {
		$this->log("Plugin Finished ===================================================================================");
		@fclose($this->logFileHandle);
	}
	
	// for showing messages in the admin backend
	function enqueMessage( $message ) {
		$app = & JFactory::getApplication(); 
		$app->enqueueMessage( $this->_name . " - " . $message );
	}
	
	// just runs queries like update, delete, insert etc..
	// if a resultset is need, then this cannot be used.
	function execQuery($q) {
		// keeping database access on an as and when basis, because db access is only needed when in admin mode
		// (mostly)
		
		$db = JFactory::getDBO();
		
		$db->setQuery( $q );
		
		try {
			$result=$db->query();
			$this->log("Successully executed [" . $q . "]");
			return $result;
		} catch (RuntimeException $e) {
			$this->log("********* SQL ERROR REPORT !!!!! ***************** ",true);
			$this->log("The query [" . $q . "] Caused the Error [ " . $db->getErrorMsg() . 
				"] the exception message is [" . $e . "]",true);
			$this->log("********* END OF SQL ERROR REPORT  ***************** ",true);
		}
		return false;
	}
	
}