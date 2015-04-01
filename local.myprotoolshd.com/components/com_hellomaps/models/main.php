<?php  
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport ('joomla.application.component.model');

class JForceMapModelMain extends JModelLegacy
{
	function getUser($data)
	{
		$data = JRequest::get('post');		
		$document =& JFactory::getDocument();
		$uri = JURI::root();
		$db =& JFactory::getDBO();
		$userdata= JRequest::getVar('username');
		$wherecondition = " (username LIKE '$userdata%')";
		$sqlside="SELECT id,name FROM #__users WHERE $wherecondition";
		$db->setQuery($sqlside);
		$userinside = $db->loadObjectList();
		return $userinside;
		//return $send[$i];
	}
	
	function suggestaddress($add) {
		
		$add = JRequest::get('post');		
		$document =& JFactory::getDocument();
		$uri = JURI::root();
		$db =& JFactory::getDBO();
		
		$querystr= JRequest::getVar('address');
		$sqladdr="SELECT value FROM #__community_fields_values WHERE field_id=8 and value LIKE '$querystr%'";
		$db->setquery($sqladdr);
		$loadaddress = $db->loadObjectList();
	 	//$prova="Marco";
	 	return $loadaddress;
	}
	
	
	function tomodule()
    {
		
		$uri = JURI::root();
		$db =& JFactory::getDBO();
		
		$newsql="SELECT id,name FROM #__users";
		$db->setQuery($newsql);
		$userdb = $db->loadObjectList();
		
        return $userdb;
       
        
    }
	
	
	
}