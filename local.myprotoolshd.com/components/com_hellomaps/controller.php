<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class HelloMapsController extends JControllerLegacy
{
    /**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName	 = JRequest::getCmd('view', 'hellomaps');
		$vFormat = $document->getType();
		$lName	 = JRequest::getCmd('layout', 'default');

		if ($view = $this->getView($vName, $vFormat)) {
			// Do any specific processing by view.
            //load the jquery if needed
            $load_jquery = HelloMapsHelper::GetConfiguration('load_jquery',0);
            
            if($load_jquery)
            {
                $document->addScript('http://code.jquery.com/jquery-latest.min.js');
            }
            $document->addScript(JURI::base().'components/com_hellomaps/assets/js/hquery-2.1.1.js');
            //$document->addScriptDeclaration("var hQuery = jQuery.noConflict( true );\n");            
			switch ($vName) {				
				default:
					$model = $this->getModel($vName);
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
    public function get_user()
    {
	
		$document =& JFactory::getDocument();
		// Set the MIME type for JSON output.
		$document->setMimeEncoding( 'application/json' );
		// Change the suggested filename.
		JResponse::setHeader( 'Content-Disposition',
		'attachment; filename="'.$this->getName().'.json"' );

		$data = JRequest::get( 'username' );
		
        // Get the model.
        $model = $this->getModel('main');
        // Get the data from the model.
        $data = $model->getUser($data);
        // Check for errors.
        if ($model->getError()) {
            // Do something.
        }
        // Echo the data as JSON.
        echo json_encode($data);
       
        
    }
	
	public function suggest_address()
    {
	
		$document =& JFactory::getDocument();
		$document->setMimeEncoding( 'application/json' );
		JResponse::setHeader( 'Content-Disposition',
		'attachment; filename="'.$this->getName().'.json"' );
		$add = JRequest::get( 'address' );
        $model = $this->getModel('main');
        $add = $model->suggestaddress($add);
        
		echo json_encode($add);
    }
    
    /**
     * for freaking xipt, i need to use task only under main controller
    */
    public function marker_search()
    {
        if(!class_exists('HellomapsControllerPlugin_manager'))
        {
            require_once(JPATH_COMPONENT.'/controllers/plugin_manager.php');
            $pluginManager = new HellomapsControllerPlugin_manager();
            $pluginManager->search();
        }
    }
} 

?>