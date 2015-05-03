<?php
/**
* @version		$Id: LunarHotel EmailAsUsername Extention class instance (bypc OPC for Virtuemart) $
* @package		Joomla 1.6 Native version
* @copyright	Copyright (C) 2011 LunarHotel.co.uk. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

require_once( JPATH_ROOT . DS . "plugins" . DS . "system" . 
	DS . "emailasusername" . DS . "lunarExtention.php" );

// now we can inherit the class in lunarExtention.php, and make it specific to com_users
// there wont be much work customising it.

class le_opc_for_vm_bypv extends lunarExtention {
	
	function __construct( $name, & $parentObject ) {
		$name="opc_for_vm_bypv";
		parent::__construct( $name, $parentObject );
		//override these
		$this->manifestFile="plugins" . DS . "system" . DS . "opc_for_vm_bypv" . DS . "opc_for_vm_bypv.xml";
		$this->type="plg";
	}
	
	function processInput() {
		parent::processInput();
		@$this->pageData->task=strtolower( $this->pageData->task );
		// this can be quite a small set of conditions (infact just one) because we know
		// to get here, we've passed all the previous tests of the virtuemart extension class.
		if(@$this->pageData->task=="checkout" ) {
			
			$this->log("opc_for_vm_bypv:Editing the billing / shipping details / registration");
			
			// need to generate a username, hopefully at least one of these will give us a seed
			if(!$usernameseed=@$this->pageData->bypv_billing_address_first_name . 
				@$this->pageData->bypv_billing_address_middle_name . 
				@$this->pageData->bypv_billing_address_last_name) {
			  
			  if(!$usernameseed=$this->pageData->bypv_billing_address_name) {
				  if(!$usernameseed=$this->pageData->company) {
					  $usernameseed=$this->pageData->email;
				  }
			  }
			 }
			
			$username = $this->genUserName($usernameseed);
			$this->log("opc_for_vm_bypvt:setting username to " . $username);
			if(!$this->pageData->name) {
			  // the name field was probably hidden
			  // use the vm values to populate it.
			  JRequest::set(array("bypv_billing_address_name" => $usernameseed),"post");
			}
			// now set the value in the post variable
			JRequest::set(array("bypv_billing_address_username" => $username),"post");
			
			return;
		}
		$this->log("processInput for opc_for_vm_bypv complete");
	}
	
	// this needs its own hideUsername because the plugin actually runs within com_virtuemart, so that where we need to copy
	// the overrides.
	function hideUsername() {
		jimport('joomla.filesystem.file');
		// first get the version we're dealing with
		$this->log("opc_for_vm_bypv hideUsername() function ");
		$ver = $this->getVersion();
		$this->log("Extension [" . $this->name . "] version is ". $ver );
		// next select the version of the template overrides we're going to use.
		$versionToUse = $this->getVersionToUse( $ver, $this->name );
		// next we need to know the name of the current front end template
		$template=$this->currentTemplate();
		// now lets see if there are template overrides specifically for the version we have found
		// this will allow us to include template overrides for specific templates
		$src = JPATH_PLUGIN_ROOT . DS . OVERRIDE_PATH . DS . $this->name . DS . $versionToUse . DS . "com_virtuemart";
		
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
	
}
?>