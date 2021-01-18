/**
 * @file
 * Improves the remote URL text field.
 */

(function ($, Drupal) {

  'use strict';

  // @todo Add remote URL client validation.

  /**
   * Attach behaviors to the file URL auto add URL.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches triggers for the remote URL addition.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches triggers for the remote URL addition.
   */
  Drupal.behaviors.fileUrlRemoteUrlAdd = {
    attach: function (context) {
      $(context).find('input[data-drupal-file-url-remote]').once('auto-remote-url-add').on('change.autoRemoteUrlAdd', Drupal.file.triggerUploadButton);
    },
    detach: function (context, setting, trigger) {
      if (trigger === 'unload') {
        $(context).find('input[data-drupal-file-url-remote]').removeOnce('auto-remote-url-add').off('.autoRemoteUrlAdd');
      }
    }
  };

})(jQuery, Drupal);
