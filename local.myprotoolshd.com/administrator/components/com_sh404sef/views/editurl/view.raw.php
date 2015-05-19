<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2015
 * @package     sh404SEF
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     4.4.9.2487
 * @date		2015-04-20
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

jimport( 'joomla.application.component.view');

class Sh404sefViewEditurl extends ShlMvcView_Base {

  public function display( $tpl = null) {

    // declare docoument mime type
    $document = JFactory::getDocument();
    $document->setMimeEncoding( 'text/xml');

    // call helper to prepare response xml file content
    $response = Sh404sefHelperGeneral::prepareAjaxResponse( $this);

    // echo it
    echo $response;
     
  }
}
