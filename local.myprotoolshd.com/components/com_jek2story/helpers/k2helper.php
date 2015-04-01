<?php
/**

* @package   JE K2 STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/     
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

class k2helper {
	function getdata()
	{
	$db = jFactory::getDBO();
	$query = 'SELECT id,created_by FROM #__k2_items ';
	$db->setQuery($query);
	$data = $db->loadObjectList();
	
	for($i=0;$i<count($data);$i++){
	
	$query = 'SELECT * FROM #__je_k2itemlist where itemid = '.$data[$i]->id; 
	$db->setQuery($query);
	$res1 = $db->loadObject();
	
		if(!$res1){
		
			$query = 'SELECT * FROM #__users where id = '.$data[$i]->created_by; 
			$db->setQuery($query);
			$res2 = $db->loadObject();
			
			$sql = "INSERT INTO #__je_k2itemlist (`itemid`,`userid`,`name`,`email`,`published`) values (".$data[$i]->id.",".$data[$i]->created_by.",'".$res2->username."','".$res2->email."','".$data[$i]->published."')";  
			 $db->setQuery($sql);
			 $db->query();
		}
	
	}
	

	}
		
	

		
}


?>