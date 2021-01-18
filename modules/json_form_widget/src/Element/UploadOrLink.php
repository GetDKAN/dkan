<?php

namespace Drupal\json_form_widget\Element;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Element\ManagedFile;
use Drupal\file\Entity\File;
use Drupal\json_form_widget\Entity\RemoteFile;

/**
 * Provides a new Element for uploading or linking to files.
 *
 * @FormElement("upload_or_link")
 */
class UploadOrLink extends ManagedFile {

  /**
   * File URL item type: file upload.
   */
  const TYPE_UPLOAD = 'upload';

  /**
   * File URL item type: URL to remote file..
   */
  const TYPE_REMOTE = 'remote';

  /**
   * {@inheritDoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processManagedFile'],
      ],
      '#element_validate' => [
        [$class, 'validateManagedFile'],
      ],
      '#pre_render' => [
        [$class, 'preRenderManagedFile'],
      ],
      '#theme' => 'file_managed_file',
      '#theme_wrappers' => ['form_element'],
      '#progress_indicator' => 'throbber',
      '#progress_message' => NULL,
      '#upload_validators' => [],
      '#upload_location' => NULL,
      '#size' => 22,
      '#multiple' => FALSE,
      '#extended' => FALSE,
      '#attached' => [
        'library' => ['file/drupal.file'],
      ],
      '#accept' => NULL,
    ];
  }

  /**
   * Render API callback: Expands the managed_file element type.
   *
   * Expands the file type to include Upload and Remove buttons, as well as
   * support for a default value.
   * TODO: update comment to reflect what this function does.
   */
  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    // ksm($element, 'start');
    $element = parent::processManagedFile($element, $form_state, $form);
    $file_url_type = static::getUrlType($element);
    // $file_url_type = isset($element['#value']['file_url_type']) ? $element['#value']['file_url_type'] : NULL;
    $file_url_remote = $element['#value']['file_url_remote'];
    $file_url_remote_is_valid = UrlHelper::isValid($file_url_remote, TRUE);
    if ($file_url_remote_is_valid && $file_url_type) {
      $remote_file = RemoteFile::load($file_url_remote);
      $element['#files'] = [$file_url_remote => $remote_file];
      $file_link = [
        '#type' => 'link',
        '#title' => $remote_file->getFileUri(),
        '#url' => Url::fromUri($remote_file->getFileUri()),
      ];
      if ($element['#multiple']) {
        $element["file_{$file_url_remote}"]['selected'] = [
          '#type' => 'checkbox',
          '#title' => \Drupal::service('renderer')->renderPlain($file_link),
        ];
      }
      else {
        $element["file_{$file_url_remote}"]['filename'] = $file_link + ['#weight' => -10];
      }
    }
    $access_file_url_elements = (empty($element['#files']) && !$file_url_remote_is_valid) || !$file_url_type;

    // Build the file URL additional sub-elements.
    $element['file_url_type'] = [
      '#type' => 'radios',
      '#options' => [
        static::TYPE_UPLOAD => t('Upload file'),
        static::TYPE_REMOTE => t('Remote file URL'),
      ],
      '#default_value' => $file_url_type,
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
      '#access' => $access_file_url_elements,
      '#weight' => 5,
    ];

    $selector = ':input[name="' . $element['#name'] . '[file_url_type]"]';
    $remote_visible = [$selector => ['value' => static::TYPE_REMOTE]];
    $element['file_url_remote'] = [
      '#type' => 'url',
      '#title' => t('Remote URL'),
      '#title_display' => 'invisible',
      '#description' => t('This must be an external URL such as <em>http://example.com</em>.'),
      // '#default_value' => isset($element['#default_value']) ? $element['#default_value'] : $file_url_remote,
      '#default_value' => $file_url_remote,
      // Only show this field when the 'remote' radio is selected.
      '#states' => ['visible' => $remote_visible],
      '#attached' => [
        // Load the JS functionality that triggers automatically the 'Upload'
        // button when a remote URL is entered.
        'library' => ['json_form_widget/remote_url'],
      ],
      '#attributes' => [
        // Used by 'file_url/remote_url' library identify the text field.
        'data-drupal-file-url-remote' => TRUE,
      ],
      '#access' => $access_file_url_elements,
      '#weight' => 15,
    ];

