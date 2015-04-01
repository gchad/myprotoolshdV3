/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
jQuery(document).ready(function(){	
	jQuery('#jform_params_marker_icon').change(function(){
		if(jQuery(this).val() == 'profile-type')
		{
		    //check the profile type source
            var profileTypeSource = jQuery('#jform_params_profile_type_source').val();
            if(profileTypeSource == 'jomsocial')
            {
                jQuery('li.xipt_profile_type').hide();
                jQuery('li.jomsocial_profile_type').show();
            }
            else if(profileTypeSource == 'xipt')
            {
                jQuery('li.jomsocial_profile_type').hide();
                jQuery('li.xipt_profile_type').show();
            }
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

	
	jQuery('select','.profileTypesMarkers').change(function(){
		buildProfileTypeMarkerJson();
	});
    
    jQuery('select#jform_params_profile_type_source').change(function(){
		ProfileTypeSourceChanged();
	});
    ProfileTypeSourceChanged();
	
});
function buildProfileTypeMarkerJson()
{
	var jsonItems = [];
	jQuery('select','.profileTypesMarkers').each(function(){
		jsonItem = {profileTypeID:jQuery(this).attr('data-profile_type'),profileMarkerImage: jQuery(this).val()};
		jsonItems.push(jsonItem);
	});
	jQuery('#jform_params_markers_name').val(JSON.stringify(jsonItems));
}
function ProfileTypeSourceChanged()//from jomsocial or xipt
{
    var currentSource = jQuery('#jform_params_profile_type_source').val();
    if(currentSource == 'jomsocial')
    {
        jQuery('#jform_params_fitler_xipt_profile_types-lbl').parent().parent().hide();
        jQuery('#jform_params_fitler_profile_types-lbl').parent().parent().show();
    }
    else if(currentSource == 'xipt')
    {
        jQuery('#jform_params_fitler_profile_types-lbl').parent().parent().hide();
        jQuery('#jform_params_fitler_xipt_profile_types-lbl').parent().parent().show();
    }
    if(jQuery('#jform_params_marker_icon').val() == 'profile-type')
        jQuery('#jform_params_marker_icon').change();
}