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
      var input = $(this).find('input');
      desc.click(function () {
        input.focus();
      });
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

  /**
   * Shows and hides a description for Autocomplete Deluxe form elements.
   */
  $.fn.dkanFormsAutoDeluxeHide = function () {
    this.each(function () {
      $(this).addClass('compact-form-wrapper');
      var desc = $(this).find('.description').addClass('compact-form-description');
      var input = $(this).find('#autocomplete-deluxe-input');
      desc.click(function () {
        input.focus();
      });
      input.focus(function () {
        desc.hide();
      });
      if ($('#autocomplete-deluxe-item').html() != null) {
        desc.css('display', 'none');
      }
      if ($(this).find('input').val() != '') {
        desc.css('display', 'none');
      }
    });
  }

  Drupal.behaviors.dkanAddtional = {
    attach: function (context, settings) {
      console.log(settings.dkanAdditional);
      if (settings.dkanAdditional && context.context) {
        var pos = $('#zone-content-wrapper').offset();
        $('html, body').animate({ scrollTop: pos.top}, 'fast');
        window.history.pushState("", "", '/node/' +  settings.dkanAdditional.nid + '/edit?additional=1');
        delete settings.dkanAdditional;
      }
    }
  }
  Drupal.behaviors.dkanPush = {
    attach: function (context, settings) {
      console.log(settings.dkanPush);
      if (settings.dkanPush && context.context) {
        var pos = $('#zone-content-wrapper').offset();
        $('html, body').animate({ scrollTop: pos.top}, 'fast');
        window.history.pushState("", "", '/node/add/resource?dataset=' + settings.dkanPush.nid);
        // Make sure this doesn't fire again.
        delete settings.dkanPush;
      }
    }
  }

  Drupal.behaviors.dkanForms = {
    attach: function (context, settings) {

      var elements = "#edit-field-link-file,#edit-field-link-api,.field-name-body,#views-exposed-form-dataset-page,#block-dkan-dataset-dkan-dataset-search-bar";
      $(elements, context).dkanFormsHide();
      var autoDeluxeElements = ".field-name-field-tags,#edit-field-format";
      $(autoDeluxeElements, context).dkanFormsAutoDeluxeHide();
    }
  }

})(jQuery);
