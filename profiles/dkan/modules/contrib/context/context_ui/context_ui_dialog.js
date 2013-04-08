(function ($) {

  Drupal.behaviors.context_ui_dialog = {
    attach: function(context) {
      var selector = $('#context_ui_dialog-context-ui', context).not('context_ui_dialog-processed');

      if(selector) {
        selector.addClass('context_ui_dialog-processed');
        selector.detach();
        $('#page').prepend(selector);

        var labelOpen = Drupal.t('Select Context');
        var labelClose = Drupal.t('Hide');

        // Create a tab to show/hide our edit area
        var tab = $('<a href="javascript:" class="context-ui-dialog-open" title="Show Context Selector">'+labelClose+'</a>');
        selector.append(tab);

        selector.toggled = false;
        var width = $(selector).outerWidth();
        tab.click(function(e){
          if(selector.toggled) {
            selector.stop(true, false).animate({'left':0}, 400);
            selector.toggled = false;
            $(this).text(labelClose);
          } else {
            selector.stop(true, false).animate({'left':-width-4}, 400);
            selector.toggled = true;
            $(this).text(labelOpen);
          }
        });

        $('#context_ui_dialog-context-ui').show();

        // Make sure the UI is 60% of the size of the window
        var context_ui_height = Math.round(6 * $(window).height() / 10);
        var item_list_height = context_ui_height - 200;
        item_list_height = (item_list_height < 50) ? 50 : item_list_height;
        $('#context_ui_dialog-context-ui').height(context_ui_height);
        $('#context_ui_dialog-context-ui .item-list').height(item_list_height);

        // Add a class to body
        $('body').once().addClass('context-field-editor');
      }
    }
  };
})(jQuery);
