/**
 * @file
 * Provides options for chart visualization.
 */

(function ($) {

  Drupal.behaviors.jsonEditor = {
    attach: function (context) {
      var field = document.getElementById('edit-field-conformsto-schema-und-0-value');
      var json;
      try {
        json = field.value ? JSON.parse(field.value) : {};
      } catch(e) {
        console.warn('An error ocurred trying to parse the dictionary schema.');
      }
      var container = document.getElementById('field-conformsto-schema-add-more-wrapper');
      var options = {
        mode: 'code',
        modes: ['code', 'form', 'tree']
      };
      var editor = new JSONEditor(container, options);
      container.jsoneditor = editor;
      editor.set(json);
      $('.form-item-field-conformsto-schema-und-0-value .resizable-textarea').css({display: "none"});

      $('#resource-node-form').submit(function( event ) {
        var json = editor.get();
        field.value = JSON.stringify(json);
      });
    }
  };

})(jQuery);
