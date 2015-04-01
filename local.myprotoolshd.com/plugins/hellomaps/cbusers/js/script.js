/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
var cbusersLocationSearchAutocomplete; 
var deny_module_infowindow;

HelloMapsPopulateFilterCallabck.cbusers = function(){
	if(hQuery('input[type=checkbox]#filter_cbusers:checked').length > 0 )
	{
		hQuery('#filter_cbusersmember_fields').show();
	}
	else
	{
		hQuery('#filter_cbusersmember_fields').hide();
		//remove filters
	}
	CheckSearchOptions();//show hide the search button
    HelloMapsSearch.UpdateFiltersAreaScrollbar();
}
HelloMapsSearch.plugin_result_containers.push('#cbusers_plugin_results');

HelloMapsSearch.initCbusersSearchParams = function(){
	/*if( (hQuery.trim(hQuery('#cbusers_search_text','#filter_ads_fields').val()) == "") && (hQuery(':input[type=checkbox]:checked','#filter_ads_fields').length == 0))
	{
		return false;
	}
	else*/
	{
		if(HelloMapsSearch.searchParams == "")
			HelloMapsSearch.searchParams = hQuery( ":input",'#filter_cbusersmember_fields' ).serialize();
		else
			HelloMapsSearch.searchParams += '&' + hQuery( ":input",'#filter_cbusersmember_fields' ).serialize();
	}
}
//process search result, update the markers and list
HelloMapsSearch.processCbusersSearchResult = function(searchResult){
   // console.log(searchResult);
    if(searchResult.total > 0)
    {
        HelloMapsSearch.totalSearchResult = (HelloMapsSearch.totalSearchResult + searchResult.total);
        hQuery.each(searchResult.rows, function(i,adRow) {
            //console.log(adRow.latitude+', '+adRow.longitude);
            
            var latitude = adRow.latitude;
			var longitude = adRow.longitude;
			var coords = new google.maps.LatLng(latitude, longitude);
            
            //else
            {
                if(cbusers_marker_type == "ad_category")
                {
                    var pinIcon = adRow.marker_icon_url;
                }
                else
                {
                    var pinIcon = new google.maps.MarkerImage(
                        adRow.marker_icon_url,
                        null, /* size is determined at runtime */
                        null, /* origin is 0,0 */
                        null, /* anchor is bottom center of the scaled image */
                        new google.maps.Size(cbusers_marker_width, cbusers_marker_height)
                    );
                }
                

                var marker = new google.maps.Marker({
						position: coords,
						map: map,		
						title: adRow.title,
                        icon: pinIcon
				});
                marker.marker_key = 'cbusersmember_'+adRow.id;
				
                if((adRow.display_marker_infowindow)&&(deny_module_infowindow!=1))
                {
                    google.maps.event.addListener(marker, 'click', function() {
                        infoWindow.setContent(adRow.infowindow_content);                        
                        infoWindow.open(map,marker);
                        HelloMapsSearch.currentMarkerKey = marker.marker_key;
                        //put the scrollbal
                        hQuery(".cbusers_marker_info_window").mCustomScrollbar("destroy");
                        hQuery(".cbusers_marker_info_window").mCustomScrollbar({
                			scrollButtons:{
                				enable:true
                			}
                		});
                    });
                }    
                if(sidebar_enable && contents_enable && marker_mouse_over_enabled && cbusers_show_in_sidebar && enable_cbusers_detail_sidebar)
                {
                    google.maps.event.addListener(marker, 'mouseover', function() {
                        //console.log('mouseover worked!');
                                                
                        var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
                        if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
                        {
                            hQuery("#global_result_list").mCustomScrollbar("scrollTo",'#cbusers_result_global_'+adRow.id);    
                        }
                        else if(hQuery(activeTabLi).attr('id') == 'plugin_tab_li_cbusers')
                        {
                            hQuery("#cbusers_plugin_results").mCustomScrollbar("scrollTo",'#cbusers_result_'+adRow.id);
                        }
                        else//open the tab and initialize the scroller
                        {
                            //check if it is already visited or not
                            hQuery(activeTabLi).removeClass('active');
                            hQuery('.tab-pane.active','#plugin_tabs_contents').removeClass('active');
                            hQuery('#plugin_tab_li_cbusers').addClass('active');
                            hQuery('#title_tab_cbusers').addClass('active');                            
                            HelloMapsSearch.AdjustCbusersTabLayout();//will setup the scroller for notice/results
                            hQuery('#plugin_tab_li_cbusers').addClass('visited');
                            hQuery("#cbusers_plugin_results").mCustomScrollbar("scrollTo",'#cbusers_result_'+adRow.id);
                        }
                        //hQuery('#cbusers_result_'+adRow.id).addClass('selected_ad');
                    });
                    /*google.maps.event.addListener(marker, 'mouseout', function() {
                        console.log('mouseout worked!');
                        hQuery('#cbusers_result_'+adRow.id).removeClass('selected_ad');
                    });*/
                }    
            }
            
            if(HelloMapsSearch.currentMarkerKey == marker.marker_key)
            {
                HelloMapsSearch.oldOpenedMarkerExist = true;
                HelloMapsSearch.lastActiveMarker = marker;
            }
            
            //var icon = new google.maps.MarkerImage(adRow.marker_icon_url, null, null, null, new google.maps.Size(64, 64));//if image is too big, then resize
			
            HelloMapsSearch.markers.push(marker);      
            if(autocenter_markers)
                bounds.extend( new google.maps.LatLng(latitude, longitude) ); 
            
            if(sidebar_enable && contents_enable && cbusers_show_in_sidebar && enable_cbusers_detail_sidebar)
            {
                var adRowGlobalHtml = adRow.html;    
                adRowGlobalHtml = adRowGlobalHtml.replace('cbusers_result_','cbusers_result_global_');
                hQuery('#global_result_list').append(adRowGlobalHtml);
                hQuery('#cbusers_plugin_results').append(adRow.html);
                //focus on the marker if user put mouse on result item
                if(sidebar_mouse_over_enabled)
                {
                    hQuery('#cbusers_result_'+adRow.id+','+'#cbusers_result_global_'+adRow.id).mouseover(function(){
                        if(marker.getAnimation() == null)
                            marker.setAnimation(google.maps.Animation.BOUNCE);    
                    });
                    hQuery('#cbusers_result_'+adRow.id+','+'#cbusers_result_global_'+adRow.id).mouseout(function(){
                        if(marker.getAnimation() != null)
                            marker.setAnimation(null);    
                    });
                }
                //open the marker info window when user clicks on the result
                //if(adRow.display_marker_infowindow) 
                {
                    hQuery('.focus_marker','#cbusers_result_'+adRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");
                    });  
                    hQuery('.focus_marker','#cbusers_result_global_'+adRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");
                    });    
                }    
            }
        });        
        
        if(sidebar_enable && contents_enable)
        {
            hQuery('.collapsed_cbusers_data_show_more').click(function(){
               hQuery(this).hide(); 
               var targetAdId = hQuery(this).attr('data-ad'); 
               var containerSelector = "";
               if(hQuery('#cbusers_result_'+targetAdId).is(':visible'))
               {
                  containerSelector = '#cbusers_result_'+targetAdId;
               }
               else
               {
                  containerSelector = '#cbusers_result_global_'+targetAdId;  
               }
               hQuery('.collapsed_cbusers_data',containerSelector).show('slow');
               hQuery(this).siblings('.collapsed_cbusers_data_show_less').show();
               //if All tabs are open
               var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
               if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
               {
                   hQuery("#global_result_list").mCustomScrollbar("update");  
               }
               else //members plugin tab is active
               {
                   hQuery("#cbusers_plugin_results").mCustomScrollbar("update");   
               }
            });  
            hQuery('.collapsed_cbusers_data_show_less').click(function(){
               hQuery(this).hide();
               var targetAdId = hQuery(this).attr('data-ad'); 
               var containerSelector = "";
               if(hQuery('#cbusers_result_'+targetAdId).is(':visible'))
               {
                  containerSelector = '#cbusers_result_'+targetAdId;
               }
               else
               {
                  containerSelector = '#cbusers_result_global_'+targetAdId;  
               }           
               hQuery('.collapsed_cbusers_data',containerSelector).hide();
               hQuery(this).siblings('.collapsed_cbusers_data_show_more').show();
               //if All tab are open
               var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
               if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
               {
                   hQuery("#global_result_list").mCustomScrollbar("update");  
               }
               else
               {
                   hQuery("#cbusers_plugin_results").mCustomScrollbar("update");    
               }           
            });    
        }        
    }
    if(hQuery('li.active','ul#plugin_tabs_ul').attr('id') == 'plugin_tab_li_cbusers')
        HelloMapsSearch.AdjustCbusersTabLayout();
    if((counter_result_type == "byzoom") && searchResult.display_marker_result_count)
    {
        hQuery('#markerStatistics').append(searchResult.percentageBlock);
    }
    
}
HelloMapsSearch.OnCbusersTabVisit = function(){    
    setTimeout(function(){
        HelloMapsSearch.AdjustCbusersTabLayout(); 
    },200);
}

