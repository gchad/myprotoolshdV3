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

// now we can inherit the class in lunarExtention.php, and make it specific to com_<extension>

class le_com_community extends lunarExtention {
	
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
		$this->log("com_community processInput task is [" . @$this->pageData->task . "]");
		if(@$this->pageData->task=="register_save" || @$this->pageData->task=="register") {
				//&& (@$this->pageData->jsname || @this) {
			if( $this->pageData->jsname ) {
				$usernameseed = $this->pageData->jsname;
			} elseif ( $this->pageData->jsfirstname ) {
				$usernameseed = $this->pageData->jsfirstname . $this->pageData->jslastname ;
			} else {
				$usernameseed = $this->pageData->jsemail ;
			}
			
			
			
			$username = $this->genUserName( $usernameseed );
			$this->log("com_community:setting username to " . $username);

			JRequest::set(array("jsusername" => $username),"post");
		}
		
		
		/*if($this->pageData->task=="azrul_ajax" && @trim($this->pageData->func) == "connect,ajaxCheckUsername") {
			$this->log("generating temp username for validation check");
			JRequest::set( Array("arg3"=>"\"_d_\",\"usr\""), "get" );
			JRequest::set( Array("arg3"=>"\"_d_\",\"usr\""), "post" );
		}*/
		
		if(@$this->pageData->task=="azrul_ajax" && 
			@trim($this->pageData->func)=="connect,ajaxCreateNewAccount") {
			// its a jom social ajax request to create a new account, probably via 
			// facebook connect
			$this->log("com_community facebook connect ajax call");
			
			$firstfield="_d_";
			
			if(strpos($this->pageData->arg2, ",")) {
				$this->log("exploding value");
				$usernameseed = explode(",",$this->pageData->arg2);
				$firstfield=$usernameseed[0];
				$usernameseed = $usernameseed[1];
			}
			
			$this->log("username seed for JomSocial AjaxCall is [" . $usernameseed . "]");
			$this->log("REMEMBER: dont be alarmed by '[' or ']' in username seeds, they get sanitised by genUsername");
			
			// now generate the username
			$username = $this->genUsername($usernameseed);
			$usernamevalue="[\"" . $firstfield . "\",\"" . $username . "\"]";
			$this->log("Setting username to (" . $usernamevalue . ")");
			JRequest::set( Array("arg3"=>$usernamevalue), "get" );
			
		}
		$this->log("processInput for com_community complete");
	}
	
}
?>