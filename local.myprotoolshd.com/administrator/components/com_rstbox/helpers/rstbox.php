<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */
 
// No direct access to this file
defined('_JEXEC') or die;
 
abstract class RstboxHelper {
    public static $assdir = "components/com_rstbox/assets/";

    /* General Functions */
    public static function getVer() {
        $xmlFile = JPATH_ADMINISTRATOR .'/components/com_rstbox/rstbox.xml';

        if (self::jVer() == "3") {
            $xml = JFactory::getXML($xmlFile);
            $version = (string)$xml->version;

            return $version;
        } else {
            $parser = JFactory::getXMLParser('Simple');
            $parser->loadFile($xmlFile);
            $doc = $parser->document;
            $element = $doc->getElementByPath('version');
            $version = $element->data();

            return $version;        
        }
    }

    public static function clean($string) {
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function renderField($field) {
        $fieldName = self::clean(str_replace("jform","",$field->name));
        return '<div class="control-group '.$fieldName.' clearfix "><div class="control-label">'.$field->label.'</div><div class="controls">'.$field->input.'</div></div>';
    }

    public static function renderFormFields($fieldset) {
        $html = "";

        foreach ($fieldset as $field) {
            $html .= self::renderField($field);
        }

        return $html;
    }

    public static function renderLayout($file, $displayData) {
        $mainframe = JFactory::getApplication();

        $templatePath = JPATH_THEMES."/".$mainframe->getTemplate()."/html/layouts/com_rstbox/";
        $componentPath = JPATH_ADMINISTRATOR."/components/com_rstbox/layouts/";

        /* Check if there is an override layout in templates folder */
        $usePath = (JFile::exists($templatePath.$file.'.php')) ? $templatePath : $componentPath;

        /* Checking Joomla version */
        if (self::jVer() == "3") {
            /* If is 3 then render layout with JLayoutFile class help */
            $layout = new JLayoutFile($file, $usePath, $displayData);
            $layoutOutput = $layout->render($displayData);
            return $layoutOutput;
        } else {
            /* If Joomla version is below 3 then render layout with custom render functions */
            ob_start();
            include $usePath.$file.".php";
            $layoutOutput = ob_get_contents();
            ob_end_clean();
            return $layoutOutput;
        }
    }

    public static function jVer() {
        if (version_compare(JVERSION, '3.0', '>=')) { return "3"; }
        if (version_compare(JVERSION, '3.0', '<')) { return "2.5"; }
    }

    public static function enableRenderPlugin() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
         
        $fields = array(
            $db->quoteName('enabled') . '=1'
        );
         
        $conditions = array(
            $db->quoteName('type') . '="plugin"', 
            $db->quoteName('element') . '="rstbox"',
            $db->quoteName('folder') . '="system"'
        );
         
        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
         
        return $db->query();
    }

    public static function loadModule($id) {
        $db = JFactory::getDBO();
        $document = JFactory::getDocument();

        $renderer = $document->loadRenderer('module');
        
        $module_style = "none";

        $params = array('style'=>$module_style);
        
        $contents = '';
        $module = 0;
        
        //get module as an object       
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__modules');
        $query->where('id='.$db->q($id));        
        $rows = $db->setQuery($query);              
        $rows = $db->loadObjectList();

        if ($rows) {
            foreach($rows as $row){     
                $params = array('style'=>$module_style);            
                $contents = $renderer->render($row, $params);
            }           
        }
        
        return $contents;
    }

    public static function boxRender($box) {
        switch ($box->boxtype) {
            case 'module':
                $layout = self::loadModule(intval($box->settings->moduleid));
                return $layout;
                break;
            case 'emailform':
                $layout = self::renderLayout("emailform", $box);
                return $layout;
                break;
            default:

                $html = $box->customhtml;
       
                if ((isset($box->params->preparecontent)) && ($box->params->preparecontent)) {
                    $html = JHtml::_('content.prepare', $html, '', 'com_rstbox');
                }

                return $html;
                break;
        }

        return $box->customhtml;
    }

    public static function validateLang($boxes) {
        $validBoxes = array();

        $lang = JFactory::getLanguage();
        $langTag = (string) $lang->getTag();

        foreach ($boxes as $box) {
            $params = $box->params;
            $params = json_decode($params);

            if (isset($params->assign_lang)) {

                $langList = (isset($params->assign_lang_list)) ? $params->assign_lang_list : array();

                // Inclusion check
                if (($params->assign_lang == "1") && (!in_array($langTag, $langList))) {
                    // Box don't pass validation
                    continue;
                }

                // Exclusion check
                if (($params->assign_lang == "2") && (in_array($langTag, $langList))) {
                    // Box don't pass validation
                    continue;
                }
            }

            $validBoxes[] = $box;
        }
        return $validBoxes;
    }

    public static function validateDevice($boxes) {
        
        if(!class_exists('Mobile_Detect')) {
            require_once("Mobile_Detect.php");
        }

        $detect = new Mobile_Detect;
        $detectDeviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');

        $validBoxes = array();

        foreach ($boxes as $box) {
            $params = $box->params;
            $params = json_decode($params);

            if (isset($params->assign_devices)) {

                $deviceList = (isset($params->assign_devices_list)) ? $params->assign_devices_list : array();

                // Inclusion check
                if (($params->assign_devices == "1") && (!in_array($detectDeviceType, $deviceList))) {
                    // Box don't pass validation
                    continue;
                }

                // Exclusion check
                if (($params->assign_devices == "2") && (in_array($detectDeviceType, $deviceList))) {
                    // Box don't pass validation
                    continue;
                }
            }

            $validBoxes[] = $box;
        }
        return $validBoxes;
    }

