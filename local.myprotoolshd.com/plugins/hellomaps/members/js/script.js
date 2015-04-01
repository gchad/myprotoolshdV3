/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
var membersLocationSearchAutocomplete;
var deny_module_infowindow;

HelloMapsPopulateFilterCallabck.members = function(){
	if(hQuery('input[type=checkbox]#filter_members:checked').length > 0 )
	{
		hQuery('#filter_members_fields').show();
	}
	else
	{
		hQuery('#filter_members_fields').hide();
		//remove filters
	}
	CheckSearchOptions();//show hide the search button
    HelloMapsSearch.UpdateFiltersAreaScrollbar();
}
HelloMapsSearch.plugin_result_containers.push('#members_plugin_results');

//will be called from hellomap.js function to collect search params
HelloMapsSearch.initMembersSearchParams = function(){
	/*if( (hQuery.trim(hQuery('#members_search_text','#filter_members_fields').val()) == "") && (hQuery(':input[type=checkbox]:checked','#filter_members_fields').length == 0))
	{
		return false;
	}
	else*/
	{		
		if(HelloMapsSearch.searchParams == "")
			HelloMapsSearch.searchParams = hQuery( ":input",'#filter_members_fields' ).serialize();
		else
			HelloMapsSearch.searchParams += '&' + hQuery( ":input",'#filter_members_fields' ).serialize();
	}
}

