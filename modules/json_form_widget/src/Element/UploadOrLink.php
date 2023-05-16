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
 * @codeCoverageIgnore
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
   *
   * @codeCoverageIgnore
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [[$class, 'processManagedFile']],
      '#element_validate' => [[$class, 'validateManagedFile']],
      '#pre_render' => [[$class, 'preRenderManagedFile']],
      '#theme' => 'file_managed_file',
      '#theme_wrappers' => ['form_element'],
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
   * Expands file_managed type to include option for links to remote files/urls.
   */
  public static function processManagedFile(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#uri'] = static::getDefaultUri($element, $form_state);
    // Build element.
    $element = parent::processManagedFile($element, $form_state, $complete_form);
    $file_url_type = static::getUrlType($element);
    $element = static::unsetFilesWhenRemoving($form_state->getTriggeringElement(), $element);

    $file_url_remote = isset($element['#value']['file_url_remote']) ? $element['#value']['file_url_remote'] : $element['#uri'];
    $file_url_remote_is_valid = isset($file_url_remote) && UrlHelper::isValid($file_url_remote, TRUE);
    $is_remote = $file_url_remote_is_valid && $file_url_type == static::TYPE_REMOTE;
    if ($is_remote) {
      $element = static::loadRemoteFile($element, $file_url_remote);
    }

    $access_file_url_elements = (empty($element['#files']) && !$file_url_remote_is_valid) || !$file_url_type;
    $file_url_type_selector = ':input[name="' . $element['#name'] . '[file_url_type]"]';
    $remote_visible = [$file_url_type_selector => ['value' => static::TYPE_REMOTE]];

    $element['file_url_type'] = static::getFileUrlTypeElement($file_url_type, $access_file_url_elements);
    $element['file_url_remote'] = static::getFileUrlRemoteElement($file_url_remote, $access_file_url_elements, $remote_visible);
    $element = static::overrideUploadSubfield($element, $file_url_type_selector);

    return $element;
  }

  /**
   * Return file_url_type element.
   */
  private static function getFileUrlTypeElement($file_url_type, $access_file_url_elements) {
    return [
      '#type' => 'radios',
      '#options' => [
        static::TYPE_UPLOAD => t('Upload Data File'),
        static::TYPE_REMOTE => t('Link to Data File'),
      ],
      '#default_value' => $file_url_type,
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
      '#access' => $access_file_url_elements,
      '#weight' => 5,
    ];
  }

  /**
   * Return file_url_remote element.
   */
  private static function getFileUrlRemoteElement($file_url_remote, $access_file_url_elements, $remote_visible) {
    return [
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
  }

  /**
   * Helper function to return element without files when removing.
   */
  private static function unsetFilesWhenRemoving($triggering_element, $element) {
    $button = is_array($triggering_element) ? array_pop($triggering_element['#array_parents']) : '';
    if ($button == 'remove_button') {
      unset($element['#files']);
      $element = static::unsetFids($element);
    }
    return $element;
  }

  /**
   * Helper function to unsetFids.
   */
  private static function unsetFids($element) {
    foreach ($element['#value']['fids'] as $fid) {
      unset($element['file_' . $fid]);
    }
    $element['#value']['fids'] = [];
    return $element;
  }

  /**
   * Load remote file into element.
   */
  private static function loadRemoteFile($element, $file_url_remote) {
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
    return $element;
  }

  /**
   * Helper function to override upload subelement.
   */
  private static function overrideUploadSubfield($element, $file_url_type_selector) {
    // Only show this field when the 'upload' radio is selected. Add also a
    // wrapper around file upload, so states knows what field to target.
    $selector_fids = ':input[name="' . $element['#name'] . '[fids]"]';
    $upload_visible = [
      [$selector_fids => ['empty' => FALSE]],
      'or',
      [$file_url_type_selector => ['value' => static::TYPE_UPLOAD]],
    ];
    $element['upload']['#states']['visible'] = $upload_visible;
    $element['upload']['#theme_wrappers'][] = 'form_element';
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
    if (static::getUrlType($element) === static::TYPE_UPLOAD) {
      parent::validateManagedFile($element, $form_state, $complete_form);
      if ($element_parents = $form_state->get('upload_or_link_element')) {
        $element_parents[] = $element['#parents'];
        $form_state->set('upload_or_link_element', $element_parents);
      }
      else {
        $form_state->set('upload_or_link_element', [$element['#parents']]);
      }
    }
    $form_state->setValueForElement($element, $uri);
  }

  /**
   * Helper function for getting the url type.
   */
  protected static function getUrlType($element) {
    $type = static::TYPE_REMOTE;
    if (isset($element['#value']['file_url_type'])) {
      $type = $element['#value']['file_url_type'];
    }
    elseif (!empty($element['#value']['fids'])) {
      $type = static::TYPE_UPLOAD;
    }
    return $type;
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

    if (static::getUrlType($element) == static::TYPE_UPLOAD) {
      return static::getLocalFileUrl($element);
    }
    elseif (!empty($element['#value']['file_url_remote'])) {
      $uri = $element['#value']['file_url_remote'];
      return $uri;
    }

    return isset($element['#uri']) ? $element['#uri'] : NULL;
  }

  /**
   * Helper function to get the URL of a local file.
   */
  protected static function getLocalFileUrl($element) {
    $fids = $element['#value']['fids'];
    foreach ($fids as $fid) {
      if ($file = File::load($fid)) {
        $uri = $file->getFileUri();
        return \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
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
