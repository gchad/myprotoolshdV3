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
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
class RstboxControllerItem extends JControllerForm
{
	public function postSaveHook($model, $data) 
	{ 
        $item = $model->getItem(); 
        $id = $item->get('id');
        $values = array(); 

        /* Check if box is assigned to all pages */
        $assignToAllMenus = ($data["prm_allmenus"] == "1") ? true : false;
        if ($assignToAllMenus) {
            array_push($values,"($id,-1)");
        }

 		/* Prepare Relation between boxes & menu items */
        foreach ($data["menuitems"] as $menuitem) {
            array_push($values,"($id,$menuitem)");
        }

        $values_prepared = implode(",", $values);
        $sql_for_menu_items = "INSERT INTO #__rstbox_menu (boxid, menuid) VALUES $values_prepared";

        RstboxHelper::del("rstbox_menu", "boxid=$id");
        RstboxHelper::runquery($sql_for_menu_items);
	} 
}