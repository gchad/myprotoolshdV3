<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_jak2filter/models/model.php');
require_once(JPATH_SITE.'/components/com_jak2filter/models/itemlist.php');

/**
 * @package		Joomla.Site
 * @subpackage	mod_jak2filter
 * @since		1.5
 */
class modJak2filterHelper
{
	const COUNT_ITEMS_TXT = ' (%s)';
	protected $module = 0;
	protected $params = null;
	protected $comParams = null;
	protected $display_counter = 0;
	protected $update_counter = 0;
	protected $disable_option_empty = 0;
		
	protected $enable_reset_button = 0;

	public $activeCats = array();
	
	public function __construct($module) {
		$this->module = $module;
		$this->params = new JRegistry($module->params);
		$this->comParams = JComponentHelper::getParams('com_jak2filter');

		$this->display_counter		= (int) $this->params->get('display_counter');
		$this->update_counter		= (int) $this->params->get('update_counter');
		$this->disable_option_empty		= (int) $this->params->get('disable_option_empty');
		$ajax_filter = (int) $this->params->get('ajax_filter', 0);
		if($ajax_filter) {
			$this->update_counter = 0;
			$this->disable_option_empty = 0;
		}
		
		$this->enable_reset_button		= (int) $this->params->get('enable_reset_button');
		
		$this->update_counter &= $this->display_counter;//only enable if display_counter option is enabled
		if(!$this->display_counter) {
			$this->disable_option_empty = 0;
		}

		$mode = $this->params->get('form_mode', 'normal');
		if($mode == 'dynamic') {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			if(!$active) {
				$Itemid = JRequest::getInt('Itemid', 0);
				if($Itemid) {
					$active = $menu->getItem($Itemid);
				}
			}
			if($active) {
				$uri = JURI::getInstance($active->link);
				$option = $uri->getVar('option');
				if($option == 'com_k2') {
					$categories = $active->params->get('categories', array());
					if(count($categories)) {
						$catCatalogMode = $active->params->get('catCatalogMode', 1);
						if($catCatalogMode) {
							$helper = new JAK2FilterModelItemlist();
							$categories = $helper->getCategoryTree($categories);
						}

						$cat_id = JRequest::getVar('category_id',0);
						if(!$cat_id) {
							JRequest::setVar('category_id', implode(',', $categories));
						}
					}

					$this->activeCats = $categories;
				} elseif ($option == 'com_jak2filter') {
					$categories = $uri->getVar('category_id', 0);
					if($categories) {
						$isc = $uri->getVar('isc', 0);
						if($isc) {
							$helper = new JAK2FilterModelItemlist();
							$categories = $helper->getCategoryTree($categories);
						}
						$this->activeCats = $categories;
					}
				}
			}
		}
	}
	
	/**
	 * Get a list of the K2 Extra Fields.
	 *
	 * @param	JRegistry	$params	The module options.
	 *
	 * @return	array
	 * @since	1.5
	 */
	public function getList($fields, $fields_type)
	{				
		$items = array();
		$ja_stylesheet = $this->params->get('ja_stylesheet');

		if(is_array($fields) && count($fields)>0)
		{
			$xgroup_order = $this->params->get('xgroup_order', array());
			$gindex = 0;

			if(count($this->activeCats)) {
				$activeGroups = $this->getextraFieldsGroupsByCat($this->activeCats);
			}

			foreach ($fields_type as $index => $value)
			{
				$field = explode(":", $value);
				if(count($field)>1)
				{
					$fieldId = $field[0];
					$fieldType = $field[1];
					$fn = ucfirst(strtolower($fieldType));
					$func = 'get'.$fn;
					
					if(!in_array($fieldId, $fields) || !method_exists($this, $func)) continue;

					$row = $this->getExtraField($fieldId);
					if(!$row) continue;
					$group = $row->group;
					if(isset($activeGroups) && !in_array($group, $activeGroups)) {
						//dynamically get extra fields
						continue;
					}
					if(!isset($items[$group])) {
						$order = isset($xgroup_order[$gindex]) ? $xgroup_order[$gindex] : $gindex;
						$gindex++;
						$items[$group] = array('groupid' => $group, 'group' => $row->group_name, 'order' => $order, 'items' => array());
					}

					$fieldname = 'xf_'.$row->id;
					$row->jatype = 'xfield';
					$row->ff_type = $fieldType;//form field type
					$row->index = $index;
					$html = $this->getLabel($row->ff_type, $fieldname, $row->name, $row->group);
					$html .= call_user_func_array(array($this, $func), array($fieldname, $row));
					
					$items[$group]['items'][$fieldType.'_'.$fieldId] = $html;
				}
			}
		}
		if(count($items) > 1) {
			//sort group
			usort($items, array($this, 'groupcmp'));
		}

		if($ja_stylesheet == 'vertical-layout' && count($items) > 1) {
			//include asset
			if(!defined('JAK2FILTER_ASSET_ACCORDION')) {
				define('JAK2FILTER_ASSET_ACCORDION', 1);
				$doc = JFactory::getDocument();
				$basepath = JURI::root(true).'/modules/' . $this->module->module . '/assets/';
				JHTML::_('JABehavior.jquery');
				JHTML::_('JABehavior.jqueryui');
				$doc->addStyleSheet($basepath.'jquery/jquery.ui.css');
				$doc->addScript($basepath.'jquery/jquery.ui.accordion.js');
			}
		}
		
		return $items;
	}
	/*
	 * Get extra field from k2
	 * */
	public function getExtraField($id){
		$db	=	JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("ef.`id`, ef.`name`, ef.`value`, ef.`type`, ef.`group`, efg.`name` AS group_name")
			->from("#__k2_extra_fields AS ef")
			->leftJoin("#__k2_extra_fields_groups AS efg ON efg.id = ef.group")
			->where("ef.id = ".$db->quote($id));
		$db->setQuery($query);
		$rows	=	$db->loadObject();
		return $rows;
	}
	
