/**
 * @file
 * Provides options for chart visualization.
 */

(function ($) {

  Drupal.behaviors.jsonEditor = {
    attach: function (context, settings) {
      // Get the existing value
      var containerId = '#field-describedby-schema-add-more-wrapper';
      var field = document.getElementById('edit-field-describedby-schema-und-0-value');
      var json;
      try {
        json = field.value ? JSON.parse(field.value) : {};
      } catch(e) {
        console.warn('An error ocurred trying to parse the dictionary schema.');
      }

      // Insert the editor
      $(containerId, context).once(function(){
        var options = {
          mode: 'code',
          modes: ['code', 'form', 'tree']
        };
        var editor = new JSONEditor(this, options);
        // Store reference to object for easier manipulation of API.
        this.jsoneditor = editor;
        editor.set(json);

        // Hide the original field.
        $('.form-item-field-describedby-schema-und-0-value .resizable-textarea').css({display: "none"});
      });

      // Submit!
      $('#resource-node-form').submit(function( event ) {
        var json = document.querySelector(containerId).jsoneditor.get();
        field.value = JSON.stringify(json);
      });

    }
  };

})(jQuery);
