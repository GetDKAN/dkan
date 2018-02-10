/**
 * @file
 * Provides options for chart visualization.
 */

(function ($) {

  Drupal.behaviors.jsonEditor = {
    attach: function (context) {
      // Get the existing value
      var field = document.getElementById('edit-field-conformsto-schema-und-0-value');
      var json;
      try {
        json = field.value ? JSON.parse(field.value) : {};
      } catch(e) {
        console.warn('An error ocurred trying to parse the dictionary schema.');
      }
      // Insert the editor
      var container = document.getElementById('field-conformsto-schema-add-more-wrapper');
      var options = {
        mode: 'code',
        modes: ['code', 'form', 'tree']
      };
      var editor = new JSONEditor(container, options);
      // Store reference to object for easier manipulation of API
      container.jsoneditor = editor;
      editor.set(json);
      // Remove the old field
      $('.form-item-field-conformsto-schema-und-0-value .resizable-textarea').css({display: "none"});

      // Submit!
      $('#resource-node-form').submit(function( event ) {
        var json = editor.get();
        field.value = JSON.stringify(json);
      });
    }
  };

})(jQuery);
