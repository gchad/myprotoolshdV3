<?php
// No direct access to this file
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldModules extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'modules';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions() 
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__modules');
        $query->where('published=1'); 
        $query->where('access !=3'); 
        $query->order('title'); 
        $rows = $db->setQuery($query);              
        $results = $db->loadObjectList();

        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }

        $options = array();

        foreach ($results as $option) {
            $options[] = JHTML::_('select.option', $option->id, $option->title);
        }

        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}