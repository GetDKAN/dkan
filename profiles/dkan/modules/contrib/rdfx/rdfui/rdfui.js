(function ($) {

Drupal.behaviors.rdfuiFieldsetSummaries = {
  attach: function (context) {
    function setSummary() {
      $(this).drupalSetSummary(function (context) {
        var formValues = $(':input', context).not('[type=hidden]').map(
          function () {
            return $(this).closest('.form-item').css('display') === 'none' ? null : $(this).val()
          }
        );
        // Only show values in the vertical tab if the first
        // form element (types, predicates) is not empty.
        return !formValues[0] ? null : Drupal.checkPlain(formValues.toArray().join(' '))
      })
    }
    $('fieldset.rdf-field', context).each(setSummary);
    $(document).bind('state:visible', function () {
      Drupal.behaviors.rdfuiFieldsetSummaries.attach($(this).closest('fieldset.rdf-field')[0])
    })
  }
};

})(jQuery);
