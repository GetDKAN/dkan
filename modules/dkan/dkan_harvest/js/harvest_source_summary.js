/**
 * @file
 */

(function ($) {

Drupal.behaviors.autoSelectURIField = {
  attach: function (context) {
    $('#harvest_source_summary_uri').on('click', function (e) {
      $(this).select();
    });
    var arrows = [37,38,39,40];
    $('#harvest_source_summary_uri').on('keydown', function(e){
      if(arrows.indexOf(e.keyCode) === -1) {
        e.preventDefault();
      }
    });
  }
};

})(jQuery);