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
      desc.click(function () {
        input.focus();
      });
      var input = $(this).find('input');
      if ($(input).html() == null) {
        var input = $(this).find('textarea');
      }
      input.blur(function () {
        if (input.val() === '') {
          desc.fadeIn('fast');
        }
      });
      input.keyup(function () {
        if (input.val() != '') {
          desc.hide();
        }
      });
      if (input.val() != '') {
        desc.css('display', 'none');
      }
    });
  }
  Drupal.behaviors.dkanPush = {
    attach: function (context, settings) {
      if (settings.dkanPush) {
        //window.history.pushState("", "", '/node/add/resource?dataset=' + settings.dkanPush.nid);
      }
    }
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

      // Resource list.
      $('#block-dkan-forms-dkan-forms-resource-nodes ul li.last').slugify('.form-type-textfield input');

      var elements = "#edit-field-link-file,#edit-field-link-api,.form-item-title,.field-name-body,#views-exposed-form-dataset-page";
      $(elements, context).dkanFormsHide();
      $('#edit-field-tags .description').addClass('compact-form-description');
      $('#edit-field-tags').addClass('compact-form-wrapper');
      $('#edit-field-tags .description').click(function () {
        $('#edit-field-tags .description').fadeOut('fast');
      });
      $('#autocomplete-deluxe-input').focus(function () {
        $('#edit-field-tags .description').fadeOut('fast');
      });
      if ($('#autocomplete-deluxe-item').html() != null) {
        $('#edit-field-tags .description').css('display', 'none');
      }
      $('#edit-field-format .description').addClass('compact-form-description');
      $('#edit-field-format').addClass('compact-form-wrapper');
      $('#edit-field-format .description').click(function () {
        $('#edit-field-format .description').fadeOut('fast');
      });
      $('#autocomplete-deluxe-input').focus(function () {
        $('#edit-field-format .description').fadeOut('fast');
      });
      if ($('#autocomplete-deluxe-item').html() != null) {
        $('#edit-field-format .description').css('display', 'none');
      }
    }
  }

})(jQuery);
