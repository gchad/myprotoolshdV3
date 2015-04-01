<?php
/**
 * ------------------------------------------------------------------------
 * JA Promo Bar module
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldServerinfo extends JFormField
{

	protected function getInput() {
		$func = (string) $this->element['function'];

		if($func && method_exists($this, $func)) {
			return call_user_func_array(array($this, $func), array());
		}
		return null;
	}

	protected function datetime() {
		$date = JFactory::getDate();
		$format = $this->element['function'];
		if($format) $format = 'Y-m-d H:i:s';
		return $date->format($format, false);
	}

	protected function datetime_utc() {
		$format = $this->element['function'];
		if($format) $format = 'Y-m-d H:i:s';
		return gmdate($format);
	}

	protected function datetimelocal() {
		$html = "
		<script type=\"text/javascript\">
		var cd = new Date();
		var offset = -cd.getTimezoneOffset()/60;
		var datestr = cd.getFullYear() + '-' +(cd.getMonth()+1)+'-'+cd.getDate();
		datestr += ' ' + cd.getHours()+':'+cd.getMinutes()+':'+cd.getSeconds();
		datestr += ' UTC' + (offset >=0 ? '+' + offset : offset);
		document.write(datestr);
		</script>
		";
		return $html;
	}
}