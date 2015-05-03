<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Item Model
 */
class RstboxModelItem extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param       type    The table type to instantiate
     * @param       string  A prefix for the table class name. Optional.
     * @param       array   Configuration array for model. Optional.
     * @return      JTable  A database object
     * @since       2.5
     */
    public function getTable($type = 'Items', $prefix = 'RstboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param       array   $data           Data for the form.
     * @param       boolean $loadData       True if the form is to load its own data (default case), false if not.
     * @return      mixed   A JForm object on success, false on failure
     * @since       2.5
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_rstbox.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) 
        {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return      mixed   The data for the form.
     * @since       2.5
     */
    protected function loadFormData() 
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_rstbox.edit.item.data', array());

        if (empty($data)) 
        {
            $data = $this->getItem();
            $isNew = (!$data->id) ? true : false;

            if (!$isNew) {
                /* Fetch Menu Items */
                if ($data->id) {
                    $data->menuitems = RstboxHelper::getMenuItems($data->id);
                }

                /* Check if box is assign to all pages */
                if (in_array("-1", $data->menuitems)) {
                    $data->allmenus = true;
                }

                /* Append params column values to data */
                foreach ($data->params as $key => $value) {
                    $key_new = "prm_".$key;
                    $data->$key_new = $value;
                }

                /* Append box settings column values to data */
                foreach (json_decode($data->settings) as $key => $value) {
                    $key_new = "bx_".$key;
                    $data->$key_new = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  The form data.
     *
     * @return  boolean  True on success.
     * @since   1.6
     */

    public function save($data)
    {
        
        $assignToAllMenus = ($data["prm_allmenus"] == "1") ? true : false;
        if ((!$data["menuitems"]) && (!$assignToAllMenus)) {
            $this->setError(JText::_('COM_RSTBOX_WRONG_MENU_ASSIGNMENT'));
            return false;
        }

        $data["settings"] = json_encode(RstboxHelper::searchFields($data, "bx_"));
        $data["params"] = json_encode(RstboxHelper::searchFields($data, "prm_"));

        switch ($data["boxtype"]) {
            case 'emailform':
                    if (!filter_var($data["bx_mc_url"], FILTER_VALIDATE_URL)) {
                        $this->setError(JText::_('COM_RSTBOX_WRONG_SUBMIT_URL'));
                        return false;
                    }
                break;
            case 'module':
                if (!$data["bx_moduleid"]) {
                    $this->setError(JText::_('COM_RSTBOX_ERROR_MODULEID'));
                    return false;
                }                 
                break;
            case 'custom':
                if (!$data["customhtml"]) {
                    $this->setError(JText::_('COM_RSTBOX_ERROR_CUSTOM_HTML'));
                    return false;
                }           
                break;
        }
        switch ($data["triggermethod"]) {
            case "element":
                $prm = $data["prm_triggerelement"];
                if (!$prm) {
                    $this->setError(JTEXT::_('COM_RSTBOX_ERROR_TRIGGER_ELEMENT'));
                    return false;
                }  
                break;
        }

        if (parent::save($data))
        {
            return true;
        }   
    }

}

