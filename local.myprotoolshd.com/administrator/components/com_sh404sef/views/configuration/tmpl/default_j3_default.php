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

defined('JPATH_BASE') or die;

?>

<div class="container-fluid">
<?php

foreach ($this->form->getFieldset($this->currentFieldset->name) as $field)
{
	$renderer = empty($field->element['shlrenderer']) ? 'default' : (string) $field->element['shlrenderer'];
	echo $this->layoutRenderer[$renderer]->render($field);
}

?>
</div>
