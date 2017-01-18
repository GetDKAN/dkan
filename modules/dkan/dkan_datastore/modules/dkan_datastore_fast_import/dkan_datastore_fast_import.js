(function ($) {
  Drupal.behaviors.dkanDatastoreFastImport = {
    attach: function (context, settings) {
      $('#edit-feedsflatstoreprocessor-geolocate', context).on('change', function (e) {
        $('#edit-use-fast-import').attr('checked', false);
      });
    }
  };
}(jQuery));