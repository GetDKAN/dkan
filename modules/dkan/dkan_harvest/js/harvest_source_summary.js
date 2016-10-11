(function ($) {

Drupal.behaviors.autoSelectURIField = {
  attach: function (context) {
    $('#harvest_source_summary_uri').on('click', function() {
      $(this).select();
    });
  }
};

})(jQuery);
