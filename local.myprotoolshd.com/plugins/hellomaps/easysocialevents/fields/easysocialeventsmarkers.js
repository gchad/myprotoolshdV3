/**
 * @version     1.0.8
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
jQuery(document).ready(function(){	
	jQuery('#jform_params_marker_icon').change(function(){
		if(jQuery(this).val() == 'easysocialevent-type')
		{
			jQuery('#jform_params_markers_name-lbl').parent().parent().show();
		}
		else
		{
			jQuery('#jform_params_markers_name-lbl').parent().parent().hide();
		}
		if(jQuery(this).val() == 'custom')
		{
			jQuery('#jform_params_custom_marker_image-lbl').parent().parent().show();
		}
		else
		{
			jQuery('#jform_params_custom_marker_image-lbl').parent().parent().hide();	
		}
	});
	jQuery('#jform_params_marker_icon').change();

	
	jQuery('select','.easysocialeventsmarkers').change(function(){
		buildEasySocialProfileTypeMarkerJson();
	});
	
});
function buildEasySocialProfileTypeMarkerJson()
{
	var jsonItems = [];
	jQuery('select','.easysocialeventsmarkers').each(function(){
		jsonItem = {profileTypeID:jQuery(this).attr('data-profile_type'),profileMarkerImage: jQuery(this).val()};
		jsonItems.push(jsonItem);
	});
	jQuery('#jform_params_markers_name').val(JSON.stringify(jsonItems));
}