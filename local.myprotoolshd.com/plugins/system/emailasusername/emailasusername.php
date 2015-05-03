<?php
/**
* @version		$Id: emailasusername.php 2014-03-28 (version 5.24) $
* @package		Joomla 3.x Native version
* @copyright	Copyright (C) 2014 LunarHotel.co.uk. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
/*
This version:
Support for Jomsocial 4.x
New support for byPV one page checkout for virtuemart - Also works with the remove name field option
Included support for osmembership (membership pro)
updated bt-sociallogin support
Added option to remove name field in virtuemart registration

Support for Virtuemart 3
Updated Hikashop support.
Updated SCLogin support
Updated Hikashop support again! :)
Updated Hikashop support
Updated K2 support. That is added it.
Updated support for JomSocial
Fixed bug with username creation, thanks to Jordan Dimov for pointing this out.
Fixed bug with potential duplicate username generation
SCLogin support
Username still showing in user profile page
updated template overrides for mod_login
Fixed broken password reset
Support for JomSocial Hellome module
BT Login doesnt use the standard Joomla com_users registration function which was causing "username already registered errors", so additional support build into template override
Includes temporary fix for the effects of Joomla 3 (idiotic) handling of get and post
Support for JomSocial 3.0
Support for BT_Login module
Minor release to fix JRequest deprecation. Major release to follow
Support for JShopping, fixed package manifest XML code, updated database access (Thanks to Antoly Doroshenko for pointing out these problems)
*/

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

defined('JPATH_BASE') or die;

define( "OVERRIDE_PATH" 	, 	"template_overrides" );
define( "LE_CLASS_PREFIX"	,	"le");
define( "JPATH_PLUGIN_ROOT" ,   JPATH_ROOT . DS . "plugins" . DS . "system" . DS . "emailasusername");


jimport( 'joomla.filesystem.file' );
jimport( 'joomla.plugin.plugin' );

