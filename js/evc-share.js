/* EVC-Share */ 

jQuery(document).ready(function($) {
    /*
    var oTimeout = 5000;
    var oScreens = 4/5;
    var oCookieExpires = 2;
    var oAction = 'scroll'; 
    
    
    var sTimeout = 5000;
    var sScreens = 3/4;
    var sCookieExpires = 365;
    var sSpeed = 800;
    var sAction = 'scroll'; 
    */
    if ($('#overlay-sidebar-wrap').length) {
    
      var oWrap = $('#overlay-sidebar-wrap');
      // Close button
      oWrap.find('.overlay-sidebar-close').on( 'click', function() {
        oWrap.fadeOut( 200, function() {
          oWrap.addClass('hide');
        });
      });  
      
      var oOpen = false;
      
      if (oAction == 'timeout') {
        setTimeout( function() {
          if ( !oOpen ) {
            oInit();
            oOpen = true;
          }
        }, oTimeout );
      }    
      
      if (oAction == 'scroll') {
        $(document).scroll(function () {
          var docViewHeight = $(window).height();
          var docViewTop = $(window).scrollTop();
          if ( docViewTop > docViewHeight * oScreens  && !oOpen ) {
            oInit();
            oOpen = true;  
          }
        });    
      }    

        
      function oInit () {    
        // Open          
        $('#overlay-sidebar-bg').fadeIn( {
          duration: 200,
          complete: function() {
            $('#overlay-sidebar-bg').removeClass('hide');
            $('#overlay-sidebar').css('top', oTop);
          },
          done: function() {
            $('#overlay-sidebar').css('top', oTop);
          }
        });
        
        if ($.cookie('oSidebar') == 'undefined' || !$.cookie('oSidebar'))
          $.cookie('oSidebar', '1', { expires: oCookieExpires, path: '/' });
        else
          $.cookie('oSidebar', parseInt($.cookie('oSidebar')) + 1, { expires: oCookieExpires, path: '/' });    
      }
    } // Overlay Sidebar End
    
    if ($('#slide-sidebar-wrap').length) {
    
      var sOpen = false;
      var sWrap = $('#slide-sidebar-wrap');
      var sWidth = $( "#slide-sidebar" ).css('right');
      sWrap.find('.slide-sidebar-close').on( 'click', function() {
        $( "#slide-sidebar" ).animate({ "right": sWidth}, sSpeed );
      });
      
      
      if (sAction == 'timeout') {
        setTimeout( function() {
          if ( !oOpen ) {
            sInit();
            sOpen = true;
          }
        }, sTimeout );
      }    
      
      if (sAction == 'scroll') {
        $(document).scroll(function () {
          var docViewHeight = $(window).height();
          var docViewTop = $(window).scrollTop();
          if ( docViewTop > docViewHeight * sScreens  && !sOpen ) {
            sInit();
            sOpen = true;  
          }
        });    
      }      

          
      function sInit () {
        $( "#slide-sidebar" ).animate({ "right": 0}, sSpeed );
            
        if ($.cookie('sSidebar') == 'undefined' || !$.cookie('sSidebar')) {
          $.cookie('sSidebar', '1', { expires: sCookieExpires, path: '/' });
          console.log($.cookie('sSidebar'));
          }
        else
          $.cookie('sSidebar', parseInt($.cookie('sSidebar')) + 1, { expires: sCookieExpires, path: '/' });
      }
    }  // Slide Sidebar End
    
    
  
  }); // End jQuery 
  
  function async_load(u,id) {
    if (!gid(id)) {
      s="script", d=document,
      o = d.createElement(s);
      o.type = 'text/javascript';
      o.id = id;
      o.async = true;
      o.src = u;
      // Creating scripts on page
      x = d.getElementsByTagName(s)[0];
      x.parentNode.insertBefore(o,x);
    }
  }
  
  function gid (id){
    return document.getElementById(id);
  }
  
  window.onload = function() {  
    async_load("//vk.com/js/api/openapi.js", "id-vkontakte");//vkontakte
  };
   
  // Инициализация vkontakte
  window.vkAsyncInit = function(){
    
    //console.log(VKWidgetsLike);
    if (typeof VKWidgetsLike !== 'undefined' && VKWidgetsLike.length > 0) {
      for (index = 0; index < VKWidgetsLike.length; ++index) {
        VK.Widgets.Like(VKWidgetsLike[index].element_id, VKWidgetsLike[index].options);
      }
    }
    
    if (typeof VKWidgetsGroup !== 'undefined' && VKWidgetsGroup.length > 0) {
      for (index = 0; index < VKWidgetsGroup.length; ++index) {
        //console.log(VKWidgetsGroup);
        VK.Widgets.Group(VKWidgetsGroup[index].element_id, VKWidgetsGroup[index].options, VKWidgetsGroup[index].group_id);
      }
      
      VK.Observer.subscribe('widgets.groups.joined', function(n) {
        console.log(VKWidgetsGroup[n - 1].group_id);
        
        var data = {
          action: 'evc_add_vk_widget_stats',
          gid: VKWidgetsGroup[n - 1].group_id,
          widget: 'group',
          waction: 'joined'
        };
        jQuery.ajax({
          url: ajaxurl,
          data: data,
          type:"POST",
          dataType: 'json'
        }); 
        
      });
      
      VK.Observer.subscribe('widgets.groups.leaved', function(n) {
        console.log(VKWidgetsGroup[n - 1].group_id);
        
        var data = {
          action: 'evc_add_vk_widget_stats',
          gid: VKWidgetsGroup[n - 1].group_id,
          widget: 'group',
          waction: 'leaved'
        };
        jQuery.ajax({
          url: ajaxurl,
          data: data,
          type:"POST",
          dataType: 'json'
        });         

      });    
    }    
    
  };  
