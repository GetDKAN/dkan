(function ($) {

Drupal.behaviors.diffRevisions = {
  attach: function (context, settings) {
    var $rows = $('table.diff-revisions tbody tr');
    function updateDiffRadios() {
      var newTd = false;
      var oldTd = false;
      if (!$rows.length) {
        return true;
      }
      $rows.removeClass('selected').each(function() {
        var $row = $(this);
        $row.removeClass('selected');
        var $inputs = $row.find('input[type="radio"]');
        var $oldRadio = $inputs.filter('[name="old"]').eq(0);
        var $newRadio = $inputs.filter('[name="new"]').eq(0);
        if (!$oldRadio.length || !$newRadio.length) {
          return true;
        }
        if ($oldRadio.attr('checked')) {
          oldTd = true;
          $row.addClass('selected');
          $oldRadio.css('visibility', 'visible');
          $newRadio.css('visibility', 'hidden');
        } else if ($newRadio.attr('checked')) {
          newTd = true;
          $row.addClass('selected');
          $oldRadio.css('visibility', 'hidden');
          $newRadio.css('visibility', 'visible');
        } else {
          if (Drupal.settings.diffRevisionRadios == 'linear') {
            if (newTd && oldTd) {
              $oldRadio.css('visibility', 'visible');
              $newRadio.css('visibility', 'hidden');
            } else if (newTd) {
              $newRadio.css('visibility', 'visible');
              $oldRadio.css('visibility', 'visible');
            } else {
              $newRadio.css('visibility', 'visible');
              $oldRadio.css('visibility', 'hidden');
            }
          } else {
            $newRadio.css('visibility', 'visible');
            $oldRadio.css('visibility', 'visible');
          }
        }
      });
      return true;
    }
    if (Drupal.settings.diffRevisionRadios) {
      $rows.find('input[name="new"], input[name="old"]').click(updateDiffRadios);
      updateDiffRadios();
    }
  }
};

})(jQuery);
