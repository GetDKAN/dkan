(function ($) {
  Drupal.behaviors.colorPicker = {
    attach: function(context){
			var colorpickerInput = $("#edit-field-topic-icon-color-und-0-rgb");
		  colorpickerInput.spectrum({
		    showInput: true,
		    allowEmpty:true,
		    showInitial: true,
		    preferredFormat: "hex",
		  });
    }
  }
})(jQuery);
