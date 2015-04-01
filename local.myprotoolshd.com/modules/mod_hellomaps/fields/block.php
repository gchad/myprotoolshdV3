<?php
/**
 * @copyright	Copyright (C) 2014 JoomlaForceTeam. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once (dirname(__FILE__).'/text.php');

class JFormFieldHM_Block extends JFormField
{
	public $type = 'Block';
	private $params = null;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		
		JLoader::import( 'joomla.version' );
		$version = new JVersion();
		
		if (version_compare( $version->RELEASE, '2.5', '<=')) {
			return "";
		} else {
		
		$this->params = $this->element->attributes();
		$title = $this->get('label');
		$description = $this->get('description');
		$class = $this->get('class');
		$start = $this->get('start', 0);
		$end = $this->get('end', 0);

		$html = array();

		if ($start || !$end)
		{
			$html[] = '</div>';
			if (!(strpos($class, 'alert') === false))
			{
				$html[] = '<div class="alert ' . $class . '">';
			}
			else
			{
				$html[] = '<div class="form-horizontal well ' . $class . '" style=" margin-bottom:0px !important; padding:10px;">';
			}
			if ($title)
			{
				$title = HMText::html_entity_decoder(JText::_($title));
				$html[] = '<legend>' . $title . '</legend>';
			}
			if ($description)
			{
				// variables
				$v1 = JText::_($this->get('var1'));
				$v2 = JText::_($this->get('var2'));
				$v3 = JText::_($this->get('var3'));
				$v4 = JText::_($this->get('var4'));
				$v5 = JText::_($this->get('var5'));

				$description = HMText::html_entity_decoder(trim(JText::sprintf($description, $v1, $v2, $v3, $v4, $v5)));
				$description = str_replace('span', 'span class="hm_code"', $description);
				$html[] = '<div>' . $description . '</div>';
			}
			$html[] = '<div><div>';
		}
		if (!$start && !$end)
		{
			$html[] = '</div>';
		}

		return '</div>' . implode('', $html);
		
		}
	}

	

	private function get($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
