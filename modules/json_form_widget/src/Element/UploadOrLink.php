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
   * Inherited.
   *
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
   * Helper function to check a url and define if it corresponds to local file.
   */
  private static function checkIfLocalFile($url) {
    $filename = \Drupal::service('file_system')->basename($url);
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['filename' => $filename]);
    if (!empty($files)) {
      return reset($files);
    }
    return FALSE;
  }

  /**
   * Render API callback: Expands the managed_file element type.
   *
   * Expands the file type to include Upload and Remove buttons, as well as
   * support for a default value.
   * TODO: update comment to reflect what this function does.
   */
  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    // If removing, unset #uri.
    $element['#uri'] = static::getDefaultUri($element, $form_state);
    // Build element.
    $element = parent::processManagedFile($element, $form_state, $complete_form);
    $file_url_type = static::getUrlType($element);

    $triggering = $form_state->getTriggeringElement();
    $button = is_array($triggering) ? array_pop($triggering['#array_parents']) : '';
    if ($button == 'remove_button') {
      unset($element['#files']);
      unset($element['file_' . reset($element['#value']['fids'])]);
      $element['#value']['fids'] = [];
    }

    // Load default value.
    if (!empty($element['#uri'])) {
      $file = static::checkIfLocalFile($element['#uri']);
      if ($file && $element['#value']['file_url_type'] !== static::TYPE_REMOTE) {
        $element['#files'][$file->id()] = $file;
        $element['#value']['fids'][] = $file->id();
        $element['#value']['file_url_type'] = "upload";
        $element['fids']['#type'] = 'hidden';
        $element['fids']['#value'][] = $file->id();
        $file_link = [
          '#theme' => 'file_link',
          '#file' => $file,
        ];
        $element['file_' . $file->id()]['filename'] = $file_link + ['#weight' => -10];
      }
    }

    $file_url_remote = isset($element['#value']['file_url_remote']) ? $element['#value']['file_url_remote'] : $element['#uri'];
    $file_url_remote_is_valid = UrlHelper::isValid($file_url_remote, TRUE);
    if ($file_url_remote_is_valid && $file_url_type == static::TYPE_REMOTE) {
      $remote_file = RemoteFile::load($file_url_remote);
      $element['#files'] = [$file_url_remote => $remote_file];
      $file_link = [
        '#type' => 'link',
        '#title' => $remote_file->getFileUri(),
        '#url' => Url::fromUri($remote_file->getFileUri()),
      ];
      $element["file_{$file_url_remote}"]['filename'] = $file_link + ['#weight' => -10];
      $element['#value']['file_url_type'] = static::TYPE_REMOTE;
      $element['#value']['file_url_remote'] = $file_url_remote;
      $element['#value']['upload'] = NULL;
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
      '#default_value' => $file_url_remote,
      // Only show this field when the 'remote' radio is selected.
      '#states' => ['visible' => $remote_visible],
      '#access' => $access_file_url_elements,
      '#weight' => 15,
    ];

    // Only show this field when the 'upload' radio is selected. Add also a
    // wrapper around file upload, so states knows what field to target.
    $selector_fids = ':input[name="' . $element['#name'] . '[fids]"]';
    $upload_visible = [
      [$selector_fids => ['empty' => FALSE]],
      'or',
      [$selector => ['value' => static::TYPE_UPLOAD]],
    ];
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
    return $element;
  }

  /**
   * Render API callback: Validates the upload_or_link element.
   */
  public static function validateManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    $uri = static::getDefaultUri($element, $form_state);
    if ($element['#value']['file_url_type'] == static::TYPE_UPLOAD || !empty($element['#value']['fids'])) {
      parent::validateManagedFile($element, $form_state, $complete_form);
      $form_state->set('upload_or_link_element', $element['#parents']);
    }
    $form_state->setValueForElement($element, $uri);
  }

  /**
   * Helper function for getting the url type.
   */
  protected static function getUrlType($element) {
    if (isset($element['#value']['file_url_type'])) {
      return $element['#value']['file_url_type'];
    }
    elseif (isset($element['#uri'])) {
      if (static::checkIfLocalFile($element['#uri'])) {
        return static::TYPE_UPLOAD;
      }
      else {
        return static::TYPE_REMOTE;
      }
    }
    return NULL;
  }

  /**
   * Helper function for getting the default URI.
   */
  protected static function getDefaultUri($element, FormStateInterface $form_state) {
    $triggering = $form_state->getTriggeringElement();
    $button = is_array($triggering) ? array_pop($triggering['#array_parents']) : '';
    if ($button == 'remove_button') {
      return '';
    }

    if ($element['#value']['file_url_type'] == static::TYPE_UPLOAD || !empty($element['#value']['fids'])) {
      return static::getLocalFileUrl($element);
    }
    elseif (!empty($element['#value']['file_url_remote'])) {
      $uri = $element['#value']['file_url_remote'];
      return $uri;
    }

    return $element['#uri'];
  }

  /**
   * Helper function to get the URL of a local file.
   */
  protected static function getLocalFileUrl($element) {
    $fids = $element['fids']['#value'];
    foreach ($fids as $fid) {
      if ($file = File::load($fid)) {
        $uri = $file->getFileUri();
        return file_create_url($uri);
      }
    }
    return $element['#uri'];
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
