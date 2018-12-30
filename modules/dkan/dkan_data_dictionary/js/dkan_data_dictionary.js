/**
 * @file
 * Provides options for chart visualization.
 */

(function ($) {

  Drupal.behaviors.dkan_data_dictionary = {
    attach: function (context, settings) {

      $('#edit-field-describedby-file-und-0-ajax-wrapper').once(function () {
        $(this).prepend('<div id="csv-schema-message"></div>');
      });

      if (Drupal.settings.dkan_data_dictionary.fileid != 0) {
        var valid = Drupal.settings.dkan_data_dictionary.valid;

        if (valid) {
          $('.group-conformsto-schema-source .horizontal-tab-button-1').hide();
          $('#csv-schema-message').addClass('alert alert-success').html('<span>Your data dictionary will be converted into a JSON Schema automatically.</span>');
        }
        else {
          $('.group-conformsto-schema-source .horizontal-tab-button-1').show();
          $('#csv-schema-message').addClass('alert alert-warning').html('<span>Your data dictionary is not in a recognized format. It will be presented as a simple file link.</span>');
        }
      }
      else {
        $('.group-conformsto-schema-source .horizontal-tab-button-1').show();
        $('#csv-schema-message').removeClass().html('');
      }
    }
  };

})(jQuery);
