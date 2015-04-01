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
 * @package     Joomla.Administrator
 * @subpackage  com_tydlyn
 * @since       2.5
 */

	   
class JFormFieldhellomapsarticleslocations extends JFormFieldList
{
	/**
	 * The form field type.
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'hellomapsarticleslocations';
	/**
	 * Method to get the field input markup.
	 * @return  string  The field input markup.
	 * @since   11.1
	 */
	
	protected function getInput()
	{
		
		$newarr = $this->getLatLng();
		$lat=""; $lng="";
		for ($i=0; $i<count($newarr); $i++)
  		{
   		 
		 if (empty($newarr[$i])) 
		 {
		 	unset($newarr[$i]);

		 }else
		 {
			$lat = $newarr[0]; 
			$lng = $newarr[1];
		}
		
		}
		
		//unset($newarr[1]);
							
					
		$html = array();
		//$prefix = $this->formControl . '_';
		$html[] = '<input type="hidden" name="jform[attribs][mappajf][]" id="jform_attribs_lat" value="' . $lat . '" />';
		$html[] = '<input type="hidden" name="jform[attribs][mappajf][]" id="jform_attribs_lng" value="' . $lng . '" />';
		$html[] = $this->getMap(); 
		return implode("\n", $html);
	} 

	public function getMap()
{
		
		JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
		$document = JFactory::getDocument();			
		$document->addScript("http://maps.google.com/maps/api/js?sensor=true");
		$document->addScript(JURI::root().'plugins/content/hellomaps/js/hellomapsarticleslocations.js');
		// Add styles
		$style  = '#hellomap_canvas img {'
				. 'width: auto !important; display:inline !important; max-width: none !important; }'
				. '#hellomap_canvas label {  width: auto !important; display:inline !important; } ';
		
		
		$document->addStyleDeclaration($style);
		$html = array();
		$html[] = '<div> <input id="jform_attribs_searchaddress" type="textbox" style="width:60%" value="">';
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
		$articleId = JRequest::getInt('id');
		// Get a db connection.
		$db = JFactory::getDBO();
		// Create a new query object.
		$query = $db->getQuery(true);
		/*$query
		->select($db->quoteName(array('a.*')))
		->from($db->quoteName('#__content', 'a'))
		->where($db->quoteName('a.id') . ' = '.intval($articleId));*/
		
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.attribs')));
		$query->from($db->quoteName('#__content','a'));
		$query->where($db->quoteName('id') . ' = '.intval($articleId));
		$db->setQuery($query);

		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$results = $db->loadResult();
		// Check for a database error.
		if ($db->getErrorNum())
		{
				$this->_subject->setError($db->getErrorMsg());
				return false;
		}
		
		$subcolumname = json_decode($results, true);
		//print_r($subcolumname['mappajf']);
		
		
		if (empty($subcolumname['mappajf']))
		{ 
			unset($subcolumname['mappajf']); 
			return ;
		}else{ 
		
			return $subcolumname['mappajf']; 
		}
		//return $subcolumname['mappajf'];
	} 
	
	
	 
}