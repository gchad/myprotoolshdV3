<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of Responsive Scroll Triggered Box component
 */
class com_RstboxInstallerScript
{

    function preflight($type, $parent) 
    {

        /* Run Schema Fix only on Joomla 2.5 */
        if (version_compare(JVERSION, '3.0', '<')) {
            if ($type == "update") {

                $app = JFactory::getApplication();
                $app->enqueueMessage("Running Schema Fixes");

                $com = $this->getComponent();
                $comManifest = json_decode($com["1"]);
                $extensionId = $com["0"];
                $extensionVersion = $comManifest->version;

                $this->schemaFix($extensionId, $extensionVersion);
            }
        }

        return true;
    }

    function schemaFix($extension_id, $version_id) {
        //$app = JFactory::getApplication();
        $schemaCheck = $this->schemaIsOK($extensionId, $extensionVersion);

        //$app->enqueueMessage($extensionId . " : " . $extensionVersion . " : " . $schemaCheck);

        if (!$schemaCheck) {

            $this->schemaDelete($extension_id);

            $profile = new stdClass();
            $profile->extension_id=$extension_id;
            $profile->version_id=$version_id;
            JFactory::getDbo()->insertObject('#__schemas', $profile);  

            if ($this->schemaIsOK($extensionId, $extensionVersion)) {
                //$app->enqueueMessage("Schema Fixed to $extensionVersion");
            }
        }
    }

    function schemaDelete($extension_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('extension_id') .'='. $extension_id);
        $query->delete($db->quoteName('#__schemas'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();
    }

    function schemaIsOK($extension_id, $version_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = '. $extension_id)
            ->where($db->quoteName('version_id') . ' = '. $db->quote($version_id));
        
        $db->setQuery($query);
        return $db->loadResult();       
    }

    function getComponent() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select($db->quoteName(array("extension_id","manifest_cache")))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = '. $db->quote('com_rstbox'));
        
        $db->setQuery($query);
        return $db->loadRow();
    }
}
?>
