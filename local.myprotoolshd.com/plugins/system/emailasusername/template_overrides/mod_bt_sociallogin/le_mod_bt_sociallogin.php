<?php
/**
* @version		$Id: LunarHotel EmailAsUsername Extention class instance (com_users) $
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

class le_mod_bt_sociallogin extends lunarExtention {
	
	function __construct( $name, & $parentObject ) {
		parent::__construct( $name, $parentObject );
	}
	
	/*function hideUsername() {
		// get the extension version
		parent::hideUsername();
	}*/
	
	// this function will process the input from pages affected by the template
	// overrides. in most cases this will mean generating a username from the posted
	// information, and adding the generated username to the post variable, for processing
	// by the target extension.
	
	function processInput() {
		// get the pagedata so we have a fresh copy.
		parent::processInput();
		
		if(@$this->pageData->bttask=="register" ) {
			
			$this->log("mod_bt_sociallogin:ajax call ");

			// need to generate a username
			$username = $this->genUserName($this->pageData->jform['name']);
			$this->log("mod_bt_sociallogin:setting username to " . $username);

			$jform = $this->pageData->jform;
			
			$jform["username"] = $username;
			
			// now set the value in the post variable
			$this->addJformPost( $jform );
			
			$postData = JRequest::getVar('jform');
			JRequest::setVar('jform',$jform,"get");
			$this->log("mod_bt_sociallogin:Postdata: [" . print_r($postData,true) . "]");
		      return;
		}
		
		$this->log("processInput for mod_bt_sociallogin complete");
	}
	
}
?>