HelloMapsSearch.AdjustCbusersTabLayout = function(){
    if(contents_enable)
    {
        if(show_global_notice && (notice_position == sidebar_position))
        {
            if(hQuery('.global_notice_open_button').is(':visible'))//notice is closed
            {
                //console.log('Notice is closed');
                var sidebarNewHeight = sidebar_height-120;          
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);                    
            }
            else//notice is opened
            {
                //console.log('Notice is opened');
                var sidebarNewHeight = sidebar_height-notice_offset;     
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);  
            }
            var cbusersmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                cbusersmemberResultsHeight = cbusersmemberResultsHeight - hQuery('#filter_cbusersmember_fields').actual('height') ;
        }        
        else if(hQuery('#notice_box_holder_cbusers_plugin').length > 0)//plugin notice exist
        {
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_cbusers_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_cbusers_plugin').hide();
            }            
            if(!hQuery('#notice_box_holder_cbusers_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_cbusers_plugin').show();
            }
            hQuery("#notice_box_holder_cbusers_plugin .cbusers_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_cbusers_plugin .cbusers_notice_content").mCustomScrollbar({
    			scrollButtons:{
    				enable:true
    			}
    		});
            if(notice_position == sidebar_position)
            {
                var sidebarNewHeight = sidebar_height-notice_offset;     
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);    
            }
            else if(notice_position != sidebar_position)
            {
                hQuery('#map-canvas-sidebar').height(sidebar_height-40);  
            }
            var cbusersmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                cbusersmemberResultsHeight = cbusersmemberResultsHeight - hQuery('#filter_cbusersmember_fields').actual('height') ;
        }
        else
        {
            if(hQuery('.notice_box_holder_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').hide();
            }
            hQuery('#map-canvas-sidebar').height(sidebar_height-40);
            var cbusersmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').height() - hQuery('#plugin_tabs_ul').height() - 5;
            if(search_enable)
                cbusersmemberResultsHeight = cbusersmemberResultsHeight - hQuery('#filter_cbusersmember_fields').actual('height') ;    
        }
        hQuery('#cbusers_plugin_results').height(cbusersmemberResultsHeight);
        hQuery("#cbusers_plugin_results").mCustomScrollbar('destroy');
        hQuery("#cbusers_plugin_results").mCustomScrollbar({
    		scrollButtons:{
    			enable:true
    		}
    	});
            
    }
    else
    {
        //consider notice works
        if(show_global_notice && (notice_position == sidebar_position))//global notice exist
        {
            var sidebarNewHeight = sidebar_height-notice_offset;     
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
        }
        else if(hQuery('#notice_box_holder_cbusers_plugin').length > 0)//plugin notice exist
        {
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_cbusers_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_cbusers_plugin').hide();
            }
            if(!hQuery('#notice_box_holder_cbusers_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_cbusers_plugin').show();
            }
            hQuery("#notice_box_holder_cbusers_plugin .cbusers_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_cbusers_plugin .cbusers_notice_content").mCustomScrollbar({
    			scrollButtons:{
    				enable:true
    			}
    		});
            if(notice_position == sidebar_position)
            {
                var sidebarNewHeight = sidebar_height-notice_offset;     
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);    
            }
            else if(notice_position != sidebar_position)
            {
                hQuery('#map-canvas-sidebar').height(sidebar_height-40);  
            }            
        }
        else//give full height
        {
            if(hQuery('.notice_box_holder_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').hide();
            }
            hQuery('#map-canvas-sidebar').height(sidebar_height);
        }
    }
}
HelloMapsSearch.ChangeBigImage = function(targetAdId,newImgSrc){   
   var containerSelector = "";
   if(hQuery('#cbusers_result_'+targetAdId).is(':visible'))
   {
      containerSelector = '#cbusers_result_'+targetAdId;
   }
   else
   {
      containerSelector = '#cbusers_result_global_'+targetAdId;  
   }           
   hQuery('.cbusersmemberLargeThumb img',containerSelector).attr('src',newImgSrc);
}
HelloMapsSearch.SetupCbusersRadiusSearch = function(){
    // Create the autocomplete object, restricting the search
  // to geographical location types.  
  cbusersLocationSearchAutocomplete = new google.maps.places.Autocomplete(
      /** @type {HTMLInputElement} */(document.getElementById('cbusers_location_text')),
      { types: ['geocode'] });
  // When the user selects an address from the dropdown,
  // populate the address fields in the form.
  google.maps.event.addListener(cbusersLocationSearchAutocomplete, 'place_changed', function() {
    var place = cbusersLocationSearchAutocomplete.getPlace();
    hQuery('#cbusers_location_lat').val(place.geometry.location.lat());
    hQuery('#cbusers_location_lng').val(place.geometry.location.lng());
  });
  
  hQuery('#cbusers_search_radius').bind('keypress', function(e) {
    if( e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
    {
        return false;
    }
  });
}
HelloMapsSearch.CloseCbusersNotice = function(){
    hQuery('.plugin_notice_close_button','#notice_box_holder_cbusers_plugin').hide();
    
    hQuery("#notice_box_holder_cbusers_plugin .cbusers_notice_content").mCustomScrollbar('destroy');    
    
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');    
    
    
    hQuery( "#notice_box_holder_cbusers_plugin" ).animate({
        'height': 0
    }, 500, function() {
        hQuery(this).remove();
        if(contents_enable && (notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
            HelloMapsSearch.AdjustCbusersTabLayout();    
        }
        else if((notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')//give full height
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);            
        }
    });  
}
HelloMapsSearch.OpenCbusersFilters = function(){
    hQuery('#cbusers_filter_area').slideDown('slow',function(){
        hQuery('.cbusers_filter_control_expand').hide();
        hQuery('.cbusers_filter_control_collapse').show();        
        hQuery('.cbusers_filter_control').attr('data-filter_status','visible');
        HelloMapsSearch.AdjustCbusersTabLayout();
    });
}
HelloMapsSearch.CloseCbusersFilters = function(){
    hQuery('#cbusers_filter_area').slideUp('slow',function(){
        hQuery('.cbusers_filter_control_collapse').hide();
        hQuery('.cbusers_filter_control_expand').show();
        hQuery('.cbusers_filter_control').attr('data-filter_status','invisible');
        HelloMapsSearch.AdjustCbusersTabLayout();
    });
}
HelloMapsSearch.ResetCbusersSearch = function(){
    hQuery('input[type=text],textarea','#filter_cbusersmember_fields').val('');
    hQuery('input[type=radio],input[type=checkbox]','#filter_cbusersmember_fields').removeAttr('checked');
    HelloMapsSearch.performSearch();
}
hQuery('document').ready(function(){
    if(hQuery('#hellomapCbusersSearchButton').length > 0)
    {
        hQuery('#hellomapCbusersSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.performSearch();
        });    
    }
    if(hQuery('#hellomapCbusersResetSearchButton').length > 0)
    {
        hQuery('#hellomapCbusersResetSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.ResetCbusersSearch();            
        });    
    }
	
    
    hQuery('a','li#plugin_tab_li_cbusers').click(function(){
       HelloMapsSearch.OnCbusersTabVisit(); 
    });
    if(!contents_enable && hQuery('li#plugin_tab_li_cbusers').hasClass('active'))
    {
        HelloMapsSearch.OnCbusersTabVisit(); 
    }
    if(search_enable && enable_radius_search)
    {
        HelloMapsSearch.SetupCbusersRadiusSearch();        
    }
    //plugin notice show hide
    if(hQuery('.plugin_notice_close_button','#notice_box_holder_cbusers_plugin').length > 0)
    {
        hQuery('.plugin_notice_close_button','#notice_box_holder_cbusers_plugin').click(function(){            
            HelloMapsSearch.CloseCbusersNotice();
        });
    }    
    //plugin notice show hide
    if(hQuery('.cbusers_filter_control_collapse').length > 0)
    {
        hQuery('.cbusers_filter_control_collapse').click(function(){
            HelloMapsSearch.CloseCbusersFilters();    
        });        
    }
    if(hQuery('.cbusers_filter_control_expand').length > 0)
    {
        hQuery('.cbusers_filter_control_expand').click(function(){
            HelloMapsSearch.OpenCbusersFilters();    
        });        
    }
});