    // Only show this field when the 'upload' radio is selected. Add also a
    // wrapper around file upload, so states knows what field to target.
    $upload_visible = [$selector => ['value' => static::TYPE_UPLOAD]];
    $element['upload']['#states']['visible'] = $upload_visible;
    $element['upload']['#theme_wrappers'][] = 'form_element';
    // The upload instructions are added directly to the file upload element.
    $element['upload']['#description'] = [
      '#theme' => 'file_upload_help',
      '#description' => '',
      '#upload_validators' => $element['#upload_validators'],
    ];
    $element['upload']['#weight'] = 10;

    // Make sure the upload button is the last in form element.
    $element['upload_button']['#weight'] = 20;
    ksm($element, 'end');
    return $element;
  }

  /**
   * Render API callback: Validates the upload_or_link element.
   */
  public static function validateManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    $uri = NULL;
    if (!empty($element['#value']['fids'])) {
      parent::validateManagedFile($element, $form_state, $complete_form);
      $fids = $element['fids']['#value'];
      foreach ($fids as $fid) {
        if ($file = File::load($fid)) {
          $uri = $file->getFileUri();
        }
      }
    }
    else {
      $uri = $element['#value']['file_url_remote'];
    }
    $form_state->setValueForElement($element, $uri);
  }

  /**
   * Helper function for getting the url type.
   */
  protected static function getUrlType($element) {
    if (isset($element['#default_value'])) {
      $uri = $element['#default_value'];
      if (substr_count($uri, "http://") > 0 || substr_count($uri, "https://") > 0) {
        return static::TYPE_REMOTE;
      }
      else {
        return static::TYPE_UPLOAD;
      }
    }
    return isset($element['#value']['file_url_type']) ? $element['#value']['file_url_type'] : NULL;
  }

  /**
   * Helper function to know if user should have access to url type subfield.
   */
  protected static function getUrlTypeAccess($element) {
    if (isset($element['#default_value'])) {
      $uri = $element['#default_value'];
      if (substr_count($uri, "http://") > 0 || substr_count($uri, "https://") > 0) {
        return static::TYPE_REMOTE;
      } else {
        return static::TYPE_UPLOAD;
      }
    }
    return isset($element['#value']['file_url_type']) ? $element['#value']['file_url_type'] : NULL;
  }

  /**
   * Render API callback: Hides display of the upload or remove controls.
   *
   * Upload controls are hidden when a file is already uploaded. Remove controls
   * are hidden when there is no file attached. Controls are hidden here instead
   * of in \Drupal\file\Element\ManagedFile::processManagedFile(), because
   * #access for these buttons depends on the managed_file element's #value. See
   * the documentation of \Drupal\Core\Form\FormBuilderInterface::doBuildForm()
   * for more detailed information about the relationship between #process,
   * #value, and #access.
   *
   * Because #access is set here, it affects display only and does not prevent
   * JavaScript or other untrusted code from submitting the form as though
   * access were enabled. The form processing functions for these elements
   * should not assume that the buttons can't be "clicked" just because they are
   * not displayed.
   *
   * @see \Drupal\file\Element\ManagedFile::processManagedFile()
   * @see \Drupal\Core\Form\FormBuilderInterface::doBuildForm()
   */
  public static function preRenderManagedFile($element) {
    // If we already have a file, we don't want to show the upload controls.
    if (!empty($element['#value']['fids'])) {
      if (!$element['#multiple']) {
        $element['upload']['#access'] = FALSE;
        $element['upload_button']['#access'] = FALSE;
      }
    }
    // If we don't already have a file, there is nothing to remove.
    elseif (empty($element['#value']['file_url_remote'])) {
      $element['remove_button']['#access'] = FALSE;
    }
    return $element;
  }

}
