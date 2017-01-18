/**
 * @file
 * JS for DKAN forms.
 */
(function ($) {

  $.fn.dkanFormsSlugifyTitle = function (source) {
    var $target = this;
    var $source = $(source);

    $source.keyup(function () {
      $target.text($(this).val());
    });

  return this;
  };

  Drupal.behaviors.dkanFormsSlugify = {
    attach: function (context, settings) {
      // Slugify!
      if ($('#edit-path-alias').val() != '') {
        $('#url-edit-preview').hide();
      }
      else {
        // Initially hide the path until clicked.
        $('.node-dataset-form .group-primary .path-form').hide();
        // Hidden by default in case js is disabled.
        $('#url-edit-preview').show();
        // Force URLs to be url friendly.
        $('#edit-path-alias').slugify('#edit-path-alias');
        // Only edit path alias if alias has not been edited.
        $('.form-item-title input').click(function(e) {
          $('#edit-path-alias').slugify($(this));
          $('#url-slug').slugify($(this));
        });
        $('#url-edit-preview button.btn').click(function(e) {
          e.preventDefault();
          $('#url-edit-preview').hide();
          $('.node-dataset-form .group-primary .path-form').show();
          $('#edit-path-alias').focus();
          $('#edit-path-alias').addClass('processed');
        });
      }

      // Resource list.
      $('#block-dkan-forms-dkan-forms-resource-nodes ul li.last').dkanFormsSlugifyTitle('.form-item-title input');

    }
  }

})(jQuery);
