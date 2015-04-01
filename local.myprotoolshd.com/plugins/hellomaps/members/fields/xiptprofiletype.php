<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Form Field class for the component
 */
if(!class_exists('XiptHelperProfiletypes') && is_file(JPATH_SITE.'/components/com_xipt/includes.php'))
    require_once JPATH_SITE.'/components/com_xipt/includes.php'; 
if(!class_exists('JFormFieldXiptprofiletype'))
{	
	class JFormFieldXiptprofiletype extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Xiptprofiletype';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
		    $options = array();
            if(class_exists('XiptLibProfiletypes'))
            {
                $allTypes = XiptLibProfiletypes::getProfiletypeArray();			
    			if (!empty($allTypes))
    			{			
    				//$options[] = JHtml::_('select.option', '', JText::_('JSelect'));
    				foreach($allTypes as $item) 
    				{
    					$options[] = JHtml::_('select.option', $item->id, $item->name);
    				}
    			}    
            }
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
