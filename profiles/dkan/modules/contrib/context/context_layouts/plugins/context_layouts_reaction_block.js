
(function($) {

Drupal.behaviors.contextLayoutsReactionBlock = {};
Drupal.behaviors.contextLayoutsReactionBlock.attach = function(context) {
  // ContextBlockForm: Init.
  $('.context-blockform-layout:not(.contextLayoutsProcessed)').each(function() {
    $(this).addClass('contextLayoutsProcessed');
    $(this).change(function() {
      var layout = $(this).val();
      if (Drupal.settings.contextLayouts.layouts[layout]) {
        $('#context-blockform td.blocks').find('table, div.label, div.tabledrag-toggle-weight-wrapper').hide();
        for (var key in Drupal.settings.contextLayouts.layouts[layout]) {
          var region = Drupal.settings.contextLayouts.layouts[layout][key];
          $('.context-blockform-regionlabel-'+region).show().next('div.tabledrag-toggle-weight-wrapper').show();
          $('#context-blockform-region-'+region).show();
        }
        if (Drupal.contextBlockForm) {
          Drupal.contextBlockForm.setState();
        }
      }
    });
    $(this).change();
  });
};

})(jQuery);