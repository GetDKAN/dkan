// geo-location shim
// Source: https://gist.github.com/366184

// currentely only serves lat/long
// depends on jQuery

;(function(geolocation, $){

  if (geolocation) return;
  
  var cache;
  
  geolocation = window.navigator.geolocation = {};
  geolocation.getCurrentPosition = function(callback){
    
    if (cache) callback(cache);
    
    $.getScript('//www.google.com/jsapi',function(){
      
      cache = {
        coords : {
          "latitude": google.loader.ClientLocation.latitude, 
          "longitude": google.loader.ClientLocation.longitude
        }
      };
      
      callback(cache);
    });
    
  };
  
  geolocation.watchPosition = geolocation.getCurrentPosition;

})(navigator.geolocation, jQuery);

;(function ($) {
  Drupal.behaviors.geofieldGeolocation = {
    attach: function (context, settings) {
      // callback for getCurrentPosition
      function updateLocation(position) {
        // @TODO: calculate bounding box from accuracy value (accuracy is in meters)
        $fields.find('.geofield_lat').val(position.coords.latitude);
        $fields.find('.geofield_lon').val(position.coords.longitude);
      }
      
      // don't do anything if we're on field configuration
      if (!$(context).find("#edit-instance").length) {
        var $fields = $(context).find('.field-widget-geofield-geolocation');
        // check that we have something to fill up
        // on muti values check only that the first one is empty
        if ($fields.find('.geofield_lat').val() == '' && $fields.find('.geofield_lon').val() == '') {
          // very simple geolocation, no fallback support
          if (navigator.geolocation) {        
	          navigator.geolocation.getCurrentPosition(updateLocation);
          }
        }
      }
      
    }
  };
})(jQuery);