	/*
	 * Return html of field
	 * */
	public function getTextfield($fieldname, $field){
		$input = JFactory::getApplication()->input;

		$selected_values = $input->get($fieldname."_txt",'', 'STRING');
		
		$values = json_decode($field->value);
		if($values && $this->params->get('auto_complete', 0)){
			JHtml::script('com_finder/autocompleter.js', false, true);
		}
		$html = '';
		foreach ($values as $f)
        {
           	if($selected_values){
           		$f->value = $selected_values;
           	} else {
				//do not use default value for search form
				$f->value = '';
			}
           	$id = "auto_".$this->module->id."_".$fieldname."_txt";

        	$html .= "\n\t<input type=\"text\" class=\"exfield exgroup{$field->group}\" name=\"{$fieldname}_txt\" id=\"".$id."\" value=\"{$f->value}\" placeholder=\"{$field->name}\" />";
			if($this->params->get('auto_complete', 0)==1){
				//$url = JRoute::_('index.php?option=com_jak2filter&view=suggestions&xf_id='.$field->id.'&tmpl=component', false);
				$url = 'index.php?option=com_jak2filter&view=suggestions&m_id='.$this->module->id.'&xf_id='.$field->id.'&tmpl=component';
				$html .= '<script type="text/javascript">
							var completer_' . $this->module->id . '_' . $fieldname.' = new Autocompleter.Request.JSON(
								document.id(\''.$id.'\'), 
								\''.$url.'\', 
								{\'minLength\': 3, \'postVar\': \''.$id.'\'}
							); ';
							
				if($this->params->get('auto_filter', 0)){
					$html .= '$(\''.$id.'\').addEvent(\'keyup\', function(event){
									if (event.key == \'enter\'){
										$(\'jak2filter-form-'.$this->module->id.'\').fireEvent(\'submit\');
									}
								});	';
				}
				$html .= '</script>';				
			}
        }
        
        return $html;
	}
	
	protected function getExtraFieldParam($param, $index) {
		$vals = $this->params->get($param);
		$val = isset($vals[$index]) ? $vals[$index] : null;
		return $val;
	}

    protected function jak2filterNumberFormat($number, $field){
        $decimals = $this->getExtraFieldParam('range_slider_format_decimals', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimals', $field->index) : 0;
        $dec_point = $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) : '.';
        $thousands_sef = $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) ? $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) : ',';
        $number_txt = '';
        $number_txt .= $this->getExtraFieldParam('range_slider_format_prefix', $field->index);
        if($this->getExtraFieldParam('range_slider_format', $field->index)){
            $number_txt .= number_format($number, $decimals, $dec_point, $thousands_sef);
        }else{
            $number_txt .= $number;
        }
        $number_txt .= $this->getExtraFieldParam('range_slider_format_suffix', $field->index);
        return $number_txt;
    }

	public function getValuerange($fieldname, $field){
		$input = JFactory::getApplication()->input;
		
		$selected_values = $input->get($fieldname."_range", null, 'string');
		
		$field->value = json_decode($field->value);
		
		$ranges = $this->getExtraFieldParam('filter_by_fieldrange', $field->index);
		
		if(isset($ranges)){
			$ranges = explode("|", $ranges);
			$ranges = array_filter($ranges);
			sort($ranges,SORT_NUMERIC);
			$options[] = JHTML::_('select.option', 0, JText::sprintf('JAK2_SELECT_OPTION', $field->name));
		
			for($i = 0; $i<count($ranges);$i++){
				if($i==0){
					$options[] = JHTML::_('select.option','|'.$ranges[$i],JText::_('LESS_THAN').' '. $this->jak2filterNumberFormat($ranges[$i], $field) );
				}
				else if($i==(count($ranges)-1)){
					$options[] = JHTML::_('select.option',$ranges[$i-1].'|'.$ranges[$i],$this->jak2filterNumberFormat($ranges[$i-1], $field).JText::_('JA_K2FILTER_TO').$this->jak2filterNumberFormat($ranges[$i], $field));
					$options[] = JHTML::_('select.option',$ranges[$i].'|',JText::_('MORE_THAN').' '.$this->jak2filterNumberFormat($ranges[$i], $field) );
				}
				else{
					$options[] = JHTML::_('select.option',$ranges[$i-1].'|'.$ranges[$i],$this->jak2filterNumberFormat($ranges[$i-1], $field).JText::_('JA_K2FILTER_TO').$this->jak2filterNumberFormat($ranges[$i], $field));
				}
				
			}
			
        	return JHTML::_('select.genericlist', $options, $fieldname.'_range', array('class'=>'exfield exgroup'.$field->group), 'value', 'text', $selected_values);
		}
		return '';
	}
	
	public function getLabelRangeSlider($fieldname, $fieldtitle, $group = 0) {
     	$id = 'presenter_'.$this->module->id.'_'.$fieldname.'_range';
		$label = "\n\t<label class=\"group-label\">".$fieldtitle;
     	$label .= '<span class="presenter" id="'.$id.'" ></span>';
     	$label .= '<div class="clearfix"></div>';
     	$label .= '</label>';
		return $label;
	}
	
	public function getRangeSlider($fieldname, $field){
		//include asset
		if(!defined('JAK2FILTER_ASSET_RANGESLIDER')) {
			define('JAK2FILTER_ASSET_RANGESLIDER', 1);
			$doc = JFactory::getDocument();
			$basepath = JURI::root(true).'/modules/' . $this->module->module . '/assets/';
			JHTML::_('JABehavior.jquery');
			JHTML::_('JABehavior.jqueryui');
			$doc->addStyleSheet($basepath.'jquery/jquery.ui.css');
			$doc->addScript($basepath.'jquery/jquery.ui.slider.js');
			$doc->addScript($basepath.'jquery/jquery.number.min.js');
		}
		//
		if($field->jatype != 'xfield') {
			$name = $fieldname;

			$attrs = $field->attrs;
			$min = $attrs['min'];
			$max = $attrs['max'];
			$start = $attrs['start'];
			$stop = $attrs['stop'];
			$step = $attrs['step'];
		} else {
			$name = $fieldname.'_range';
			$min = (float) $this->getExtraFieldParam('rangeslider_min', $field->index);
			$max = (float) $this->getExtraFieldParam('rangeslider_max', $field->index);
			$start = (float) $this->getExtraFieldParam('rangeslider_start', $field->index);
			$stop = (float) $this->getExtraFieldParam('rangeslider_stop', $field->index);
			$step = (float) $this->getExtraFieldParam('rangeslider_step', $field->index);
            $prefix = $this->getExtraFieldParam('range_slider_format_prefix', $field->index);
            $suffix = $this->getExtraFieldParam('range_slider_format_suffix', $field->index);
            $decimals = $this->getExtraFieldParam('range_slider_format_decimals', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimals', $field->index) : 0;
            $dec_point = $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) : '.';
            $thousands_sef = $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) ? $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) : ',';
		}
		$id = $this->module->id.'_'.$name;
		$idSlider = 'slider_'.$id;
		$idPresentor = 'presenter_'.$id;
		$idRange = 'rating_range_'.$this->module->id;
		$value = JRequest::getVar($name,'');
		
		//$field->value = json_decode($field->value);
		
		if(!empty($value)) {
			$values = explode('|', $value);
			if(!empty($values[0])) $start = (int) $values[0];
			if(isset($values[1])) $stop = (int) $values[1];
		}
		
		$auto_filter		= (int) $this->params->get('auto_filter');
		$formid 			= 'jak2filter-form-'.$this->module->id;
		$instantSearch = '';
		if($auto_filter) {
			$instantSearch = ',
			stop: function (event, ui) {
				$("#'.$formid.'").trigger(\'submit\');
			}';
		}
		
		if($name == 'rating') {
			$presentor = '$( "#'.$idPresentor.'" ).css( {left: ((ui.values[ 0 ] - 1) * 20)+"%" , 
			width: ((ui.values[ 1 ]-ui.values[ 0 ] + 1) * 20)+"%"} );';
			$presentor .= '
			$("#'.$idRange.'").find("span.srange").removeClass("active");
			if (ui.values[0] == ui.values[1]) {
				$( "#'.$idPresentor.'_note" ).html("(" + ui.values[ 0 ] + " Stars)");
			} else {
				$( "#'.$idPresentor.'_note" ).html("(" + ui.values[ 0 ] + " - " + ui.values[ 1 ] + " Stars)");
			}
			if(ui.values[0] >= 2) {
				$("#'.$idRange.'").find("span[rel=\'"+(ui.values[0]-1)+"-stars\']").addClass("active");
			}
			';
			$presentorFirst = '$( "#'.$idPresentor.'" ).css( {left: (($( "#'.$idSlider.'" ).slider( "values", 0 ) - 1) * 20)+"%", 
			width: (($( "#'.$idSlider.'" ).slider( "values", 1 ) - $( "#'.$idSlider.'" ).slider( "values", 0 ) + 1) * 20)+"%"} );';
			$presentorFirst .= '
			if ($( "#'.$idSlider.'" ).slider( "values", 0 ) == $( "#'.$idSlider.'" ).slider( "values", 1 )) {
				$( "#'.$idPresentor.'_note" ).html("(" + $( "#'.$idSlider.'" ).slider( "values", 1 ) + " Stars)");
			} else {
				$( "#'.$idPresentor.'_note" ).html("(" + $( "#'.$idSlider.'" ).slider( "values", 0 ) + " - " + $( "#'.$idSlider.'" ).slider( "values", 1 ) + " Stars)");
			}
			if($( "#'.$idSlider.'" ).slider( "values", 0 ) >= 2) {
				$("#'.$idRange.'").find("span[rel=\'"+($( "#'.$idSlider.'" ).slider( "values", 0 )-1)+"-stars\']").addClass("active");
			}';
			
		} else {
            if($this->getExtraFieldParam('range_slider_format', $field->index)){
                $presentor = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $.number(ui.values[ 0 ], '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" + " - " + "' . $prefix . '" +$.number(ui.values[ 1 ], '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" );';
                $presentorFirst = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $.number($( "#'.$idSlider.'" ).slider( "values", 0 ), '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '"+ " - " + "' . $prefix . '" +$.number($( "#'.$idSlider.'" ).slider( "values", 1 ), '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" );';
            }else{
                $presentor = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + ui.values[ 0 ] + " - " + ui.values[ 1 ] + "' . $suffix . '" );';
                $presentorFirst = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $( "#'.$idSlider.'" ).slider( "values", 0 ) + "' . $suffix . '" + " - " + "' . $prefix . '" +$( "#'.$idSlider.'" ).slider( "values", 1 ) + "' . $suffix . '" );';
            }
		}
		
		$html = '
		<script type="text/javascript">
		(function($) {
			$(document).ready(function(){
				$( "#'.$idSlider.'" ).slider({
					range: true,
					min: '.$min.',
					max: '.$max.',
					step: '.$step.',
					values: [ '.$start.', '.$stop.' ],
					slide: function( event, ui ) {
						$( "#'.$id.'" ).val( ui.values[ 0 ] + "|" + ui.values[ 1 ] );
						'.$presentor.'
					},
					change: function( event, ui ) {
						$( "#'.$id.'" ).val( ui.values[ 0 ] + "|" + ui.values[ 1 ] );
						'.$presentor.'
					}
					'.$instantSearch.'
				});
				$( "#'.$id.'" ).val( $( "#'.$idSlider.'" ).slider( "values", 0 ) + "|" + $( "#'.$idSlider.'" ).slider( "values", 1 ) );
				'.$presentorFirst.'
			});
		})(jQuery);
		</script>
		<div id="'.$idSlider.'"></div>
		<input type="hidden" name="'.$name.'" id="'.$id.'" value="" />
		<input type="hidden" name="'.$name.'_jacheck" id="'.$id.'_jacheck" value="'.$min.'|'.$max.'" />';
		return $html;
	}
	
	public function getRatings() {
		$attrs = array(
			'min' => 1,
			'max' => 5,
			'start' => 1,
			'stop' => 5,
			'step' => 1
		);
		$field = $this->createFieldObject('rating', 'rating', 'rangeslider', '[]', $attrs);
		return $this->getRangeSlider('rating', $field);
	}

	public function getXFieldDatatype($field, $default = 'string') {
		static $dt = array();
		if(!count($dt)) {
			$xfDataType = $this->comParams->get('extra_fields_data_type', array());
			if(is_array($xfDataType) && count($xfDataType)) {
				foreach($xfDataType as $val) {
					@list($xfid, $type) = explode(':', $val);
					$dt[$xfid] = $type;
				}
			}
		}

		$type = isset($dt[$field->id]) ? $dt[$field->id] : $default;
		return $type;
	}

	private function getXFieldValues($field)
	{
		$sortMode = $this->comParams->get('extra_fields_sort_mode', array());
		$sort = 'alpha';
		if(count($sortMode)) {
			foreach($sortMode as $sm) {
				if(strpos($sm, $field->id.':') === 0) {
					$sort = str_replace($field->id.':', '', $sm);
					break;
				}
			}
		}
		$datatype = $this->getXFieldDatatype($field);
		if($field->type == 'labels') {
			$db = JFactory::getDbo();
			$direction = ($sort == 'ralpha') ? 'DESC' : 'ASC';
			$query = "SELECT t.labels AS name, t.labels AS `value`, t.`num_items` AS `num_items`
					FROM #__jak2filter_taxonomy t, #__k2_extra_fields e
					WHERE t.labels <> ''
					AND t.asset_id = e.id
					AND t.`asset_id` = " . $field->id . "
					AND e.`group` = " . $field->group . "
					AND num_items > 0
					ORDER BY t.labels ".$direction;
			$db->setQuery($query);
			$values = $db->loadObjectList();
		} else {
			$values = json_decode($field->value);
			if($datatype == 'number') {
				if($sort == 'alpha') {
					usort($values, array($this, 'numbecmp'));
				} elseif ($sort == 'ralpha') {
					usort($values, array($this, 'numbercmp'));
				}
			} else {
				if($sort == 'alpha') {
					usort($values, array($this, 'strcmp'));
				} elseif ($sort == 'ralpha') {
					usort($values, array($this, 'strrcmp'));
				}
			}
		}

		foreach ($values as $id => &$f)
		{
			$f->name = JText::_($f->name);
			if ($field->type == 'labels') {
				if(!$this->display_counter) {
					$num_items = '';
				} elseif($this->update_counter) {
					$num_items = $this->getNumItems('xfield', $field->id, 0, $f->value);
				} else {
					$num_items = $f->num_items;
				}
			} else {
				if(empty($f->name)) {
					unset($values[$id]);
					continue;
				}
				$num_items = $this->getNumItems('xfield', $field->id, $f->value);
			}
			$this->bindListData($f, $num_items);
		}
		//@sort( $values, SORT_REGULAR );
		//var_dump($values);
		return $values;
	}

	private function bindListData(&$f, $num_items) {
		$f->disabled = ($this->disable_option_empty && !$num_items) ? true : false;
		$f->num_items = $num_items;
		$f->num_items_txt = (!empty($num_items)) ? sprintf(self::COUNT_ITEMS_TXT, $num_items) : '';
	}

	public function numbecmp($a, $b) {
		return (float) $a->name > (float) $b->name;
	}

	public function numbercmp($a, $b) {
		return (float) $a->name < (float) $b->name;
	}

	public function strcmp($a, $b) {
		return strcmp($a->name, $b->name);
	}

	public function strrcmp($a, $b) {
		return strcmp($b->name, $a->name);
	}

	public function groupcmp($a, $b) {
		return $a['order'] > $b['order'];
	}
	
	public function getCheckbox($fieldname, $field) {
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}

       	$html = '';
		foreach ($values as $k => $f)
        {
           	$checked = '';
        	if(in_array($f->value, $selected_values)){
        		$checked = 'checked="checked"';
        	}
			$disabled = $f->disabled ? 'disabled="disabled"' : '';

			if ($this->disable_option_empty != 2 || empty($disabled)) {
				$html .= "\n\t<input type=\"checkbox\" class=\"exfield exgroup{$field->group}\" name=\"{$fieldname}[]\" id=\"{$fieldname}_{$k}\" value=\"{$f->value}\" {$checked} {$disabled}/>";
         		$html .= "\n\t<label class=\"lb-checkbox\" for=\"{$fieldname}_{$k}\">{$f->name}{$f->num_items_txt}</label>";
			}
        }
        return $html;
		
	}
	
	public function getSelect($fieldname, $field){
		$input = JFactory::getApplication()->input;

		$selected_values = $input->get($fieldname, null);
		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
			$attrs = array('class'=>'exfield exgroup'.$field->group);
		} else {
			$values = $field->value;
			$attrs = isset($field->attrs) ? $field->attrs : array();
			sort( $values, SORT_REGULAR );
		}
		$html[] = JHTML::_('select.option', 0, JText::sprintf('JAK2_SELECT_OPTION', $field->name));
		foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$html[] = JHTML::_('select.option', $f->value, $f->name . $f->num_items_txt, 'value', 'text', $f->disabled);
			}
        }
		
        return JHTML::_('select.genericlist', $html, $fieldname, $attrs, 'value', 'text', $selected_values);
	}
	
	public function getMultipleSelect($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
			$attrs = array('class'=>'exfield multiple exgroup'.$field->group);
		} else {
			$values = $field->value;
			$attrs = isset($field->attrs) ? $field->attrs : array();
		}
		$attrs['multiple'] = 'multiple';
     	$options = array();
        foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$options[] = JHTML::_('select.option', $f->value, $f->name . $f->num_items_txt, 'value', 'text', $f->disabled);
			}
        }
	
        return JHTML::_('select.genericlist', $options, $fieldname.'[]', $attrs, 'value', 'text', $selected_values);
	}

	public function getRadioKeywords($selected='') {
        $options = array();
		$options[] = JHtml::_('select.option', 'exact', JText::_('MOD_JA_K2_FILTER_KEYWORD_EXACT'), 'value', 'text');
		$options[] = JHtml::_('select.option', 'all', JText::_('MOD_JA_K2_FILTER_KEYWORD_ALL'), 'value', 'text');
		$options[] = JHtml::_('select.option', 'any', JText::_('MOD_JA_K2_FILTER_KEYWORD_ANY'), 'value', 'text');

		$selected = $selected ? $selected : JRequest::getVar("st", $this->params->get('keyword_default_mode', 'exact'));
	
        return JHtml::_('select.radiolist', $options, 'st', array(), 'value', 'text', $selected);
	}

	public function getLabelMagicSelect($fieldname, $fieldtitle, $group = 0){
		$buttonid = 'g-'.$this->module->id.'-'.$fieldname;
		$listid = 'mg-'.$this->module->id.'-'.$fieldname;

		$css = 'select closed exfield';
		if($group) $css .= ' exgroup'.$group;
		$txt = JText::_('JAADD').' '.$fieldtitle;
		$label = "\n\t<label class=\"group-label\">".$fieldtitle;
		$label .= '<button type="button" id="'.$buttonid.'" class="'.$css.'" href="#" onclick="jaMagicSelect(this, \''.$listid.'\'); return false;" title="'.addslashes($txt).'">'.$txt.'</button>';
		$label .= '</label>';
		return $label;
	}
	
	public function getMagicSelect($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}

		$auto_filter		= (int) @$this->params->get('auto_filter');
		$listid = 'mg-'.$this->module->id.'-'.$fieldname;
		$html = '<div class="ja-magic-select" id="'.$listid.'" data-autofilter="'.$auto_filter.'"><ul>';

		foreach ($values as $f)
		{
			$cls = '';
			if(is_array($selected_values) && in_array($f->value, $selected_values)) {
				$cls = 'selected';
			}
			$cls .= $f->disabled ? ' disabled' : ' active';

			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$html .= '<li rel="'.$f->value.'" class="'.$cls.'">'.$f->name . $f->num_items_txt.'</li>';
			}
		}
		$html .= '</ul>';
		$html .= '<span class="btn-close" onclick="jaMagicSelectClose(this, \''.$listid.'\'); return false;">Close</span>';
		$html .= '<span class="arrow">&nbsp;</span>';
		$html .= '</div>';
		$html .= '<div id="'.$listid.'-container" class="ja-magic-select-container"></div>';

		$html .= '
