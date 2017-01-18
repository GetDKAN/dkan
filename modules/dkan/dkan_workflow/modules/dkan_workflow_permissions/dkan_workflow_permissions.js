/**
 * @file
 */

(function ($) {
    Drupal.behaviors.checkbox = {
        // Enforce role pairings defined in dkan_workflow_permissions_form_user_profile_form_alter()
        attach: function (context, settings) {
            var rolePairings = settings.dkan_workflow_permissions.rolePairings;
            var checkboxClass = settings.dkan_workflow_permissions.checkboxClass;
            // When we load the page, disable any checkboxes that are dependents for paired roles.
            for (dependee in rolePairings) {
                if (rolePairings.hasOwnProperty(dependee)) {
                    if (roleIsChecked(dependee) && roleIsChecked(rolePairings[dependee])) {
                        checkAndDisable(rolePairings[dependee]);
                    }
                }
            }
            $(checkboxClass).find('.form-checkbox').on('click', function (event) {
                var clickedRole = $(this).val();
                var isChecked = $(this)[0].checked;
                if (clickedRole in rolePairings) {
                    if (isChecked) {
                        checkAndDisable(rolePairings[clickedRole]);
                    }
                    else {
                        unCheckAndEnable(rolePairings[clickedRole])
                    }
                }
            });

            // Is the checkbox for this role id checked?
            function roleIsChecked(rid) {
                return $(checkboxClass + '-' + rid).prop("checked");
            }
            // Check and disable the checkbox for a given role id.
            function checkAndDisable(rid) {
                $(checkboxClass + '-' + rid).prop('checked', true)
                    .prop("disabled", true)
                    // Make sure the form element is enabled when the form is submitted.
                    // @todo Stop from accumulating multiple listeners if user clicks more than once
                    .closest("form").on('submit', function (e) {
                        $(checkboxClass + '-' + rid).prop("disabled", false);
                    });
            }
            // Un-check and enable the checkbox for a given role id.
            function unCheckAndEnable(rid) {
                $(checkboxClass + '-' + rid).prop('checked', false)
                    .prop("disabled", false);
            }
        }
    }
})(jQuery);
