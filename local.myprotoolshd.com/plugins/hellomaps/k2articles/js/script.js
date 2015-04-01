/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
var k2articlesLocationSearchAutocomplete;
var deny_module_infowindow;
HelloMapsPopulateFilterCallabck.k2articles = function(){
	if(hQuery('input[type=checkbox]#filter_k2articles:checked').length > 0 )
	{
		hQuery('#filter_k2articlesitem_fields').show();
	}
	else
	{
		hQuery('#filter_k2articlesitem_fields').hide();
		//remove filters
	}
	CheckSearchOptions();//show hide the search button
    HelloMapsSearch.UpdateFiltersAreaScrollbar();
}
HelloMapsSearch.plugin_result_containers.push('#k2articles_plugin_results');

HelloMapsSearch.initK2articlesSearchParams = function(){
	/*if( (hQuery.trim(hQuery('#k2articles_search_text','#filter_ads_fields').val()) == "") && (hQuery(':input[type=checkbox]:checked','#filter_ads_fields').length == 0))
	{
		return false;
	}
	else*/
	{
		if(HelloMapsSearch.searchParams == "")
			HelloMapsSearch.searchParams = hQuery( ":input",'#filter_k2articlesitem_fields' ).serialize();
		else
			HelloMapsSearch.searchParams += '&' + hQuery( ":input",'#filter_k2articlesitem_fields' ).serialize();
	}
}
//process search result, update the markers and list
HelloMapsSearch.processK2articlesSearchResult = function(searchResult){
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
                if(k2articles_marker_type == "k2articlescategory-type")
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
                        new google.maps.Size(k2articles_marker_width, k2articles_marker_height)
                    );
                }
				
				/*1.7g*/
				   //var myIcon= adRow.marker_icon_url;
				   var hmicon= pinIcon;
				   var marker = new google.maps.Marker({
					  position: coords,
					  map: map,  
					  title: adRow.title,
										/*icon: pinIcon*/
				   icon: hmicon, optimized:false
					}); 
					
				 if(k2articles_marker_type != "k2articlescategory-type")
                {
				// I create an OverlayView, and set it to add the "markerLayer" class to the markerLayer DIV
				 var myoverlay = new google.maps.OverlayView();
				 myoverlay.draw = function () {
				  this.getPanes().markerLayer.id='markerLayer';
				  hQuery('#markerLayer').addClass('circlehmaps');
				 };
				 myoverlay.setMap(map); 
				}
                marker.marker_key = 'k2articlesmember_'+adRow.id;
				
				
				/*Add 1.7g*/
                if ((adRow.display_marker_infowindow)&&(deny_module_infowindow!=1))
                {
                    google.maps.event.addListener(marker, 'click', function() {
                        infoWindow.setContent(adRow.infowindow_content);                        
                        infoWindow.open(map,marker);
                        HelloMapsSearch.currentMarkerKey = marker.marker_key;
                        //put the scrollbal
                        hQuery(".k2articles_marker_info_window").mCustomScrollbar("destroy");
                        hQuery(".k2articles_marker_info_window").mCustomScrollbar({
                			scrollButtons:{
                				enable:true
                			}
                		});
                    });
                }    
				
				
				
                if(sidebar_enable && contents_enable && marker_mouse_over_enabled && k2articles_show_in_sidebar && enable_k2articles_detail_sidebar)
                {
                    google.maps.event.addListener(marker, 'mouseover', function() {
                        //console.log('mouseover worked!');
                                                
                        var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
                        if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
                        {
                            hQuery("#global_result_list").mCustomScrollbar("scrollTo",'#k2articles_result_global_'+adRow.id);    
                        }
                        else if(hQuery(activeTabLi).attr('id') == 'plugin_tab_li_k2articles')
                        {
                            hQuery("#k2articles_plugin_results").mCustomScrollbar("scrollTo",'#k2articles_result_'+adRow.id);
                        }
                        else//open the tab and initialize the scroller
                        {
                            //check if it is already visited or not
                            hQuery(activeTabLi).removeClass('active');
                            hQuery('.tab-pane.active','#plugin_tabs_contents').removeClass('active');
                            hQuery('#plugin_tab_li_k2articles').addClass('active');
                            hQuery('#title_tab_k2articles').addClass('active');                            
                            HelloMapsSearch.AdjustK2articlesTabLayout();//will setup the scroller for notice/results
                            hQuery('#plugin_tab_li_k2articles').addClass('visited');
                            hQuery("#k2articles_plugin_results").mCustomScrollbar("scrollTo",'#k2articles_result_'+adRow.id);
                        }
                        //hQuery('#k2articles_result_'+adRow.id).addClass('selected_ad');
                    });
                    /*google.maps.event.addListener(marker, 'mouseout', function() {
                        console.log('mouseout worked!');
                        hQuery('#k2articles_result_'+adRow.id).removeClass('selected_ad');
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
            
            if(sidebar_enable && contents_enable && k2articles_show_in_sidebar && enable_k2articles_detail_sidebar)
            {
                var adRowGlobalHtml = adRow.html;    
                adRowGlobalHtml = adRowGlobalHtml.replace('k2articles_result_','k2articles_result_global_');
                hQuery('#global_result_list').append(adRowGlobalHtml);
                hQuery('#k2articles_plugin_results').append(adRow.html);
                //focus on the marker if user put mouse on result item
                if(sidebar_mouse_over_enabled)
                {
                    hQuery('#k2articles_result_'+adRow.id+','+'#k2articles_result_global_'+adRow.id).mouseover(function(){
                        if(marker.getAnimation() == null)
                            marker.setAnimation(google.maps.Animation.BOUNCE);    
                    });
                    hQuery('#k2articles_result_'+adRow.id+','+'#k2articles_result_global_'+adRow.id).mouseout(function(){
                        if(marker.getAnimation() != null)
                            marker.setAnimation(null);    
                    });
                }
                //open the marker info window when user clicks on the result
                //if(adRow.display_marker_infowindow) 
                {
                    hQuery('.focus_marker','#k2articles_result_'+adRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");
                    });  
                    hQuery('.focus_marker','#k2articles_result_global_'+adRow.id).click(function(){
                        map.setCenter(marker.getPosition());//set the center of the map to this user
                        google.maps.event.trigger(marker, "click");
                    });    
                }    
            }
        });        
        
        if(sidebar_enable && contents_enable)
        {
            hQuery('.collapsed_k2articles_data_show_more').click(function(){
               hQuery(this).hide(); 
               var targetAdId = hQuery(this).attr('data-ad'); 
               var containerSelector = "";
               if(hQuery('#k2articles_result_'+targetAdId).is(':visible'))
               {
                  containerSelector = '#k2articles_result_'+targetAdId;
               }
               else
               {
                  containerSelector = '#k2articles_result_global_'+targetAdId;  
               }
               hQuery('.collapsed_k2articles_data',containerSelector).show('slow');
               hQuery(this).siblings('.collapsed_k2articles_data_show_less').show();
               //if All tabs are open
               var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
               if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
               {
                   hQuery("#global_result_list").mCustomScrollbar("update");  
               }
               else //members plugin tab is active
               {
                   hQuery("#k2articles_plugin_results").mCustomScrollbar("update");   
               }
            });  
            hQuery('.collapsed_k2articles_data_show_less').click(function(){
               hQuery(this).hide();
               var targetAdId = hQuery(this).attr('data-ad'); 
               var containerSelector = "";
               if(hQuery('#k2articles_result_'+targetAdId).is(':visible'))
               {
                  containerSelector = '#k2articles_result_'+targetAdId;
               }
               else
               {
                  containerSelector = '#k2articles_result_global_'+targetAdId;  
               }           
               hQuery('.collapsed_k2articles_data',containerSelector).hide();
               hQuery(this).siblings('.collapsed_k2articles_data_show_more').show();
               //if All tab are open
               var activeTabLi = hQuery('li.active','#plugin_tabs_ul');
               if(!hQuery(activeTabLi).hasClass('plugin_tab_li'))//All tabs are displayed
               {
                   hQuery("#global_result_list").mCustomScrollbar("update");  
               }
               else
               {
                   hQuery("#k2articles_plugin_results").mCustomScrollbar("update");    
               }           
            });    
        }        
    }
    if(hQuery('li.active','ul#plugin_tabs_ul').attr('id') == 'plugin_tab_li_k2articles')
        HelloMapsSearch.AdjustK2articlesTabLayout();
    if((counter_result_type == "byzoom") && searchResult.display_marker_result_count)
    {
        hQuery('#markerStatistics').append(searchResult.percentageBlock);
    }
    
}
HelloMapsSearch.OnK2articlesTabVisit = function(){    
    setTimeout(function(){
        HelloMapsSearch.AdjustK2articlesTabLayout(); 
    },200);
}

HelloMapsSearch.AdjustK2articlesTabLayout = function(){
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
            var k2articlesmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                k2articlesmemberResultsHeight = k2articlesmemberResultsHeight - hQuery('#filter_k2articlesitem_fields').actual('height') ;
        }        
        else if(hQuery('#notice_box_holder_k2articles_plugin').length > 0)//plugin notice exist
        {
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_k2articles_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_k2articles_plugin').hide();
            }            
            if(!hQuery('#notice_box_holder_k2articles_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_k2articles_plugin').show();
            }
            hQuery("#notice_box_holder_k2articles_plugin .k2articles_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_k2articles_plugin .k2articles_notice_content").mCustomScrollbar({
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
            var k2articlesmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').actual('height') - hQuery('#plugin_tabs_ul').actual('height');
            if(search_enable)
                k2articlesmemberResultsHeight = k2articlesmemberResultsHeight - hQuery('#filter_k2articlesitem_fields').actual('height') ;
        }
        else
        {
            if(hQuery('.notice_box_holder_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').hide();
            }
            hQuery('#map-canvas-sidebar').height(sidebar_height-40);
            var k2articlesmemberResultsHeight = hQuery('#map-canvas-sidebar').height() - hQuery('.toolsbar-area').height() - hQuery('#plugin_tabs_ul').height() - 5;
            if(search_enable)
                k2articlesmemberResultsHeight = k2articlesmemberResultsHeight - hQuery('#filter_k2articlesitem_fields').actual('height') ;    
        }
        hQuery('#k2articles_plugin_results').height(k2articlesmemberResultsHeight);
        hQuery("#k2articles_plugin_results").mCustomScrollbar('destroy');
        hQuery("#k2articles_plugin_results").mCustomScrollbar({
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
        else if(hQuery('#notice_box_holder_k2articles_plugin').length > 0)//plugin notice exist
        {
            if(hQuery('.notice_box_holder_plugin').not('#notice_box_holder_k2articles_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').not('#notice_box_holder_k2articles_plugin').hide();
            }
            if(!hQuery('#notice_box_holder_k2articles_plugin').is(':visible'))
            {
                hQuery('#notice_box_holder_k2articles_plugin').show();
            }
            hQuery("#notice_box_holder_k2articles_plugin .k2articles_notice_content").mCustomScrollbar("destroy");
            hQuery("#notice_box_holder_k2articles_plugin .k2articles_notice_content").mCustomScrollbar({
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
   if(hQuery('#k2articles_result_'+targetAdId).is(':visible'))
   {
      containerSelector = '#k2articles_result_'+targetAdId;
   }
   else
   {
      containerSelector = '#k2articles_result_global_'+targetAdId;  
   }           
   hQuery('.k2articlesmemberLargeThumb img',containerSelector).attr('src',newImgSrc);
}
HelloMapsSearch.SetupK2articlesRadiusSearch = function(){
    // Create the autocomplete object, restricting the search
  // to geographical location types.  
  k2articlesLocationSearchAutocomplete = new google.maps.places.Autocomplete(
      /** @type {HTMLInputElement} */(document.getElementById('k2articles_location_text')),
      { types: ['geocode'] });
  // When the user selects an address from the dropdown,
  // populate the address fields in the form.
  google.maps.event.addListener(k2articlesLocationSearchAutocomplete, 'place_changed', function() {
    var place = k2articlesLocationSearchAutocomplete.getPlace();
    hQuery('#k2articles_location_lat').val(place.geometry.location.lat());
    hQuery('#k2articles_location_lng').val(place.geometry.location.lng());
  });
  
  hQuery('#k2articles_search_radius').bind('keypress', function(e) {
    if( e.which!=8 && e.which!=0 && (e.which<48 || e.which>57))
    {
        return false;
    }
  });
}
HelloMapsSearch.CloseK2articlesNotice = function(){
    hQuery('.plugin_notice_close_button','#notice_box_holder_k2articles_plugin').hide();
    
    hQuery("#notice_box_holder_k2articles_plugin .k2articles_notice_content").mCustomScrollbar('destroy');    
    
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');    
    
    
    hQuery( "#notice_box_holder_k2articles_plugin" ).animate({
        'height': 0
    }, 500, function() {
        hQuery(this).remove();
        if(contents_enable && (notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
            HelloMapsSearch.AdjustK2articlesTabLayout();    
        }
        else if((notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')//give full height
        {
            var sidebarNewHeight = sidebar_height-42;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);            
        }
    });  
}
HelloMapsSearch.OpenK2articlesFilters = function(){
    hQuery('#k2articles_filter_area').slideDown('slow',function(){
        hQuery('.k2articles_filter_control_expand').hide();
        hQuery('.k2articles_filter_control_collapse').show();        
        hQuery('.k2articles_filter_control').attr('data-filter_status','visible');
        HelloMapsSearch.AdjustK2articlesTabLayout();
    });
}
HelloMapsSearch.CloseK2articlesFilters = function(){
    hQuery('#k2articles_filter_area').slideUp('slow',function(){
        hQuery('.k2articles_filter_control_collapse').hide();
        hQuery('.k2articles_filter_control_expand').show();
        hQuery('.k2articles_filter_control').attr('data-filter_status','invisible');
        HelloMapsSearch.AdjustK2articlesTabLayout();
    });
}
HelloMapsSearch.ResetK2articlesSearch = function(){
    hQuery('input[type=text],textarea','#filter_k2articlesitem_fields').val('');
    hQuery('input[type=radio],input[type=checkbox]','#filter_k2articlesitem_fields').removeAttr('checked');
    HelloMapsSearch.performSearch();
}
hQuery('document').ready(function(){
    if(hQuery('#hellomapK2articlesSearchButton').length > 0)
    {
        hQuery('#hellomapK2articlesSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.performSearch();
        });    
    }
    if(hQuery('#hellomapK2articlesResetSearchButton').length > 0)
    {
        hQuery('#hellomapK2articlesResetSearchButton').click(function(){
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.ResetK2articlesSearch();            
        });    
    }
	
    
    hQuery('a','li#plugin_tab_li_k2articles').click(function(){
       HelloMapsSearch.OnK2articlesTabVisit(); 
    });
    if(!contents_enable && hQuery('li#plugin_tab_li_k2articles').hasClass('active'))
    {
        HelloMapsSearch.OnK2articlesTabVisit(); 
    }
    if(search_enable && enable_radius_search)
    {
        HelloMapsSearch.SetupK2articlesRadiusSearch();        
    }
    //plugin notice show hide
    if(hQuery('.plugin_notice_close_button','#notice_box_holder_k2articles_plugin').length > 0)
    {
        hQuery('.plugin_notice_close_button','#notice_box_holder_k2articles_plugin').click(function(){            
            HelloMapsSearch.CloseK2articlesNotice();
        });
    }    
    //plugin notice show hide
    if(hQuery('.k2articles_filter_control_collapse').length > 0)
    {
        hQuery('.k2articles_filter_control_collapse').click(function(){
            HelloMapsSearch.CloseK2articlesFilters();    
        });        
    }
    if(hQuery('.k2articles_filter_control_expand').length > 0)
    {
        hQuery('.k2articles_filter_control_expand').click(function(){
            HelloMapsSearch.OpenK2articlesFilters();    
        });        
    }
});
