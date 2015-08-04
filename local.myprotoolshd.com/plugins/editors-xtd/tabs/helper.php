<?php
/**
 * Plugin Helper File
 *
 * @package         Tabs
 * @version         4.1.3
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 ** Plugin that places the button
 */
class plgButtonTabsHelper
{
	public function __construct(&$params)
	{
		$this->params = $params;
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of ( imageName, textToInsert )
	 */
	function render($name)
	{
		$button = new JObject;

		if (JFactory::getApplication()->isSite() && !$this->params->enable_frontend)
		{
			return $button;
		}

		require_once JPATH_PLUGINS . '/system/nnframework/helpers/functions.php';

		nnFrameworkFunctions::addScriptVersion(JURI::root(true) . '/media/nnframework/js/script.min.js');
		nnFrameworkFunctions::loadLanguage('plg_editors-xtd_tabs');

		JHtml::stylesheet('nnframework/style.min.css', false, true);

		$this->params->tag_open = preg_replace('#[^a-z0-9-_]#s', '', $this->params->tag_open);
		$this->params->tag_close = preg_replace('#[^a-z0-9-_]#s', '', $this->params->tag_close);
		$this->params->tag_delimiter = ($this->params->tag_delimiter == '=') ? '=' : ' ';

		$text = $this->getText();
		$text = str_replace('\\\\n', '\\n', addslashes($text));
		$text = str_replace('{', '{\'+\'', $text);
        
        /* GCHAD FIX */
		/*$js = "
			function insertTabs(editor) {
				selection = nnScripts.getEditorSelection(editor);
				selection = selection ? selection : '". addslashes(JText::_('TAB_TEXT'))."';

				text = '" . $text . "';
				text = text.replace('[:SELECTION:]', selection);

				jInsertEditorText(text, editor);
			}
		";*/
        
        $js = "
            function insertTabs(editor) {
                selection = nnScripts.getEditorSelection(editor);
                selection = selection ? selection : '". addslashes(JText::_('YOUR_TEXT_1'))."';

                text = '" . $text . "';
                text = text.replace('[:SELECTION:]', selection);

                jInsertEditorText(text, editor);
            }
        ";
		JFactory::getDocument()->addScriptDeclaration($js);

		$class = 'nonumber icon-tabs';

		$text_ini = strtoupper(str_replace(' ', '_', $this->params->button_text));
		$text = JText::_($text_ini);
		if ($text == $text_ini)
		{
			$text = JText::_($this->params->button_text);
		}

		$button->modal = false;
		$button->class = 'btn';
		$button->link = '#';
		$button->onclick = 'insertTabs(\'' . $name . '\');return false;';
		//$button->text = trim($text);
        
        $button->text = JText::_('INSERT_MULTILANG');
		$button->name = $class;

		return $button;
	}

	private function getText()
	{
		switch (true)
		{
			case ($this->params->button_use_custom_code && $this->params->button_custom_code):
				return $this->getCustomText();
			default:
				return $this->getDefaultText();
		}
	}

	private function getDefaultText()
	{
        /* GCHAD FIX */
		/*return
			'{' . $this->params->tag_open . $this->params->tag_delimiter . JText::_('TAB_TITLE') . ' 1}\n' .
			'<p>[:SELECTION:]</p>\n' .
			'<p>{' . $this->params->tag_open . $this->params->tag_delimiter . JText::_('TAB_TITLE') . ' 2}</p>\n' .
			'<p>' . JText::_('TAB_TEXT') . '</p>\n' .
			'<p>{/' . $this->params->tag_close . '}</p>';*/
			
        return
            '{' . $this->params->tag_open . $this->params->tag_delimiter . JText::_('TAB_TITLE_1') . '}\n' .
            '<p>[:SELECTION:]</p>\n' .
            '<p>{' . $this->params->tag_open . $this->params->tag_delimiter . JText::_('TAB_TITLE_2') . '}</p>\n' .
            '<p>' . JText::_('YOUR_TEXT_2') . '</p>\n' .
            '<p>{/' . $this->params->tag_close . '}</p>';
	}

	private function getCustomText()
	{
		$text = trim($this->params->button_custom_code);
		$text = str_replace(array("\r", "\n"), array('', '</p>\n<p>'), trim($text)) . '</p>';
		$text = preg_replace('#^(.*?)</p>#', '\1', $text);
		$text = str_replace(array('{tab ', '{/tabs}'), array('{' . $this->params->tag_open . $this->params->tag_delimiter, '{/' . $this->params->tag_close . '}'), trim($text));

		return $text;
	}
}
