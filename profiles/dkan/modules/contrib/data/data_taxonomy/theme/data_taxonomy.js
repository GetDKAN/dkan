Drupal.behaviors.data_taxonomy = function(context) {
  $('div.data-taxonomy-tagging-form:not(.data-taxonomy-processed)')
    .addClass('data-taxonomy-processed')
    .each(function() {
      var tagging_form = $(this);
      $('a.data-taxonomy-edit', this).click(function() {
        $(tagging_form).addClass('data-taxonomy-editing');
        $('input.form-text', tagging_form).focus();
        return false;
      });
      $('input.form-text', this).keypress(function(event) {
        // Detect enter key.
        if (event.keyCode == 13) {
          var selected = $('.selected', $(this).siblings('#autocomplete:has(.selected)'));
          if (selected.size() > 0) {
            $(this).val(selected.get(0).autocompleteValue);
          }
          $(this).blur();
          return false;
        }
      });
      $('input.form-text', this).blur(function() {
        $(tagging_form).removeClass('data-taxonomy-editing');
        $('ul.data-taxonomy-tags', tagging_form).addClass('data-taxonomy-edited');
        $('input.form-submit', tagging_form).mousedown();
      });
      $('input.form-submit', this).mousedown(function() {
        $(tagging_form).removeClass('data-taxonomy-editing');
        $('ul.data-taxonomy-tags', tagging_form).addClass('data-taxonomy-edited');
      });
    });
};
