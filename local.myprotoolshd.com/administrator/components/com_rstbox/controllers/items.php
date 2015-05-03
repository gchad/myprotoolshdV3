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
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

class RstboxControllerItems extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since       2.5
     */
    public function getModel($name = 'Item', $prefix = 'RstboxModel', $config = array('ignore_request' => true)) 
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function delete() {
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $model = $this->getModel();

        if ($model->delete($cid)) {
        	$pks = implode(",",$cid);
            RstboxHelper::del("rstbox_menu", "boxid IN ($pks)");
        }
           
        $this->setRedirect('index.php?option=com_rstbox');
    }
}