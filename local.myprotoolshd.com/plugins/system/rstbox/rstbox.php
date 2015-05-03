<?php

/**
 * @package         Responsive Scroll Triggered Box
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2015 TM Extensions All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemRstBox extends JPlugin
{
    public static $boxesHTML;
    private $_app;
    private $_params;
    private $_view;

    function __construct(&$subject, $config = array()) {
        
        // get application & component params  
        $this->_app = JFactory::getApplication();
        $this->_params = JComponentHelper::getParams('com_rstbox');

        $input = JFactory::getApplication()->input;
        $this->_view = $input->get('format','html','cmd');
        
        // execute parent constructor
        parent::__construct($subject, $config);
    }

    function onAfterRoute() {
        /* Do nothing on back end or if format is not html */
        if (($this->_app->isAdmin()) || ($this->_view != 'html')) return true;

        /* Get active menu id */
        $menuActive = $this->_app->getMenu()->getActive();
        if (!$menuActive) { return; }
        $id = $menuActive->id;

        if ($id) {
            /* Load RSTBox Helper class */
            JLoader::register('RstboxHelper', JPATH_ADMINISTRATOR.'/components/com_rstbox/helpers/rstbox.php');

            $user = JFactory::getUser();

            /* Get all boxes based on active menu id & user authorised view levels */
            $boxes = RstboxHelper::getBoxes($id, $user->getAuthorisedViewLevels());

            /* Check if is there at least one box and then load css and js files */
            if ($boxes) {
                if (!$this->_params->get("forceloadmedia", false)) {
                    RstboxHelper::loadassets_front();
                }

                /* Prepare HTML */
                $html = RstboxHelper::renderLayout("rstbox", $boxes);
                self::$boxesHTML = $html;
            }
        }
    }

    function onAfterRender() {
        /* Do nothing on back end or if format is not html */
        if (($this->_app->isAdmin()) || ($this->_view != 'html')) return true;

        /* Check if we have valid boxes */
        if (self::$boxesHTML) {

            /* Append HTML to body */
            $buffer = JResponse::getBody();
            $buffer = preg_replace("/<\/body>/",self::$boxesHTML."\n</body>", $buffer); 

            JResponse::setBody($buffer);
        }

        return true;
    }
}