//process search result, update the markers and list
HelloMapsSearch.processMembersSearchResult = function(searchResult){
    //console.log(searchResult);
    if(searchResult.total > 0)
    {
        HelloMapsSearch.totalSearchResult = (HelloMapsSearch.totalSearchResult + searchResult.total);
        hQuery.each(searchResult.rows, function(i,userRow) {
            //console.log(userRow);
            //console.log(userRow.latitude+', '+userRow.longitude);
            var latitude = userRow.latitude;
			var longitude = userRow.longitude;
			var coords = new google.maps.LatLng(latitude, longitude);
            
            //else
            {                
                if(members_marker_type == "profile-type")
                {
                    var pinIcon = userRow.marker_icon_url;
                }
                else
                {
					
                    var pinIcon = new google.maps.MarkerImage(
                        userRow.marker_icon_url,
                        null, /* size is determined at runtime */
                        null, /* origin is 0,0 */
                        null, /* anchor is bottom center of the scaled image */
                        new google.maps.Size(members_marker_width, members_marker_height)
                    );   
					
				
                }
                
			    var hmicon= pinIcon;
    			var marker = new google.maps.Marker({
    						position: coords,
    						map: map,		
    						title: userRow.title,
                            /*icon: pinIcon*/
							icon: hmicon, optimized:false
    				});  
				/*new in 1.07g*/
				if(members_marker_type != "profile-type")
                {
				// I create an OverlayView, and set it to add the "markerLayer" class to the markerLayer DIV
				 var myoverlay = new google.maps.OverlayView();
				 myoverlay.draw = function () {
					this.getPanes().markerLayer.id='markerLayer';
					hQuery('#markerLayer').addClass('circlehmaps');			
				 };
				
				 myoverlay.setMap(map);	
				 
				}
				/*end in 1.07g*/
					          
                marker.marker_key = 'members_'+userRow.id;
				
                if ((userRow.display_marker_infowindow)&&(deny_module_infowindow!=1))
                {
                    google.maps.event.addListener(marker, 'click', function() {       
                        infoWindow.setContent(userRow.infowindow_content);                        
                        infoWindow.open(map,marker);
                        HelloMapsSearch.currentMarkerKey = marker.marker_key;
                        //put the scrollbal
                        hQuery(".jomsocial_marker_info_window").mCustomScrollbar("destroy");
                        hQuery(".jomsocial_marker_info_window").mCustomScrollbar({
                			scrollButtons:{
                				enable:true
                			}
                		});
                    });
                    
                }    
                //check the mouseover feature
                if(sidebar_enable && contents_enable && marker_mouse_over_enabled && members_show_in_sidebar && enable_member_detail_sidebar)
                {
                    google.maps.event.addListener(marker, 'mouseover', function() {
                        //hQuery('#jomsocial_member_result_'+userRow.id).addClass('selected_member');
                        //console.log('mouseover worked!');
                        var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
                        if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
                        {
                            hQuery("#global_result_list").mCustomScrollbar("scrollTo",'#jomsocial_member_result_global_'+userRow.id);    
                        }
                        else if(hQuery(activeTabLi).attr('id') == 'plugin_tab_li_members')
                        {
                            hQuery("#members_plugin_results").mCustomScrollbar("scrollTo",'#jomsocial_member_result_'+userRow.id);
                        }
                        else//open the tab and initialize the scroller
                        {
                            //check if it is already visited or not
                            hQuery(activeTabLi).removeClass('active');
                            hQuery('.tab-pane.active','#plugin_tabs_contents').removeClass('active');
                            hQuery('#plugin_tab_li_members').addClass('active');
                            hQuery('#title_tab_members').addClass('active');                            
                            HelloMapsSearch.AdjustMembersTabLayout();//will setup the scroller for notice/results
                            hQuery('#plugin_tab_li_members').addClass('visited');
                            hQuery("#members_plugin_results").mCustomScrollbar("scrollTo",'#jomsocial_member_result_'+userRow.id);
                        }
                    });
                    /*google.maps.event.addListener(marker, 'mouseout', function() {
                        hQuery('#jomsocial_member_result_'+userRow.id).removeClass('selected_member');
                        console.log('mouseout worked!');
                    });*/
                }    
            }
            
            if(HelloMapsSearch.currentMarkerKey == 'members_'+userRow.id)
            {                
                HelloMapsSearch.oldOpenedMarkerExist = true;
                HelloMapsSearch.lastActiveMarker = marker;
            }
            
            HelloMapsSearch.markers.push(marker);          
            if(autocenter_markers)
                bounds.extend( new google.maps.LatLng(latitude, longitude) );
            //add the item to result tab
            if(sidebar_enable && contents_enable && members_show_in_sidebar && enable_member_detail_sidebar)
            {
                var userRowGlobalHtml = userRow.html;
                userRowGlobalHtml = userRowGlobalHtml.replace('jomsocial_member_result_','jomsocial_member_result_global_');
                hQuery('#global_result_list').append(userRowGlobalHtml);
                hQuery('#members_plugin_results').append(userRow.html);
                
                
                //focus on the marker if user put mouse on result item
                if(sidebar_mouse_over_enabled)
                {
                    hQuery('#jomsocial_member_result_'+userRow.id+','+'#jomsocial_member_result_global_'+userRow.id).mouseover(function(){
                        if(marker.getAnimation() == null)
                            marker.setAnimation(google.maps.Animation.BOUNCE);    
                    });
                    hQuery('#jomsocial_member_result_'+userRow.id+','+'#jomsocial_member_result_global_'+userRow.id).mouseout(function(){
                        if(marker.getAnimation() != null)
                            marker.setAnimation(null);    
                    });
                }
                //open the marker info window when user clicks on the result
                //if(userRow.display_marker_infowindow) 
                {
                    hQuery('.focus_marker','#jomsocial_member_result_'+userRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");                    
                    });  
                    hQuery('.focus_marker','#jomsocial_member_result_global_'+userRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");                    
                    });   
                }    
            }
            
        });
        
        hQuery('.collapsed_member_data_show_more').click(function(){
           hQuery(this).hide(); 
           var targetMemberId = hQuery(this).attr('data-member'); 
           var containerSelector = "";
           if(hQuery('#jomsocial_member_result_'+targetMemberId).is(':visible'))
           {
              containerSelector = '#jomsocial_member_result_'+targetMemberId;
           }
           else
           {
              containerSelector = '#jomsocial_member_result_global_'+targetMemberId;  
           }
           hQuery('.collapsed_member_data',containerSelector).show('slow'); 
           hQuery(this).siblings('.collapsed_member_data_show_less').show();
           //if All tabs are open
           var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
           if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
           {
               hQuery("#global_result_list").mCustomScrollbar("update");  
           }
           else //members plugin tab is active
           {
               hQuery("#members_plugin_results").mCustomScrollbar("update");   
           }
        });  
        hQuery('.collapsed_member_data_show_less').click(function(){
           hQuery(this).hide();
           var targetMemberId = hQuery(this).attr('data-member'); 
           var containerSelector = "";
           if(hQuery('#jomsocial_member_result_'+targetMemberId).is(':visible'))
           {
              containerSelector = '#jomsocial_member_result_'+targetMemberId;
           }
           else
           {
              containerSelector = '#jomsocial_member_result_global_'+targetMemberId;  
           }
           
           hQuery('.collapsed_member_data',containerSelector).hide(); 
           hQuery(this).siblings('.collapsed_member_data_show_more').show();
           //if All tab are open
           var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
           if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
           {
               hQuery("#global_result_list").mCustomScrollbar("update");  
           }
           else
           {
               hQuery("#members_plugin_results").mCustomScrollbar("update");    
           }           
        });        
    }
    if(hQuery('li.active','ul#plugin_tabs_ul').attr('id') == 'plugin_tab_li_members')
        HelloMapsSearch.AdjustMembersTabLayout();
    if((counter_result_type == "byzoom") && searchResult.display_marker_result_count)
    {
        hQuery('#markerStatistics').append(searchResult.percentageBlock);
    }
    
}