require ( dirname(__FILE__) . DS . "apollo.php" );
/**
 * Plugin class for Removing Username from supported Joomla 1.6 Components.
 */
  
 /*
	This is a beta release which supports Joomla's inbuilt registration & login only.
	support for more extensions will be included as extension authors build in Joomla! 1.6
	compatibility
 */
 
 class plgSystemEmailAsUsername extends apolloPlugin {
	
	function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		
		// we dont need to check if we're in admin mode, because eventDisable cant be
		// set unless we are.
		
		
		if( $this->eventDisable ) {
			// the plugin has been disabled / uninstalled.
			// do the clean up and set everything back to the way it was
			// restore will also remove the backup table, so that the plugin will
			// correctly assume it needs creating when the plugin is next enabled
			$this->log("Plugin has been disabled, restoring backups");
			$this->restoreBackup();
			

		} else {
			// check for the first time run
			if( $this->firstRun() ) {
				$this->log("First run detected");
				// do the preflight checks
				// create the backup table
				$this->createBackupTable();
				// call the respective extention classes
				$this->hideUserNames();
			}
		}
		
		/*if(JRequest::getCmd("option")=="community" && trim(JRequest::getVar("task")=="azrul_ajax") && 
				trim(JRequest::getVar("func"))=="connect,ajaxCreateNewAccount") {
			$this->log("JOMSOCIAL CHECKING USERNAME",true);
			$usernamevalue="[\"d\",\"dylangen\"]";
			JRequest::set( Array("arg3"=>$usernamevalue), "get" );
		} */
		$this->log("constructor of system plugin. get is [" . print_r(JRequest::get("get"),true) . "]");
	}
	
	// function hides the username field in supported extentions.
	function hideUserNames() {
		// first we need to get a list of supported extentions. we can get that from the
		// template override store.
		
		
		$extLocation = dirname(__FILE__) . DS . OVERRIDE_PATH;
		
		$extentions=$this->getFolderList( $extLocation );
		
		foreach ($extentions as $extention ) {
			// first, check the extension is actually installed
			if ( $this->extentionInstalled( $extention ) ) {
				
				// it is, see if we can locate an extension class for it.
				$extClassFile=$extLocation . DS . $extention . 
					DS .	LE_CLASS_PREFIX . "_" . $extention . ".php";

				// so whats going on here?
				// well, not ALL supported extensions (specifically modules) need any custom code
				// for overriding the output. So, this is kind of a class override system, where if
				// an extention class for the current extension exists, it is used, otherwise, we just
				// use the default one
				// this will cut down on the messing around that needs to be done in order to get new
				// extensions supported
				$this->log("checking for existence of [". $extClassFile  . "]");
				jimport('joomla.filesystem.file');
				if( JFile::exists( $extClassFile ) ) {
					// it does exist, so go ahead and include it
					include_once ($extClassFile);
					// now create the class and let it do the rest
					$extClassName = LE_CLASS_PREFIX . "_" . $extention;
					// create the new class, tell it what extention its for (more to minimise editing of
					// the extention class, than anything else. Also pass it a reference to this object
					// so it can use the logging features, and anything else we might choose
					
				} else {
					// otherwise just include the lunarExtention class and use that.
					$this->log("couldnt find [". $extClassFile  . "] so instigating parent class");
					include_once ( dirname(__FILE__) . DS . "lunarExtention.php" );
					$extClassName = "lunarExtention";
				}
				
				$extClass= new $extClassName ($extention, $this);

				// a bit inefficient, but will only be run when the plugin is enabled
				// and not everytime a page loads.
				if($this->parameters->backupTemplateOverrides) {
					$extClass->backupTemplateOverrides();
				}
				$this->log("Running hideUsername()");
				$extClass->hideUsername();
					
				// now kill it
				unset( $extClass );	
			} else {
				$this->log("Extension [" . $extention . "] is not installed");
			}
		}
	}
	
	
	function extentionInstalled($extentionName) {
		$exttype=substr($extentionName,0,3);

		$this->log("Extention type is [". $exttype . "]");
		$folder="";
		// this is for certain system plugins (bypv One page checkout) 
		// that actually has views, and amazingly whose templates can be overridden!
		if($exttype!="com" && $exttype!="mod") {
			$folder="system";
		}

		$q="SELECT extension_id FROM #__extensions WHERE `element`='". $extentionName . "'" . 
			" and folder='" . $folder . "';";
		
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		return $db->loadResult();
	}
	
	
	// returns true if it appears its the plugins first time running (e.g. if its just been
	// enabled)
	function firstRun() {
		
		$this->log("detecting firstRun");
		$q="insert into #__eau_backups values(NULL,'A','B','" . date("Y-m-d H:i:s") . "',1);";
		// need to use this method for query because we need insertid later
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		
		try {
			$result = $db->execute();
			// its good it worked, so clean up
			$this->log("firstrun cleaning up..");
			$q="delete from #__eau_backups where id=" . $db->insertid();
			$this->execQuery( $q );
			return false;
		} catch (RuntimeException $e) {
			// no its not the first time.
			return true;
		}
	}
	
	
	// go through the backup table and restore anything that needs restoring, delete anything that needs
	// deleting
	function restoreBackup() {
		
		$this->log("Restoring files from the backup table");
		$db = JFactory::getDBO();
		// get everything
		$q="select * from #__eau_backups";
		$db->setQuery( $q );
		try { 
			$results=$db->loadObjectList();

		} catch (RuntimeException $e) {
			$this->log("some kind of problem restoring the backedup files, lets face it, odds are 99% its a database error");
			return false;
		}
		
		jimport('joomla.filesystem.file');
		
		$this->log("files to restore or delete [" . count($results) . "]");
		
		foreach($results as $result) {
			
			// first check what we need to do
			if( !$result->justdelete ) {
				// write the file
				// decode the escaped / slashed content first
				$content = rawurldecode($result->contents);
				file_put_contents( rawurldecode($result->filename), $content);
			} else {
				// just get rid of the one thats there
				$this->log("Deleting file [" . $result->filename . "]");
				JFile::delete($result->filename);
			}
		}
	
		$this->log("Removing the backup table");
		$q="drop table #__eau_backups;";
		$this->execQuery($q);
	}
	
	// returns an array of folders contained in the given $dir
	function getFolderList( $dir ) {
		$dir = dir($dir);
		if($dir) {
			while (false !== $entry = $dir->read()) {
				// Skip pointers
				if (!($entry == '.' || $entry == '..')) {
					$extentions[]=$entry;
				}
			}
		}
		return $extentions;
	}
	
	
	// create the backup table - table is processed and removed by restoreBackup
	function createBackupTable() {
		$q=" CREATE TABLE IF NOT EXISTS `#__eau_backups` (" .
				"id bigint(20) NOT NULL auto_increment," .
				"filename tinytext NOT NULL," .
				"contents MEDIUMBLOB," .
				"backupdate datetime NOT NULL," .
				"justdelete tinyint(4) NOT NULL default '0'," .
				"PRIMARY KEY  (`id`)" .
				") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		if($this->execQuery( $q )) {
			$this->log("File backup table created successully");
		} else {
			$this->log("WARNING:File backup table creation FAILED",true);
		}
	}
	
	// See my rant in onAfterRoute
	// Assumes getPageData has already been called
	function getJoomla3GlobalRequestVars() {
		$jReqGlobals=Array("option"=>"",
				"view"=>"",
				"task"=>"",
				"tmpl"=>"");
		
		$values = JFactory::getApplication()->input->getArray( $jReqGlobals );
		// now add them into the pageData object
		foreach($values as $value => $data) {
			$this->pageData->$value = $data;
		}
	}
	
	function onAfterRoute() {
		// dont run if in admin mode, there's no point
		
		// since Joomla 3 seems to have dispensed with a) being able to get request variables in sef mode
		// when plugins are instantiated, and b) no longer allows calling programs to get all variables from 
		// the request or post array easily, we might have to go down the route of serialising the whole jinput 
		// object and then unserialising it to get this information. Its hacky, and inefficient, but its a backstop.
		// before we go down that road, we'll try adding in specific variables in the extension objects. (le_com)
		
		//$test = $application->input->get("option");
		//$test = JFactory::getApplication()->input->serialize();
		//$test = JFactory::getApplication()->input->getArray( Array("option"=>"","view"=>"","task"=>"","tmpl"=>"") );
		// now unserialise
		//$test = unserialize($test);
		//die("Hello: " . print_r($test,true));
		
		if(!$this->inadmin) {
			// figure out which component we are dealing with
			$this->pageData = $this->getPageData();
			// bit of a retrofit for the stoooopid Joomla 3 request & post array handler.
			$this->getJoomla3GlobalRequestVars();
			
			
			$this->log("In onAfterRoute");
			
			$this->log("Page data is [" . print_r($this->pageData,true) . "]");
			
			// the jomsocial redirect
			$this->log("Checking for JomSocial Profile page redirect");
			$this->log("Pagedata is [" . print_r($this->pageData,true) . "]");
			@$this->log("jsprofileredirect is [" . $this->parameters->jsprofileredirect . "]");
			if(!$this->inadmin && @$this->pageData->option=="com_users" && @$this->pageData->view=="profile"
				&& $this->parameters->jsprofileredirect) {
				// check to see if jomsocial is installed, if so, redirect the user to the jomsocial profile
				// page instead of the Joomla one.
				$this->log("Destination is Joomla profile page, checking JomSocial is installed");
				if( $this->extentionInstalled("com_community")) {
					$this->log("JomSocial is indeed confirmed, redirecting to JomSocial profile page");
					$app =& JFactory::getApplication();
					$app->redirect("/index.php?option=com_community&view=frontpage&Itemid=" . 
						$this->parameters->jsItemid);
				}
			}
			

			$extensionClassFile="";
			
			
			// if jomsocial (com_community) makes an ajax call, it uses community as the option (for some reason)
			// so this just makes sure that all option command include com_
			if( @strpos($this->pageData->option,"com_")===false ) {
				@$this->pageData->option="com_" . $this->pageData->option;
			}
			
			if(@$this->pageData->bttask) {
			  $this->pageData->option="mod_bt_sociallogin";
			}
		
			if(@$this->pageData->option) {
				$extensionClassFile = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . DS . 	
					$this->pageData->option . DS . LE_CLASS_PREFIX . "_" . $this->pageData->option . ".php";
			}
			
			
			$this->log("Checking for extension class [" . $extensionClassFile . "]");
			jimport('joomla.filesystem.file');
			if(JFile::exists( $extensionClassFile )) {
				// we have an extension class for the current component, so lets load 'er up!
				$this->log("including extension class");
				include_once ( $extensionClassFile );
				// now lets create the object
				$this->log("Creating object");
				$extensionClassName = LE_CLASS_PREFIX . "_" . $this->pageData->option;
				$extensionClass= new $extensionClassName ($this->pageData->option, $this);
				// now call the input handler, which will decide if any action needs taking, and if so, 
				// take it
				$this->log("Calling processInput");
				$extensionClass->processInput();
				$this->pageData = $this->getPageData();
// 				die("<pre>" . print_r($this->pageData,true) . "</pre>");
			}
		}
	}
	
	function onAfterInitialise() {
		/*$jinput = JFactory::getApplication()->input;
		$jform=$jinput->get('jform', array(), 'array');
		$jform['username']="DYLAN";
		$jform=$jinput->set('jform',$jform);*/
	}
	
	/*function onAfterRender() {
		$this->pageData = $this->getPageData();
		$this->log("in AfterDispatch ");
		
		if( $this->pageData->action=="register.terms.getTerms" ) {
			die("here");
		}
		
		$doc= & JFactory::getDocument();
		$buffer=$doc->getBuffer("component");
			
		$html1="Login Username";
		$buffer=str_replace($html1,"Argh!",$buffer);
		$doc->setBuffer($buffer, "component");
	}*/
	
 }
?>