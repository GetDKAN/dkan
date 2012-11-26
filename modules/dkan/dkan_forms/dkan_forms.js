/**
 * @file
 * JS for DKAN forms.
 */
(function ($) {

  /**
   * Shows and hides a description for Drupal form elements.
   */
  $.fn.dkanFormsHide = function () {
    this.each(function () {
      $(this).addClass('compact-form-wrapper');
      var desc = $(this).find('.description').addClass('compact-form-description');
      $(desc).click(function () {
        $(desc).fadeOut('fast');
      });
      var input = $(this).find('input');
      input.blur(function () {
        if ($(input).val() === '') {
          $(desc).fadeIn('slow');
        }
      });
      input.focus(function () {
        $(desc).fadeOut('fast');
      });
      if ($(input).val() != '') {
        $(desc).css('display', 'none');
      }
    });
  }

  Drupal.behaviors.dkanForms = {
    attach: function (context, settings) {
      // Slugify!
      if ($('#edit-path-alias').val() != '') {
        $('#url-edit-preview').hide();
      }
      else {
        // Initially hide the path until clicked.
        $('#field-tags-wrapper .path-form').hide();
        // Hidden by default in case js is disabled.
        $('#url-edit-preview').show();
        // Force URLs to be url friendly.
        $('#edit-path-alias').slugify('#edit-path-alias');
        // Only edit path alias if alias has not been edited.
        $('.form-type-textfield input').click(function(e) {
            $('#edit-path-alias').slugify('.form-type-textfield input');
            $('#url-slug').slugify('.form-type-textfield input');
        });
        $('button.btn').click(function(e) {
          e.preventDefault();
          $('#url-edit-preview').hide();
          $('#field-tags-wrapper .path-form').show();
          $('#edit-path-alias').focus();
          $('#edit-path-alias').addClass('processed');
        });
      }

      var elements = "#edit-field-link-file,#edit-field-link-api, .form-item-title";
      $(elements, context).dkanFormsHide();
      $('#edit-field-tags .description').addClass('compact-form-description');
      $('#edit-field-tags').addClass('compact-form-wrapper');
      $('#edit-field-tags .description').click(function () {
        $('#edit-field-tags .description').fadeOut('fast');
      });
      $('#autocomplete-deluxe-input').focus(function () {
        $('#edit-field-tags .description').fadeOut('fast');
      });
      if ($('#autocomplete-deluxe-item').html() != '') {
        $('#edit-field-tags .description').css('display', 'none');
      }
    }
  }

})(jQuery);