<script type="text/javascript">
/*<![CDATA[*/
window.addEvent("domready", function(){
	jaMagicInit(\''.$listid.'\', \''.$fieldname.'\');
});
/*]]>*/
</script>';

        return $html;
	}
	
	public function getRadio($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, null);

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}
        $options = array();
		foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$options[] = JHtml::_('select.option', $f->value, $f->name . $f->num_items_txt, 'value', 'text', $f->disabled);
			}
        }
		
        return JHtml::_('select.radiolist', $options, $fieldname, array('class'=>'exfield exgroup'.$field->group), 'value', 'text',$selected_values);
	}
	
	public function getDateField($name, $cssclass) {
		$input = JFactory::getApplication()->input;
		JHTML::_('behavior.calendar');
		$attrs = array('size' => '10', 'class'=>$cssclass);

		$auto_filter = (int) @$this->params->get('auto_filter');
		if($auto_filter) {
			$attrs['onchange'] = 'validateDateRange(this);';
		}
		
		$selected_values = $input->get($name, '');
		$html = JHtml::calendar($selected_values, $name, $name.'_'.$this->module->id, '%Y-%m-%d', $attrs);
		return $html;
	}
	
	public function getDate($fieldname, $field){
		JHTML::_('behavior.calendar');
		$field->value = json_decode($field->value);
		return '<p>'.$this->getDateField($fieldname, 'date exfield exgroup'.$field->group).'</p>';
	}

	public function getDaterange($fieldname, $field){
		JHTML::_('behavior.calendar');
		$field->value = json_decode($field->value);

		$datefrom = $this->getDateField($fieldname.'_from', 'date exfield exgroup'.$field->group);
		$dateto   = $this->getDateField($fieldname.'_to', 'date exfield exgroup'.$field->group);
		return '<p class="left"><label>'.JText::_('JA_K2_FROM').'</label>'.$datefrom.'</p>'
				.'<p class="left"><label>'.JText::_('JA_K2_TO').'</label>'.$dateto.'</p>';
	}
	
	public function getSearchDateCreate() {
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('SELECT_DATE_RANGE'));
		$options[] = JHtml::_('select.option', 'today', JText::_('TODAY'));
		$options[] = JHtml::_('select.option', '-1d', JText::_('YESTERDAY'));
		$options[] = JHtml::_('select.option', '-7d', JText::_('LAST_7_DAYS'));
		$options[] = JHtml::_('select.option', '-1m', JText::_('LAST_30_DAYS'));
		$options[] = JHtml::_('select.option', 'tw', JText::_('THIS_WEEK'));
		$options[] = JHtml::_('select.option', 'lw', JText::_('LAST_WEEK'));
		$options[] = JHtml::_('select.option', 'tm', JText::_('THIS_MONTH'));
		$options[] = JHtml::_('select.option', 'lm', JText::_('LAST_MONTH'));
		$options[] = JHtml::_('select.option', 'range', JText::_('CUSTOM_RANGE'));

		$input = JFactory::getApplication()->input;
		$dtrange = $input->get('dtrange', '');
		$displayRange = $dtrange == 'range' ? 'block' : 'none';

		$attrbs = array('onchange' => "jaK2ShowDaterange(this, '#ja-custom-daterange-{$this->module->id}');");
		$datefrom = $this->getDateField('sdate', 'date');
        $dateto = $this->getDateField('edate', 'date');
		$html = '<div>
				'.JHtml::_('select.genericlist', $options, 'dtrange', $attrbs, 'value', 'text', $dtrange).'
				</div>
				<div id="ja-custom-daterange-'.$this->module->id.'" style="display:'.$displayRange.';">
				<p class="left">
					<label>'.JText::_('JA_K2_FROM').'</label>'.$datefrom.'
				</p>
				<p class="left">
					<label>'.JText::_('JA_K2_TO').'</label>'.$dateto.'
				</p>
				</div>';
		return $html;
	}

	/**
	 * Get extra field groups
	 * @param $extrafields
	 * @return mixed
	 */
	public function getextraFieldsGroups($extrafields){
		$db = JFactory::getDbo();
		if(is_array($extrafields) && count($extrafields)>0){	
			$extrafields = implode(',', $extrafields);
		}
		$query = "SELECT `group` FROM #__k2_extra_fields WHERE `id` IN ($extrafields) GROUP BY `group`";
		
		$db->setQuery($query);
		$result = $db->loadColumn();
		return $result;
	}

	public function getextraFieldsGroupsByCat($categories){
		$db = JFactory::getDbo();
		if(is_array($categories) && count($categories)>0){
			$categories = implode(',', $categories);
		}
		$query = "SELECT `extraFieldsGroup` FROM #__k2_categories WHERE `id` IN ($categories) GROUP BY `extraFieldsGroup`";

		$db->setQuery($query);
		$result = $db->loadColumn();
		return $result;
	}

	/**
	 * Get parent category Associated "Extra Fields Group"
	 * @param null $groupcategories
	 * @param null $row
	 * @param bool $hideTrashed
	 * @param bool $hideUnpublished
	 * @return array|mixed
	 */
	public function categoriesTree($groupcategories=NULL,$row = NULL, $hideTrashed = false, $hideUnpublished = true)
    {

        $db = JFactory::getDBO();
        if (isset($row->id)) {
            $idCheck = ' AND id != '.( int )$row->id;
        } else {
            $idCheck = null;
        }
        if (!isset($row->parent)) {
            if (is_null($row)) {
                $row = new stdClass;
            }
            $row->parent = 0;
        }
        $query = "SELECT m.* FROM #__k2_categories m WHERE id > 0 {$idCheck}";

        if ($hideUnpublished) {
            $query .= " AND published=1 ";
        }

        if ($hideTrashed) {
            $query .= " AND trash=0 ";
        }
		if($groupcategories) {
			if(is_array($groupcategories)) {
				if(!in_array('0', $groupcategories)) {
					$query .= " AND id IN (".implode(',', $groupcategories).")";
				}
			}
		}
        $query .= " ORDER BY parent, ordering";
        
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems) {
            foreach ($mitems as $v) {
                if (K2_JVERSION != '15') {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        foreach ($list as $item) {
            $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);

            if ($item->trash)
                $item->treename .= ' [**'.JText::_('K2_TRASHED_CATEGORY').'**]';
            if (!$item->published)
                $item->treename .= ' [**'.JText::_('K2_UNPUBLISHED_CATEGORY').'**]';

            //$mitems[] = JHTML::_('select.option', $item->id, $item->treename);
			$num_items = $this->getNumItems('category', $item->id);
			//$disabled = ($this->disable_option_empty && !$num_items) ? true : false;
			$disabled = false;//DO NOT DISABLE CATEGORY ITEMS
        	$num_items = (!empty($num_items)) ? sprintf(self::COUNT_ITEMS_TXT, $num_items) : '';
			$mitems[] = JHTML::_('select.option', $item->id, $item->treename.$num_items,array('option.attr' => 'rel', 'disable' => $disabled, 'attr'=>array('rel' => $item->extraFieldsGroup)));
        }
        return $mitems;
    }

	/*
	 * Return category list
	 * */
	public function getCategories($groupcategories,$hidden = false){
		if(count($this->activeCats)) {
			//Dynamically select categories
			$groupcategories = $this->activeCats;
			//$hidden = true;
		}
		
		if(!$hidden){
			$cat_id = JRequest::getVar('category_id',0);
			//$categories_option[]=JHTML::_('select.option', 0, JText::_('SELECT_CATEGORY_FRONT'));
			
			if(is_array($groupcategories)){
				if(($key = array_search(0, $groupcategories)) !== false) {
					unset($groupcategories[$key]);
				}
				$categories_option[]=JHTML::_('select.option', implode(",",$groupcategories), JText::_('SELECT_CATEGORY_FRONT'));
			}else{
				$categories_option[]=JHTML::_('select.option', 0, JText::_('SELECT_CATEGORY_FRONT'));
			}
			
			$categories = $this->categoriesTree($groupcategories,NULL, true, true);
			
			if(count($categories)>1){
				$categories_options=@array_merge($categories_option, $categories);
			}else{
				$categories_options = $categories;
			}
			
			$attribs = array('option.attr' => 'rel', 'option.key' => 'value', 'option.text' => 'text', 
							'list.attr' => array('class' => 'inputbox', 'onchange'=>'jak2DisplayExtraFields('.$this->module->id.',this);'),
							'list.select' => $cat_id);
			
			//Important Note: do not add more parametter after $attribs, add them into $attribs
			$categories_html = JHTML::_('select.genericlist',  $categories_options, 'category_id', $attribs);
		} else {
			$cat_ids = is_array($groupcategories)?implode(',',$groupcategories):$groupcategories;
			$categories_html = '<input type="hidden" name="category_id" value="'.$cat_ids.'" />';
		}
		return $categories_html;
	}
	/**
	 * Hidden categories
	 * */
	/*
	 * Return author list selected
	 * */
	public function getAuthors($filter_author_display){
		$db    =JFactory::getDBO();
		$display = $filter_author_display == 'author_display_name' ? 'u.name' : 'u.username';
		$query = 'SELECT DISTINCT '.$display.' AS `name`, u.id AS `value`
		    FROM #__users AS u 
		    INNER JOIN #__k2_items AS i ON u.id = i.created_by
	        WHERE i.published = 1
	        ORDER BY u.id';
		
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		if(count($rows)>0){
			foreach ($rows as &$row) {
				$num_items = $this->getNumItems('author', $row->value);
				$this->bindListData($row, $num_items);
			}
		}

		$fftype = $this->params->get('filter_by_author_fieldtype', 'select');
		$field = $this->createFieldObject(JText::_('JAK2_AUTHOR'), 'author', $fftype, $rows, array('class'=>'inputbox'));

		$func = 'get'.ucfirst($fftype);
		return call_user_func_array(array($this, $func), array('created_by', $field));
	}
	/**
	 * Get Tags in categories
	 * */
	public function getTags(){
		$db = JFactory::getDbo();
		
		$cat_ids = $this->params->get('k2catsid',null);
		if(count($this->activeCats)) {
			//Dynamically get tags
			$cat_ids = $this->activeCats;
		}
		
		if($this->params->get('catMode', 0)) {
			$model = new JAK2FilterModelItemlist();
			$cat_ids = $model->getCategoryTree($cat_ids);
		}
		$cat_ids = is_array($cat_ids) ? implode(',',$cat_ids) : $cat_ids;
		
		$query ="SELECT t.id as value,t.name as name".
				" FROM #__k2_tags AS t".
				" LEFT JOIN #__k2_tags_xref AS tx ON t.id = tx.tagID";
		if ($cat_ids) {
			$query .= " LEFT JOIN #__k2_items as ki ON tx.itemID = ki.id";
			$query .= " WHERE ki.catid IN ($cat_ids) AND t.published=1";
		} else {
			$query .= " WHERE t.published=1";
		}
		$query .=" GROUP BY t.id";
		$db->setQuery( $query );
		
		$rows = $db->loadObjectList();

		if(count($rows)>0){
			foreach ($rows as &$row) {
				$num_items = $this->getNumItems('tag', $row->value);
				$this->bindListData($row, $num_items);
			}
		}

		 
		/*$tags_id = JRequest::getInt('tags_id',0);
		
		if(JRequest::getVar('tag') && JRequest::getVar('option')=='com_k2'){
			$query ='SELECT id'.
				    ' FROM #__k2_tags';
			$query .= ' WHERE name LIKE "%'.JRequest::getVar('tag').'%"';
			
			$db->setQuery( $query );
			
			$tags_id = $db->loadResult();
		}*/

		$fftype = $this->params->get('filter_by_tags_fieldtype', 'select');
		$field = $this->createFieldObject(JText::_('JAK2_TAGS'), 'tag', $fftype, $rows, array('class'=>'inputbox'));

		$func = 'get'.ucfirst($fftype);
		return call_user_func_array(array($this, $func), array('tags_id', $field));
				
	}
	
	public function getNumItems($type, $id, $optid = 0, $label = '') {
		static $data = null;
		if(!$this->display_counter) return '';
		
		$filter = '_all_';
		$byLabel = false;
		switch ($type) {
			case 'category':
				$filter = 'category_id';
				break;
			case 'author':
				$filter = 'created_by';
				break;
			case 'tag':
				$filter = 'tags_id';
				break;
			case 'xfield':
				$filter = 'xf_'.$id;
				if(!$optid) {
					$byLabel = true;
				}
				break;
		}
		
		if(!(is_array($data) && isset($data[$filter]))) {
			$data[$filter] = $this->getCounter(NULL, $filter, $byLabel);
		}
		if(is_array($data) && isset($data[$filter])) {
			if($byLabel) {
				$tkey = sprintf('%s_%d_%s', $type, $id, $label);
			} else {
				$tkey = sprintf('%s_%d_%d', $type, $id, $optid);
			}
			if(isset($data[$filter][$tkey])) {
				return $data[$filter][$tkey];
			}
		}
		return '';
	}
	
	public function getCounter($ordering = NULL, $filter = '', $byLabel = false) {
		static $sdata = null;
		static $sdataLabel = null;

		$user = JFactory::getUser();
        $aid = $user->get('aid');
        $db = JFactory::getDBO();
        $params = K2HelperUtilities::getParams('com_k2');
        $task = JRequest::getCmd('task');
        
		$model = new JAK2FilterModelItemlist();
        
		$groupcategories = $this->params->get('k2catsid',null);
		if($this->params->get('catMode', 0)) {
			$groupcategories = $model->getCategoryTree($groupcategories);
		}
		$filter_categories = is_array($groupcategories)?implode(',',$groupcategories):$groupcategories;
        
        //
        $where = '';
        if($this->update_counter) {
			$badchars = array('#', '>', '<', '\\');
	        $search = JString::trim(JString::str_ireplace($badchars, '', JRequest::getString('searchword', null)));
			$where = $model->prepareSearch($search, $filter, $filter_categories);
        }
		
		if(empty($where)) {
			if($byLabel) {
				if(is_null($sdataLabel)) {
					//cache data for the case of search condition is empty
					$query = "SELECT `num_items`, CONCAT_WS('_', `type`, `asset_id`, `labels`) AS tkey FROM #__jak2filter_taxonomy";
					$db->setQuery($query);
					$sdataLabel = $db->loadAssocList('tkey', 'num_items');
				}
				return $sdataLabel;
			} else {
				if(is_null($sdata)) {
					//cache data for the case of search condition is empty
					$query = "SELECT `num_items`, CONCAT_WS('_', `type`, `asset_id`, `option_id`) AS tkey FROM #__jak2filter_taxonomy";
					$db->setQuery($query);
					$sdata = $db->loadAssocList('tkey', 'num_items');
				}
				return $sdata;
			}
		} else {
			$tags_id = JRequest::getVar('tags_id');
		
			$rating = JRequest::getVar('rating', '');
			$rating = explode('|', $rating);
			$ratingMin = (isset($rating[0]) && !empty($rating[0])) ? intval($rating[0]) : 1;
			$ratingMax = (isset($rating[1]) && !empty($rating[1])) ? intval($rating[1]) : 5;
	
	        $jnow = JFactory::getDate();
	        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
	        $nullDate = $db->getNullDate();
	
	        if (JRequest::getWord('format') == 'feed')
	            $limit = $params->get('feedLimit');
	
	        $query = "SELECT i.id";
	        $query .= " FROM #__k2_items as i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid";
	
	        if ($ordering == 'best' || !($ratingMin <=1 && $ratingMax == 5))
	            $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";
	
			if ($task == 'tag' || $tags_id)
				$query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags AS tags ON tags.id = tags_xref.tagID";
	
	        $query .= " WHERE i.published = 1 AND ";
	
	        if (K2_JVERSION != '15')
	        {
	            $query .= "i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND i.trash = 0"." AND c.published = 1"." AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND c.trash = 0";
	
	            $mainframe = JFactory::getApplication();
	            $languageFilter = $mainframe->getLanguageFilter();
	            if ($languageFilter)
	            {
	                $languageTag = JFactory::getLanguage()->getTag();
	                $query .= " AND c.language IN (".$db->quote($languageTag).",".$db->quote('*').") 
							AND i.language IN (".$db->quote($languageTag).",".$db->quote('*').")";
	            }
	        }
	
	        if (!($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id')))
	        {
	            $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
	            $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
	        }
	        
	        $query .= $where;
	        
	        //GET DYNAMIC COUNTER
			$field = $byLabel ? 'labels' : 'option_id';
			$query = "
				SELECT COUNT(tm.item_id) AS num_items, CONCAT_WS('_', `type`, `asset_id`, ".$db->quoteName($field).") AS tkey
				FROM #__jak2filter_taxonomy t
				INNER JOIN #__jak2filter_taxonomy_map tm ON tm.node_id = t.id
				WHERE tm.item_id IN (
				".$query."
				)
				GROUP BY tkey
				";
			$db->setQuery($query);
			$data = $db->loadAssocList('tkey', 'num_items');
			return $data;
		}
		
	}

	public function getOrderingList() {
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('JDEFAULT'));

		$activeGroups = null;
		if(count($this->activeCats)) {
			$activeGroups = $this->getextraFieldsGroupsByCat($this->activeCats);
		}
		$fields = jaK2GetOrderFields($activeGroups);

		$default = array('date', 'rdate', 'publishUp', 'alpha', 'ralpha', 'order', 'rorder', 'featured', 'hits', 'best', 'modified', 'rand');
		$display = $this->params->get('show_order_field', $default);
		if(empty($display)) {
			$options2 = $fields;
		} else {
			$options2 = array();
			$openGroup = false;

			foreach($fields as $field) {
				if(in_array($field->value, $display) || $field->value == '<OPTGROUP>' || $field->value == '</OPTGROUP>') {
					if($field->value == '</OPTGROUP>' && $openGroup) {
						//remove empty group
						$openGroup = false;
						array_pop($options2);
					} else {
						$openGroup = ($field->value == '<OPTGROUP>');
						$options2[] = $field;
					}
				}
			}
		}

		$options = array_merge($options, $options2);

		$attribs = array('class' => 'inputbox');
		$ordering = JRequest::getVar('ordering', '');
		if(!$ordering) {
			if($this->params->get('catOrdering', 'inherit') == 'inherit') {
				$ordering = $this->comParams->get('catOrdering', 'inherit');
			} else {
				$ordering = $this->params->get('catOrdering', 'inherit');
			}
		}

		$html = JHTML::_('select.genericlist',  $options, 'ordering', $attribs, 'value', 'text', $ordering);
		return $html;
	}

	/**
	 * @param $name - form field name
	 * @param $jatype - JA field type (author, tags, category, xfield, ...)
	 * @param $ff_type - Form field type (select, checkbox, ...)
	 * @param $value - field value
	 * @param array $attrs - form field attributes
	 * @return stdClass
	 */
	private function createFieldObject($name, $jatype, $ff_type, $value, $attrs = array()) {
		$obj = new stdClass();
		$obj->id = 0;
		$obj->name = $name;
		$obj->jatype = $jatype;
		$obj->ff_type = $ff_type;
		$obj->value = $value;
		$obj->group = 0;
		$obj->group_name = '';
		$obj->index = '';
		$obj->attrs = $attrs;
		return $obj;
	}

	public function getLabel($fieldtype, $fieldname, $fieldtitle, $group=0) {
		$fn = ucfirst(strtolower($fieldtype));
		$funcLabel = 'getLabel'.$fn;
		if(method_exists($this, $funcLabel)) {
			$html = call_user_func_array(array($this, $funcLabel), array($fieldname, $fieldtitle, $group));
		} else {
			$html = "\n\t<label class=\"group-label\">{$fieldtitle}</label>";
		}
		return $html;
	}
}

function jaK2GetOrderFields($activeGroups = null) {
	$options = array();
	$options[] = JHtml::_('select.option', 'relevance', JText::_('RELEVANCE'));
	$options[] = JHtml::_('select.option', 'date', JText::_('JAK2_OLDEST_FIRST'));
	$options[] = JHtml::_('select.option', 'rdate', JText::_('JAK2_MOST_RECENT_FIRST'));
	$options[] = JHtml::_('select.option', 'publishUp', JText::_('JAK2_RECENTLY_PUBLISHED'));
	$options[] = JHtml::_('select.option', 'alpha', JText::_('JAK2_TITLE_ALPHABETICAL'));
	$options[] = JHtml::_('select.option', 'ralpha', JText::_('JAK2_TITLE_REVERSEALPHABETICAL'));
	$options[] = JHtml::_('select.option', 'order', JText::_('JAK2_ORDERING'));
	$options[] = JHtml::_('select.option', 'rorder', JText::_('JAK2_ORDERING_REVERSE'));
	$options[] = JHtml::_('select.option', 'featured', JText::_('JAK2_FEATURED_FIRST'));
	$options[] = JHtml::_('select.option', 'hits', JText::_('JAK2_MOST_POPULAR'));
	$options[] = JHtml::_('select.option', 'best', JText::_('JAK2_HIGHEST_RATED'));
	$options[] = JHtml::_('select.option', 'modified', JText::_('JAK2_LATEST_MODIFIED'));
	$options[] = JHtml::_('select.option', 'rand', JText::_('JAK2_RANDOM_ORDERING'));
	//Extra Fields
	$db = JFactory::getDbo();
	$aXFSupported = array('select', 'multipleSelect', 'radio', 'labels', 'textfield', 'date');
	$query = "SELECT f.`id`, f.name, f.group, g.name AS group_name
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON f.group = g.id
			WHERE f.`type` IN ('".implode("','", $aXFSupported). "')
			ORDER BY g.name, f.name";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	if(count($items)) {
		$params = JComponentHelper::getParams('com_jak2filter');
		$xfDataType = $params->get('extra_fields_data_type');

		$dt = array();
		if(is_array($xfDataType) && count($xfDataType)) {
			foreach($xfDataType as $val) {
				@list($xfid, $type) = explode(':', $val);
				$dt[$xfid] = $type;
			}
		}

		$group = '';
		foreach($items as $item) {
			if(is_array($activeGroups) && !in_array($item->group, $activeGroups)) {
				//get Dynamic order by options
				continue;
			}
			$type = isset($dt[$item->id]) ? $dt[$item->id] : 'string';
			if($group != $item->group_name) {
				if($group != '') {
					$options[] = JHtml::_('select.option', '</OPTGROUP>', $group);
				}
				$group = $item->group_name;
				$options[] = JHtml::_('select.option', '<OPTGROUP>', $group);
			}
			if($type == 'number') {
				$options[] = JHtml::_('select.option', 'xf'.$item->id, JText::sprintf('XFIELD_ORDER_ASCENDING', $item->name));
				$options[] = JHtml::_('select.option', 'rxf'.$item->id, JText::sprintf('XFIELD_ORDER_DESCENDING', $item->name));
			} else {
				$options[] = JHtml::_('select.option', 'xf'.$item->id, JText::sprintf('XFIELD_ORDER_ALPHABETICAL', $item->name));
				$options[] = JHtml::_('select.option', 'rxf'.$item->id, JText::sprintf('XFIELD_ORDER_REVERSE_ALPHABETICAL', $item->name));
			}
		}
		$options[] = JHtml::_('select.option', '</OPTGROUP>', $group);
	}

	return $options;
}