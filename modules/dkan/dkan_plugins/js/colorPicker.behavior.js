
/**
 * @file
 * Add support to html 5 color component
 */
;(function ($) {
  Drupal.behaviors.colorPicker = {
    attach: function(context){
      if(typeof jQuery(".spectrum-color-picker").spectrum === 'function') {
        $(".spectrum-color-picker").spectrum({
          showInput: true,
          allowEmpty: false,
          showAlpha: true,
          showInitial: true,
          preferredFormat: "hex",
          clickoutFiresChange: true,
          showButtons: true
        });       
      }

    }
  }
})(jQuery);