    public static function getBoxes($menuitemid, $userAccessLevels) {
        $user = JFactory::getUser();
        $isRoot = ($user->get('isRoot')) ? 1 : 0;

        $accesslevel = implode(",", $userAccessLevels);

        $query = "select b.* from #__rstbox b ";
        $query .= "RIGHT JOIN #__rstbox_menu m on b.id = m.boxid and m.menuid IN (-1,$menuitemid) ";
        $query .= "where b.published = 1 ";
        $query .= "AND ((b.accesslevel IN ($accesslevel)) OR (b.accesslevel IS NULL))";
        if (!$isRoot) { $query .= " AND b.testmode=0"; }

        $boxes = self::runquery($query);
        $boxes = self::validateDevice($boxes);
        $boxes = self::validateLang($boxes);

        if (is_array($boxes)) {
            foreach ($boxes as $box) {
                $box->params = json_decode($box->params);
                $box->settings = json_decode($box->settings);

                /* Box Classes */
                $classSuffix = (isset($box->params->classsuffix)) ? strip_tags($box->params->classsuffix) : "";
                $box->classes = array("rstbox_".$box->boxtype, "rstbox_".$box->position, $classSuffix);

                /* Box Style Attribute */
                $width = ($box->params->width) ? "max-width:".$box->params->width." ;" : "";
                $height = ($box->params->height) ? "height:".$box->params->height." ;" : "";
                $background = ($box->params->backgroundcolor) ? "background-color:".$box->params->backgroundcolor.";" : "";
                $color = ($box->params->textcolor) ? "color:".$box->params->textcolor." ;" : "";
                $border = ($box->params->bordercolor) ? "border:solid ".$box->params->borderwidth." ".$box->params->bordercolor.";" : "";
                $padding = (self::vld($box->params->padding)) ? "padding:".$box->params->padding.";" : "";
                $shadow = (self::vld($box->params->boxshadow)) ? "box-shadow:".$box->params->boxshadow.";" : "";
                $customstyles = (self::vld($box->params->customstyles)) ? $box->params->customstyles : "";

                /* Overlay Prepare */
                if ((isset($box->params->overlay)) && ($box->params->overlay)) {
                    $box->params->overlay = $box->params->overlay_percent.":".$box->params->overlay_color.":".$box->params->overlayclick;
                } else {
                    $box->params->overlay = false;
                }

                $box->style = $width.$height.$background.$border.$color.$padding.$shadow.$customstyles;

                /* Box Trigger Attribute */
                $trigger = $box->triggermethod;
                if ($box->triggermethod == "pageheight") { $trigger .= ":".$box->params->triggerpercentage; }
                if ($box->triggermethod == "element") { $trigger .= ":".$box->params->triggerelement; }
                $box->trigger_prepared = $trigger;

                /* Test Mode & Root Check */
                $box->isroot = $isRoot;
            }
        }

        return $boxes;
    }

    public static function vld($prm) {
        if ((isset($prm)) && (!is_null($prm)) && (!empty($prm))) {
            return true;
        }
    }

    public static function searchFields($data, $find) {
        $arr = new stdClass;
        foreach ($data as $key => $value) {
            $pos = strpos($key, $find);
            if ($pos !== false) {
                $key_new = str_replace($find, "", $key);
                $arr->$key_new = $value;
            }
        }

        return $arr;
    }

    public static function msg($str) {
        JError::raiseNotice(403, $str);
    }

    public static function getMenuItems($boxid) {
        $data = self::fetch("rstbox_menu", "menuid", "boxid=$boxid");

        $menuitems = array();

        foreach ($data as $menuitem) {
            array_push($menuitems, $menuitem->menuid);
        }
        return $menuitems;
    }

    public static function pretty($str) {
        $a = $str;
        $a = ucfirst(str_replace("_", " ", $a));
        return $a;
    }

    public static function loadassets() {
        $params = JComponentHelper::getParams('com_rstbox');
        $document = JFactory::getDocument();
        $back_dir = JURI::root(true)."/administrator/".self::$assdir;

        $document->addStyleSheet($back_dir.'css/styles.css?v='.self::getVer());

        if (self::jVer() == "2.5") {
            $document->addStyleSheet($back_dir.'css/styles.j25.css?v='.self::getVer());

            if ($params->get('jqueryback', 1)) {
                $document->addScript('//code.jquery.com/jquery-latest.min.js');
            }
        }

        $document->addScript($back_dir.'js/scripts.js?v='.self::getVer());
    }

    public static function loadassets_front() {
        if (self::jVer() == "3") {
            JHtml::_('jquery.framework');
        }

        $params = JComponentHelper::getParams('com_rstbox');
        $document = JFactory::getDocument();
        $front_dir = JURI::root(true)."/".self::$assdir;

        $document->addStyleSheet($front_dir.'css/rstbox.css?v='.self::getVer());

        if (self::jVer() == "2.5") {
            if ($params->get('jquery', 1)) { $document->addScript('//code.jquery.com/jquery-latest.min.js'); }
        }

        $document->addScript($front_dir.'js/rstbox.js?v='.self::getVer());
    }

    public static function runquery($q) {
        if (isset($q)) {
            $db = JFactory::getDBO();
            $db->setQuery($q);
            return $db->loadObjectList();
        }
    }

    public static function fetch($table, $columns = "*", $where = null, $singlerow = false) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select($columns)
            ->from("#__$table");
        
        if (isset($where)) {
            $query->where("$where");
        }
        
        $db->setQuery($query);
 
        return ($singlerow) ? $db->loadObject() : $db->loadObjectList();
    }

    public static function del($table, $where) {
        if ((isset($table)) && (isset($where))) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->delete($db->quoteName("#__$table"))
                ->where($where);
            $db->setQuery($query);

            $result = $db->query();
            return  $result;
        }
    }
}