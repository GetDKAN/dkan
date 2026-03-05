(function (Drupal) {
  Drupal.behaviors.dataPreviewPageSize = {
    attach(context) {
      context.querySelectorAll('.data-preview-page-size-select')
        .forEach(function (select) {
          if (select.dataset.processed) { return;
          }
          select.dataset.processed = '1';
          select.addEventListener('change', function () {
            window.location.href = this.value;
          });
        });
    },
  };
})(Drupal);
