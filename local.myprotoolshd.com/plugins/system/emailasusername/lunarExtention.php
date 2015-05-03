<?php
/**
* @version		$Id: LunarHotel EmailAsUsername Extention class $
* @package		Joomla 3.x Native version
* @copyright	Copyright (C) 2011 LunarHotel.co.uk. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


class lunarExtention {
	// new classes based on this one should be called lunarExtention<Extention Name>
	// e.g. lunarExtentionCom_community
	
	var $version; // e.g. 1.6.0

	var $name; // e.g. com_users

	var $type; // e.g. com

	var $manifestFile; // e.g. manifest.xml (for getting extention version)

	var $frontEndTemplate; // rhuk_milkyway

	var $parentObj; // pointer to the calling class ( in this case, the plugin)

	var $pageData; // holds post and get variables for the inputProcessors
	
	function __construct( $name, & $parentObject ) {
		$this->parentObj = $parentObject;
		
		// get the post and get variables also.
		$this->pageData = $parentObject->pageData;
		
		$this->name = $name;
		$this->type = substr( $this->name,0,3 );
		// generally, the manifest file, is just the name of the extention with out the 
		// extension prefix. This can be overridden later by the extension class if required.
		$this->manifestFile = str_replace ($this->type . "_", "", $this->name ) . ".xml";
	
		return;
	}
	
	// would have liked to have put this in the mail EmailAsUsername class, but all the supporting functions
	// are here.. so...
	// this is the function that takes a copy of the existing templateOverrides, before the plugin is
	// enabled. - Proveded its turned on in the plugin parameters
	function backupTemplateOverrides() {
		// need the current template
		jimport('joomla.filesystem.folder');
		$template = $this->currentTemplate();
		$src = JPATH_ROOT . DS . "templates" . DS . $template . DS . "html" . DS;
		$dst = JPATH_ROOT . DS . "templates" . DS . $template . DS . "html.lunarhotelBackup";
		if(!JFolder::exists( $dst )) {
			// false means it wont take a backup in the database.
			$this->copyr ( $src, $dst, false);
			// so once this has run, the user has a backup of all their ORIGINAL (before the plugin was installed,
			// then enabled) template overrides. For as long as html.lunarhotelBackup exists, another backup will not
			// be taken
		} 
	}
	
	function hideUsername() {
		jimport('joomla.filesystem.file');
		// first get the version we're dealing with
		$this->log("in Parent (lunarExtention) hideUsername() function ");
		$ver = $this->getVersion();
		$this->log("Extension [" . $this->name . "] version is ". $ver );
		// next select the version of the template overrides we're going to use.
		$versionToUse = $this->getVersionToUse( $ver, $this->name );
		// next we need to know the name of the current front end template
		$template=$this->currentTemplate();
		// now lets see if there are template overrides specifically for the version we have found
		// this will allow us to include template overrides for specific templates
		$src = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . DS . $this->name . DS . $versionToUse . DS . $this->name;
		
		if(JFile::exists($src . DS . $template)) {
			$src = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . DS . $this->name . 
				DS . $versionToUse . DS . $template . DS;
		}
		
		$dst = JPATH_ROOT . DS . "templates" . DS . $template . DS . "html" . DS;
		
		// ok so, we have the src location, and the destination for the template overrides....
		
		// should copy the template overrides from the source to the templates html folder
		$this->copyr($src, $dst);

		// now see if there is a language file...
		// get the default front end language...
		
		$langFile = $this->getPreferredLanguage ( $this->getDefaultFrontEndLanguageCode(), $versionToUse );
		
		if($langFile!==false) {
			// now we have a language file of some sort, either the one
			$this->addLanguageOverride( $langFile );
			
		}
	}
	
	// to take advantage of Joomla's new language override feature, this function will copy
	// in the language files into /languages/override/<langcode>.override.ini
	// creating or appending to the file as needed. this way, we only need to carry the changes
	// in language files, rather than complete copies of the language file.
	// langfile is the same as whats returned from getPreferredLanguage
	
	function addLanguageOverride ( $langFile ) {
		if ($langFile->name) {
			// so we have then name, lets see if langCode.override.ini already exists.
			
			$this->log("langfile code is [" . $langFile->code . "]");
			
			$overrideFile = JPATH_ROOT . DS . "language" . DS . 
				"overrides" . DS . $langFile->code . ".override.ini";
			
			$this->log("Creating the language override file");
			
			$overrideData = file_get_contents($langFile->name);
			$this->log("Getting override data from [" . $langFile->name . "]");
			jimport('joomla.filesystem.file');
			if ( JFile::exists( $overrideFile ) ) {
				// we need to take a backup
				// of course, we only want a backup, if we dont have a backup already ****
				if(!$this->backupExists( $overrideFile )) {
					// we dont have a backup, so lets make one
					$this->backupFile( $overrideFile , 0);
					
					// leave the original file there, this way users can easily customise 
					// lang files with out having to delve into the template override structure
				}
			} else {
				// make sure our file gets deleted when the plugin is disabled
				$this->backupFile( $overrideFile , 1);
			}
				
			// add the data to the file, appending if its there already, or creating if not.
			$this->log("writing language override data to [" . $overrideFile . "]");
			file_put_contents($overrideFile, $overrideData, FILE_APPEND);
		}
	}
	
	// so this will will either return the path of $preferredLangCode (if an extention language 
	// file of that language exists)
	// or return the en-GB language file (if it exists)
	// or return false (to say there arent any language files)
	function getPreferredLanguage ( $preferredLangCode, $versionToUse ) {
		$this->log("getPreferredLanguage looking for [" . $preferredLangCode . "] for [" . $this->name . "]");
		$preferredLangFile = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . 
			DS . $this->name . DS . $versionToUse . DS . $preferredLangCode . "." . 
				$this->name . ".ini";
		
		// we're going to return an object.
		$langdef = new stdClass();
		$langdef->code=$preferredLangCode;
		$this->log("getPreferredLanguage looking for language file [" . $preferredLangFile . "]");
		jimport('joomla.filesystem.file');		
		if( JFile::exists( $preferredLangFile ) ) {
			$langdef->name = $preferredLangFile;
			
			$this->log("getPreferredLanguage Found:returning language object filename [" . 
				$langdef->name . "] and code is [" . $langdef->code . "]");
			
			return $langdef;
			
		} else {
			// default to en-GB
			if( $preferredLangCode !="en-GB" ) {
				$this->log("getPreferredLanguage Could not find [" . $preferredLangCode . "] so looking for en-GB");
				return $this->getPreferredLanguage ( "en-GB", $versionToUse );
			}
		}
		
		return false;
	}
	
	// does what it says on the tin (reads it directly from the DB)
	function getDefaultFrontEndLanguageCode() {
		//get the params from the com_languages entry in jos_extentions, then use json_decode to 
		// extract the front end language value.
		
		$q="select params from #__extensions where `name`=\"com_languages\"";
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		$params=$db->loadResult(); 
		// will give us a json encoded version of the params, so
		$settings = json_decode ( $params ) ;
		return $settings->site;
	}
	
	
	// returns the current default front end template.
	// this could be expanded to return an array of template names, that are assigned to
	// menu options. perhaps this will be built in to later versions.
	
	function currentTemplate() {
		$q="select template from #__template_styles where client_id=0 and home=1;";
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		$template=$db->loadResult();
		return $template;
	}
	
	// use the information in the version and name properties, to get the current
	// version of the extention.
	
	function getVersion() {
		
		// check to see if its an inbuilt extension, if so, just return the 
		// joomla version number (because the joomla version number changes, but the module / component
		// versions dont change (sometimes) dispite being different code.
		$builtinExtentions=Array("mod_login","com_users");
		
		if(in_array($this->name, $builtinExtentions)) {
			//just return the joomla  version
			$jver= new JVersion();
			$version=$jver->getShortVersion();
			$this->log("Detected inbuilt extention, so returning Joomla Version number [" . $version . "]");
			return $version;
		}
		
		
		//$parser	= & JFactory::getXMLParser('Simple');
		$this->log("getVersion Manifest file is [" . $this->manifestFile . "]");
		if( $this->manifestFile ) {
			// we have a manifest file, it should be in existence, but lets check anyway
			$this->log("getVersion extension type is [" . $this->type . "]");
			
			switch ($this->type) {
				case "com":
					$manifestPath=JPATH_ADMINISTRATOR . DS . "components" . DS . $this->name . DS . $this->manifestFile;
					break;
				case "mod":
					$manifestPath=JPATH_ROOT . DS . "modules" . DS . $this->name . DS . $this->name . ".xml";
					break;
				case "plg":
					$manifestPath=JPATH_ROOT . DS . $this->manifestFile;
					break;
			}
			
			jimport('joomla.filesystem.file');
			
			$this->log("getVersion manifest file is [" . $manifestPath . "]" );
			if( JFile::exists( $manifestPath ) ) {
				// its does, we're good.
				/*$parser->loadFile( $manifestPath );
				$doc =& $parser->document;
				$data = $doc->getElementByPath( 'version' );
				return $data->_data;*/
				// CHANGE KINDLY SUGGESTED BY ANATOLY DOROSHENKO
				$xml = JFactory::getXML($manifestPath);
				return (string)$xml->version;
			}
		}
		$this->log("getVersion COULD NOT ESTABLISH EXTENSION VERSION");
		return false;
	}
	
	
	// returns the most recent available version of template overrides, based on the version and extension
	// name supplied.
	
	function getVersionToUse( $currentVersion, $product ) {
		// first things first, check to see if we have the exact version we need
		
		$this->log("looking for template overrides for [" . $product . "] version [" . $currentVersion . "]");
		$overrides = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . DS . $product;
		
		if(JFolder::exists($overrides . DS . $currentVersion)) {
			// we have the exact version the calling func needs, pass it back
			$this->log("getVersionToUse: Found version [" . $currentVersion . "]");
			return $currentVersion;
		} 
		
		// get a list of versions
		$versions=JFolder::folders($overrides);
		// we need a list of supported versions, if theres no corresponding $currentVersion
		// then we need to find what the lowest version number is, thats closest to the version we need
		// for example, if we want version 1.5.18 and we have 1.5.19 and 1.5.17, then 1.5.17 is returned.
		
		if($versions) {
						
			if(count($versions)==1) {
				//return the only version we have
				$this->log("getVersionToUse: Only one version to return [" . $versions[0] . "]");
				return $versions[0];
			}
			
			// theres more than one to choose from, sort the array into numeric order
			sort($versions,SORT_STRING);
			
			// return the lowest version available if required
			//$this->log("list of version is [" . print_r($versions,true) . "]");
			if($currentVersion<$versions[0]) {
				$this->log("getVersionToUse: Returning the lowest version we have [" . $versions[0] . "]");
				return $versions[0];
			} elseif ($currentVersion>$versions[count($versions)-1]) {
				// return the highest version if required
				$this->log("getVersionToUse: Returning the highest version we have [" . $versions[count($versions)-1] . "]");
				return $versions[count($versions)-1];
			}
			
			// somewhere in the middle!!!
			// add the currentVersion to the list of versions, then sort it again :D
			$versions[]=$currentVersion;
			
			sort($versions,SORT_STRING);
			
			// now find currentVersion in the array, and return the entry below it! :D
			$current=array_search($currentVersion, $versions);
			$this->log("Version list is now [" . print_r($versions,true) . "]");
			$this->log("Version to use returning [" . $versions[$current-1] . "]");
			return $versions[$current-1];
			
		} 
	}
	
	// use joomla functions to copy from one location to another
	// if from has a trailing slash, then the contents of the folder will be copied, if trailing slash
	// is ommitted, then the folder and the contents will be copied
	// REMEMBER FOR THE setusernamevis function the SOURCE SHOULD HAVE a trailing DS
	// dbbackup option is so we can take a copy of things without telling the db about them
	// specifically this is used to take a file backup of the /templates/joomlatemplate/html folder
	// and not have it erased once the plugin is disabled (restore)
	
	function copyr($from, $to, $dbbackup=true) {
		// assume the best
		
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$this->log("copyr from [" . $from . "] to [" . $to . "]");
		if(!is_file($from)) {
			// if the destination is a file, when the source is a directory, then we're either going to return
			// false, or just over write the file with a directory of the same name.
			if(is_file($to)) {
				return false;
			}	
			// clean from 
			JPath::clean( $from );
			
			// get all the files below that location
			if(!$filelist=JFolder::files($from,"",true,true)) {
				$this->log("copyr: failed to get folder listing for [" . $from . "]");
				return false;
			}

		} else {
			// just put the file into an array
			$filelist=Array( $from );
		}
		
		// now see whats going on with the destination.
		if(!JFolder::exists($to)) {
			// the destination doesnt exist. create it
			$this->log("copyr trying to create destination [" . $to . "]");
			JFolder::create( $to );
		}
		
		// check we have a trailing DS on $to
		if(substr($to,-1,1)!=DS) {
			$to.=DS;
		}
		
		
		
		// so now we have a list of files
		foreach ($filelist as $key => $file) {
			// get the path of the file, adjusting for if there is a DS at the end of the from
			// first get the path where its going to
			$dest=dirname( str_replace($from,"",$file) );

			// now check to see if we're copying the folder specified in $from
			if(substr($from,-1,1)!=DS && !is_file($from)) {
				// we need to add the folder name from $from to the list of folders to potentially create
				$dest=JFile::getName($from) . $dest;
			}

			// now prepend the actuall destination location, and clean it to remove double DS
			$dest=JPath::clean($to . $dest);
			// now create the folder
			if(!JFolder::create($dest)) {
				$this->log("copyr: Failed to create [" . $dest . "]");
				return false;
			}
			
			// now check the file doesnt exist in the destination
			$dest.= DS . JFile::getName($file);
			// clean it (the destination)
			$dest=JPath::clean($dest);
	
			$contents=false;
			$haveBackup=$this->backupExists($dest);
			
			/*if(JFile::exists($dest) && !$haveBackup && $dbbackup) {
				// just read the contents of the file (because we're going to save the contents to the db
				$this->log("copyr: getting contents of [" . $dest . "]");
				$contents=file_get_contents($dest);
			} */
			
			// it will put a record in the backup table either way, if the file doesnt already exist, then 
			// it will enter a record with no file contents, and set the justdelete flag
			// this will tell the restore function to simply delete the file referenced, rather than restoring
			// something that wasnt there in the first place.
			// no need to backup if we already have one.
			if(!$haveBackup && $dbbackup) {
				$justDelete=JFile::exists($dest) ? "0":"1";
				
				$this->backupFile( $dest, $justDelete);
				
				// so if there is indeed a file to restore (dont just delete) then 
				// we need to erase the file thats there already
				
				if(!$justDelete) {
					$this->log("copyr: deleting [" . $dest . "]");
					JFile::delete($dest);
				}
			}
			
			if(!$dbbackup) {
				$this->log("copyr: The following copyr call will NOT backup to DB [" . $from . "] to [" . $to . "]");
			}
			// so thats the location created, now lets just copy the file from $from to $to
			$file=str_replace(DS.DS,DS, $file);
			$dest=str_replace(DS.DS,DS, $dest );

			$this->log("copyr: attempting copy [" . $file . "] to [" . $dest . "]");
			if(!JFile::copy( $file, $dest )) {
				$this->log("copyr: Failed to copy [" . $file . "] to [" . $dest . "]");
				return false;
			}
		}
		return true;
	}		
	
	function backupFile($filename, $justDelete = 0 ) {
		// need these for backup purposes (even if theres nothing to backup!)
		$db = JFactory::getDBO();
		$date = JFactory::getDate(); 
		
		$contents ="";
		
		// if its just being added to the table to make sure its deleted, when everythings restored
		// then we wont be able to get the filecontents (there isnt a file to get the content of!)
		
		if(!$justDelete) {
			$contents = file_get_contents( $filename );
		}
		
		
		$q="insert into #__eau_backups values(NULL," . $db->quote($filename) . "," . 
			$db->quote( rawurlencode( $contents ) ) . "," .
				"\"" . date("Y-m-d H:i:s") . "\"," . $justDelete . ");";
		
		
		$db->setQuery( $q );
		
		if(!$db->query() ) {
			// lets try and handle this a bit more gracefully!
			$this->log("backupFile failed backing up [" . $dest . "] with [" . $q . "]",true);
			return false;
		}
		return true;
	}
	
	// does a backup for the given file exist?
	function backupExists($filename="") {
		$db = JFactory::getDBO();
		$q="select id from #__eau_backups";
		if($filename) {
			$q.=" where filename=\"" . $filename ."\";";
		}
		$db->setQuery($q);
		return $db->loadResult();
	}
	
	// just to make log work without errors, we'll add a reference to the parent object, so this
	// can act as a wrapper.
	function log($message, $whack=false) {
		$this->parentObj->log($message, $whack);
	}
	
	// so usernames can be generated on registration / whatever input
	
	function genUserName($name) {
	
		// use the firstname & last name concatted together
		// check to see if that username is taken, if it is, remove any numbers from the either name, then
		// add a number on the end starting with 1 and incrementnumber accordingly
		$this->log("Generating username from [" . $name . "]");
		// check for the username generation policy
		
		// get rid of numbers first, they might interfere with the unique name generation
		$name= $this->remove_numbers($name);
		
		// get rid of spaces and other nasties
		$uname= str_replace( Array("\"", "[", "]", " ","'"), "", $name);
		
		// find out what other usernames exists that might conflict
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query	
			->select( $db->quoteName(array("username") ) )
			->from($db->quoteName("#__users"))
			->where( $db->quoteName("username") . " like " . $db->quote($uname . "%"));
	
		$db->setQuery($query);
		

		/*$db = JFactory::getDBO();
		$q=" username from #__users where username like " . $db->quote($uname . "%");
		$db->setQuery( $q );*/
		// load the usernames into an array
		
		//if($usernames=$db->loadResultArray()) {
		
		if($usernames = $db->loadColumn()) {
			// now implode them, so they're separated by something e.g. ,
			$usernames=implode(",",$usernames);
			// now give us the numbers from all of these.
			$nums=$this->extract_numbers($usernames);
			// now sort in descending order and check the top number
			if(count($nums)) {
				rsort($nums);
				// so now the highest number is at position 0
				$uname.=$nums[0]+1;
			} else {
				$uname.="1";
			}
		}
		// now just return the username to be used
		$this->log("Generated username [" . $uname . "] from [" . $name . "]");
		return $uname;
	}
	
	// thanks to http://www.bitrepository.com/how-to-extract-numbers-from-a-string.html for this
	function extract_numbers($string)
	{
		preg_match_all('/([\d]+)/', $string, $match);

		return $match[0];
	}

	
	// thanks to http://www.wallpaperama.com/forums/how-to-remove-numbers-from-a-string-php-t5738.html for this
	function remove_numbers($string) {
  		$numbers = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
  		return str_replace($numbers, '', $string);
  	}
	
	// function to return the username for the provided email address
	function getUsername( $email ) {
		$q="select username from #__users where email=\"" . $email . "\";";
		$db = JFactory::getDBO();
		$db->setQuery( $q );
		return $db->loadResult();

	}
	
	// adds the passed Jform to the post variable
	// jform is the whole jform entry (Complete with any vaules within it)
	// note this cannot be used to add fields to the post variable, only to 
	// jform objects WITHIN the postvariable
	
	function addJformPost( $jform ) {
		// first get the WHOLE post variable
		$allpost = JRequest::get("post");
		$allpost["jform"] = $jform;
		
		// now give it back to post variable
		//JRequest::set( $allpost ,"post");
		// Jrequest has been deprecated in favour of JInput:
		$jinput = JFactory::getApplication()->input;
		$jinput->post->set("jform",$jform);
	}
	
	// parent for processInput. theres probably nothing we can put in here, but hey.
	function processInput() {
		return;
	}
	
}