HelloMapsSearch.OnMembersTabVisit = function(){
    //if(!hQuery('li#plugin_tab_li_members').hasClass('visited'))//initialize the notice scroller
    {
        setTimeout(function(){
            HelloMapsSearch.AdjustMembersTabLayout(); 
        },200);
       //hQuery('li#plugin_tab_li_members').addClass('visited');
    }
}

HelloMapsSearch.AdjustMembersTabLayout = function(){    
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');
    if(currentViewStyle == 'map_view')
    {
        return;
    }
    if(contents_enable)
    {
        if(show_global_notice && (notice_position == sidebar_position))
        {
            var sidebarNewHeight = sidebar_height-notice_offset;     
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
            
            var membersResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                membersResultsHeight = membersResultsHeight  - hQuery('#filter_members_fields').actual('height'); 
        }
        else if(hQuery('#notice_box_holder_members_plugin').length > 0 && pluginNoticeExist)//plugin notice exist
        {            
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_members_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_members_plugin').hide();
            }
            if(!hQuery('#notice_box_holder_members_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_members_plugin').show();
            }
            hQuery("#notice_box_holder_members_plugin .members_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_members_plugin .members_notice_content").mCustomScrollbar({
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
            var membersResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                membersResultsHeight = membersResultsHeight  - hQuery('#filter_members_fields').actual('height');
        }
        else
        {
            if(hQuery('.notice_box_holder_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').hide();
            }
            hQuery('#map-canvas-sidebar').height(sidebar_height-40);  
            var membersResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height') - 5;
            if(search_enable)
                membersResultsHeight = membersResultsHeight  - hQuery('#filter_members_fields').actual('height');    
        }        
    
        
        hQuery('#members_plugin_results').height(membersResultsHeight);
        hQuery("#members_plugin_results").mCustomScrollbar('destroy');
        hQuery("#members_plugin_results").mCustomScrollbar({
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
        else if(hQuery('#notice_box_holder_members_plugin').length > 0 && pluginNoticeExist)//plugin notice exist
        {
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_members_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_members_plugin').hide();
            }
            if(!hQuery('#notice_box_holder_members_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_members_plugin').show();
            }
            hQuery("#notice_box_holder_members_plugin .members_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_members_plugin .members_notice_content").mCustomScrollbar({
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

HelloMapsSearch.SetupMembersRadiusSearch = function(){
    // Create the autocomplete object, restricting the search
  // to geographical location types.  
  membersLocationSearchAutocomplete = new google.maps.places.Autocomplete(
      /** @type {HTMLInputElement} */(document.getElementById('members_location_text')),
      { types: ['geocode'] });
  // When the user selects an address from the dropdown,
  // populate the address fields in the form.
  google.maps.event.addListener(membersLocationSearchAutocomplete, 'place_changed', function() {
    var place = membersLocationSearchAutocomplete.getPlace();
    hQuery('#members_location_lat').val(place.geometry.location.lat());
    hQuery('#members_location_lng').val(place.geometry.location.lng());
  });
  
  hQuery('#members_search_radius').bind('keypress', function(e) {
    if( e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
    {
        return false;
    }
  });
}
HelloMapsSearch.CloseMembersNotice = function(){
    hQuery('.plugin_notice_close_button','#notice_box_holder_members_plugin').hide();
    
    hQuery("#notice_box_holder_members_plugin .members_notice_content").mCustomScrollbar('destroy');
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');    
    
    
    hQuery( "#notice_box_holder_members_plugin" ).animate({
        'height': 0
    }, 500, function() {
        hQuery(this).remove();
        if(contents_enable && (notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
            HelloMapsSearch.AdjustMembersTabLayout();    
        }
        else if((notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')//give full height
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);            
        }
    });
}
HelloMapsSearch.OpenMembersFilters = function(){
    hQuery('#members_filter_area').slideDown('slow',function(){
        hQuery('.members_filter_control_expand').hide();
        hQuery('.members_filter_control_collapse').show();        
        hQuery('.members_filter_control').attr('data-filter_status','visible');
        HelloMapsSearch.AdjustMembersTabLayout();
    });
}
HelloMapsSearch.CloseMembersFilters = function(){
    hQuery('#members_filter_area').slideUp('slow',function(){
        hQuery('.members_filter_control_collapse').hide();
        hQuery('.members_filter_control_expand').show();
        hQuery('.members_filter_control').attr('data-filter_status','invisible');
        HelloMapsSearch.AdjustMembersTabLayout();
    });
}
HelloMapsSearch.ResetMembersSearch = function(){
    hQuery('input[type=text],textarea','#filter_members_fields').val('');
    hQuery('input[type=radio],input[type=checkbox]','#filter_members_fields').removeAttr('checked');
    HelloMapsSearch.performSearch();
}
hQuery('document').ready(function(){
    if(hQuery('#hellomapMembersSearchButton').length > 0)
    {
        hQuery('#hellomapMembersSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.performSearch();
        });    
    }
    if(hQuery('#hellomapMembersResetSearchButton').length > 0)
    {
        hQuery('#hellomapMembersResetSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.ResetMembersSearch();
        });    
    }
    
    hQuery('a','li#plugin_tab_li_members').click(function(){
       HelloMapsSearch.OnMembersTabVisit(); 
    });
    if(!contents_enable && hQuery('li#plugin_tab_li_members').hasClass('active'))
    {
        HelloMapsSearch.OnMembersTabVisit(); 
    }
    if(search_enable && enable_radius_search)
    {
        HelloMapsSearch.SetupMembersRadiusSearch();        
    }
   //plugin notice show hide
   if(hQuery('.plugin_notice_close_button','#notice_box_holder_members_plugin').length > 0)
   {
        hQuery('.plugin_notice_close_button','#notice_box_holder_members_plugin').click(function(){            
            HelloMapsSearch.CloseMembersNotice();
        });
   }
   //plugin notice show hide
   if(hQuery('.members_filter_control_collapse').length > 0)
    {
        hQuery('.members_filter_control_collapse').click(function(){
            HelloMapsSearch.CloseMembersFilters();    
        });        
    }
    if(hQuery('.members_filter_control_expand').length > 0)
    {
        hQuery('.members_filter_control_expand').click(function(){
            HelloMapsSearch.OpenMembersFilters();    
        });        
    }
});