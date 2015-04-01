<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('_JEXEC') or die;
jimport( 'joomla.form.fields.list' );
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Location field for the Tydlyn package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tydlyn
 * @since       2.5
 */
class JFormFieldLocation extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Location';
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$newarr = $this->getLatLng();
		$lat="";
		$lng="";
		$lat = $newarr[0]; $lng = $newarr[1];
		
		//unset($newarr[1]);
							
					
		$html = array();
		//$prefix = $this->formControl . '_';
		$html[] = '<input type="hidden" name="jform[hellomapsusers][mappajf][]" id="jform_hellomapsusers_lat" value="' . $lat . '" />';
		$html[] = '<input type="hidden" name="jform[hellomapsusers][mappajf][]" id="jform_hellomapsusers_lng" value="' . $lng . '" />';
		$html[] = $this->getMap(); 
		return implode("\n", $html);
	} 

	public function getMap()
{
		
		JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
		$document = JFactory::getDocument();			
		$document->addScript("http://maps.google.com/maps/api/js?sensor=true");
		$document->addScript(JURI::root().'plugins/user/hellomapsusers/js/location.js');
		// Add styles
		$style  = '#hellomap_canvas img {'
				. 'width: auto; display:inline; max-width: none; }'
				. '#hellomap_canvas label {  width: auto; display:inline; } ';
		
		
		$document->addStyleDeclaration($style);
		$html = array();
		$html[] = '<div> <input id="jform_hellomapsusers_address" type="textbox" style="width:60%" value="">';
		$html[] = '<button type="button" value="Geocode" onclick="codeAddress();" id="' . $this->id . '" class="btn btn-success">Search</button>';
		$html[] = '</div>';
		$html[] = '<div id="hellomap_canvas" style="width:100%; height:350px;"></div>';
		$html[] = '<div style="clear:both"></div>';
		$html[] = '<script type="text/javascript">initialize();</script>';
		
		
		return implode("\n", $html);
		
		}
		
		public function getLatLng()
	{
		
		$app  =JFactory::getApplication();
		$user =JFactory::getUser();
		
    	if ($app->isSite())  $userId = $user->get('id');
   		if ($app->isAdmin()) $userId = JRequest::getVar('id');
			
	    // Load the profile data from the database.
		$db = JFactory::getDbo();
		$db->setQuery(
				'SELECT profile_value FROM #__user_profiles' .
				' WHERE user_id = '.(int)$userId." AND profile_key LIKE 'hellomapsusers.mappajf'" 
		);
		$results = $db->loadResult();
		// Check for a database error.
		if ($db->getErrorNum())
		{
				$this->_subject->setError($db->getErrorMsg());
				return false;
		}
		
		$str_remove = array("[","]",'"');
		$new_str = str_replace($str_remove, "", $results);
 		$arr = explode(',', $new_str);
  		//$data = print_r($arr, true);
  		//echo "<pre>$data</pre>";
		
		return $arr;
	} 
}