
/**
 * jQuery for viewing changes to custom beautytips style.
 */
(function ($) {
  Drupal.behaviors.beautytipsCustomStyles = {
    attach: function(context, settings) {
      var btPopup = $("#beautytips-popup-changes");
      var popupText = "Sed justo nibh, ultrices ut gravida et, laoreet et elit. Nullam consequat lacus et dui dignissim venenatis. Curabitur quis urna eget mi interdum viverra quis eu enim. Ut sit amet nunc augue. Morbi ferm entum ultricies velit sed aliquam. Etiam dui tortor, auctor sed tempus ac, auctor sed sapien.";
      var currentTheme = $("input[name='beautytips_default_style']:checked").val(); 
      btPopup.bt(popupText, {trigger: 'none', clickAnywhereToClose: false, closeWhenOthersOpen: false, positions: 'bottom', cssClass: 'beautytips-fixed-beautytips'});
      btPopup.btOn();

      
      // Add the color picker to certain textfields
      $('#edit-custom-styles-fill, #edit-custom-styles-strokestyle, #edit-custom-styles-css-styles-color').ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) {
          $(el).val('#' + hex);
          $(el).ColorPickerHide();
        },
        onBeforeShow: function () {
          value = this.value.replace("#", "");
          $(this).ColorPickerSetColor(value);
        }
      })
      .bind('keyup', function(){
        $(this).ColorPickerSetColor(this.value);
      });

      var themeSettings = Drupal.settings.beautytips;
      $("#beauty-default-styles input").click(function() {
        currentTheme = $("input[name='beautytips_default_style']:checked").val();
      });

      function beautytipsStyleTip() {
        btPopup.btOff(); 
        options = beautytipsSetupDefaultOptions(themeSettings[currentTheme]); 
        // General options
        $(".bt-custom-styles .fieldset-wrapper").children('.form-item:not(.beautytips-css-styling)').each( function() {
          var name = $(this).find('input').attr('name'); 
          var optionName = name.replace("custom_styles[", "");
          optionName = optionName.replace("]", "");
          var newValue = $(this).find('input').val();
          if (optionName == 'shadow') {
            newValue = $(".beautytips-options-shadow input[@name='custom_styles[shadow]']:checked").val();
            newValue = newValue == 'default' ? null : (newValue == 'shadow' ? true : false);
          }
          if (newValue || newValue === false) {
            if (optionName == 'cornerRadius') {
              newValue = Number(newValue);
            }
            options[optionName] = newValue;
          }
        });
        // css options
        $(".beautytips-css-styling .fieldset-wrapper").children('.form-item').each( function() {
          var newValue = $(this).find('input').val();
          var name = $(this).find('input').attr('name'); 
          var optionName = name.replace("custom_styles[css-styles][", "");
          optionName = optionName.replace("]", "");
          if (!options['cssStyles']) {
            options['cssStyles'] = new Array();
          }
          if (newValue || newValue === false) {
            options['cssStyles'][optionName] = newValue;
          }
        });
        options['cssClass'] = 'beautytips-fixed-beautytips';
        $("#beautytips-popup-changes").bt(popupText, options);
        btPopup.btOn(); 
        $('.beautytips-fixed-beautytips').css('position', 'fixed');
      }
      beautytipsStyleTip();

      $(".form-item").each(function() {
        $(this).change(function() {
          beautytipsStyleTip();
        });
      });
    }
  }

  /**
   * Turn an array of items into beautytips options
   */
  function beautytipsSetupDefaultOptions(themeSettings) {
    var options = new Array();

    for (var key in themeSettings) {
      if (key == 'cssStyles') {
        options['cssStyles'] = new Array();
        for (var option in themeSettings['cssStyles']) {
          options['cssStyles'][option] = themeSettings['cssStyles'][option];
        }
      }
      else {
        options[key] = themeSettings[key];
      }
    }
    options['positions'] = 'right';
    options['trigger'] = 'none';
    options['clickAnywhereToClose'] = false;
    options['closeWhenOthersOpen'] = false
    options['positions'] =  'bottom';

    return options;
  }
})(jQuery);

