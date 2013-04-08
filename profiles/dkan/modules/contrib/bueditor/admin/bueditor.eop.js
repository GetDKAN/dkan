
//Add editor name prompt for copy and add_default operations.
//Confirm editor deletion without going to the confirmation page.
(function($) {

$(document).ready(function() {
  $('a.eop-copy, a.eop-add-default').click(function() {
    var name = prompt(Drupal.t('Editor name'), this.name);
    if (name) location.replace(this.href + '&name=' + name);
    return false;
  }).add('a.eop-export').removeClass('active');
  $('a.eop-delete').click(function() {
    var lnk = $(this).nextAll('a.eop-copy')[0];
    var msg = Drupal.t('Are you sure you want to delete the editor @name?', {'@name': lnk.name.substr(8)}) +'\n';
    msg += Drupal.t('All buttons and settings of this editor will be removed.') +'\n';
    msg += Drupal.t('This action cannot be undone.');
    if (confirm(msg)) {
      location.replace(lnk.href.replace('eop=copy', 'eop=delete'));
    }
    return false;
  });
});

})(jQuery);