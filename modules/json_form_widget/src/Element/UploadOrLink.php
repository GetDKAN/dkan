<?php

namespace Drupal\json_form_widget\Element;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\Element\ManagedFile;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;

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
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function getInfo() {
    $class = static::class;
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
   * {@inheritDoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // If the input is empty, return the default value.
    $input = $input === FALSE ? [] : $input;

    // Detect whether the remove button was clicked.
    $remove = FALSE;
    $file_remove = $form_state->get('file_remove') ?? [];
    // Make sure the element set for removal is the same as current.
    $diff = array_diff($file_remove, $element['#array_parents']);
    if (!empty($file_remove) && empty($diff) && empty($input['file_url_remote'])) {
      $remove = TRUE;
    }

    $uri = $input['file_url_remote'] ?? $element['#uri'] ?? FALSE;

    if (empty($input['fids']) && $uri) {
      $file = static::getManagedFile(static::getFileUri($uri));
      // If remove was clicked, we need to unset the uri. If not, we need to add
      // the fid to the input array.
      if ($remove) {
        $element['#uri'] = '';
        $input['file_url_remote'] = '';
      }
      else {
        // Add file ID to input array and update the entity.
        $fo = $form_state->getFormObject();
        $entity = $fo instanceof EntityFormInterface ? $fo->getEntity() : NULL;
        $input['fids'] = static::updateFile($file, $entity) ?? NULL;
      }
    }

    return parent::valueCallback($element, $input, $form_state);
  }

  /**
   * Retrieve or create a file entity based on a URI.
   *
   * @param string $uri
   *   The URI of the file.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file entity or NULL if not found or able to create.
   */
  public static function getManagedFile(string $uri): ?FileInterface {
    $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
    if (!empty($files)) {
      // If a file entity already exists, return it.
      return reset($files);
    }

    // If no File entity matches the URI, create one.
    if ($file = File::create([
      'uri' => $uri,
      'status' => File::STATUS_PERMANENT,
      'uid' => \Drupal::currentUser()->id(),
    ])) {
      $file->save();
      return $file;
    }
    return NULL;
  }

  /**
   * Generate a Drupal internal URI from an absolute URL in the widget.
   *
   * This lets absolute URLs to local files be used correctly. Ideally, the
   * JSON would simply contain public:// URLs, but this is not always the case.
   */
  public static function getFileUri(string $url): string {
    $path = urldecode((string) \Drupal::service('file_url_generator')->transformRelative($url));
    if (strpos($path, '/') !== 0) {
      return $path;
    }
    // We're loading scheme from config, but this will probably break if not
    // "public".
    $scheme = \Drupal::config('system.file')->get('default_scheme') . "://";
    $scheme_path = \Drupal::service('file_url_generator')->generateString($scheme);
    $uri = str_replace($scheme_path, $scheme, $path, $count);

    return $count ? $uri : $path;
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

    $file_url_remote = $element['#value']['file_url_remote'] ?? $element['#uri'];
    $file_url_remote_is_valid = isset($file_url_remote) && UrlHelper::isValid($file_url_remote, TRUE);

    $access_file_url_elements = (empty($element['#files']) && !$file_url_remote_is_valid) || !$file_url_type;
    $element['#uri'] = $element['#uri'] ?? $file_url_remote;

    $file_url_type_selector = ':input[name="' . $element['#name'] . '[file_url_type]"]';
    $remote_visible = [$file_url_type_selector => ['value' => static::TYPE_REMOTE]];

    $element['file_url_type'] = static::getFileUrlTypeElement($file_url_type, $access_file_url_elements);
    $element['file_url_remote'] = static::getFileUrlRemoteElement($file_url_remote, $access_file_url_elements, $remote_visible);
    $element = static::overrideUploadSubfield($element, $file_url_type_selector);

    if (!empty($element['remove_button'])) {
      $element['remove_button']['#submit'][] = [static::class, 'removeSubmit'];
    }

    return $element;
  }

  /**
   * Return file_url_type element.
   */
  private static function getFileUrlTypeElement($file_url_type, $access_file_url_elements) {
    return [
      '#type' => 'radios',
      '#options' => [
        static::TYPE_UPLOAD => new TranslatableMarkup('Upload Data File'),
        static::TYPE_REMOTE => new TranslatableMarkup('Link to Data File'),
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
      '#title' => new TranslatableMarkup('Remote URL'),
      '#title_display' => 'invisible',
      '#description' => new TranslatableMarkup('This must be an external URL such as <em>http://example.com</em>.'),
      '#default_value' => $file_url_remote,
      // Only show this field when the 'remote' radio is selected.
      '#states' => ['visible' => $remote_visible],
      '#access' => $access_file_url_elements,
      '#weight' => 15,
    ];
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
      return $element['#value']['file_url_remote'];
    }

    return $element['#uri'] ?? NULL;
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
    return $element['#uri'] ?? NULL;
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

  /**
   * Submit handler for uploaded elements on upload_or_link.
   *
   * Sets up file entities created by upload element.
   */
  public static function submit(array $form, FormStateInterface $form_state) {
    $parents = $form_state->get('upload_or_link_element');
    if (empty($parents)) {
      return;
    }

    // Get attached entity if present.
    $fo = $form_state->getFormObject();
    $entity = $fo instanceof EntityFormInterface ? $fo->getEntity() : NULL;

    // Avoid double-processing if URL is duplicated in form object.
    $urls = [];
    foreach ($parents as $parent) {
      $urls[] = $form_state->getValue($parent);
    }
    $urls = array_unique(array_filter($urls));
    foreach ($urls as $url) {
      $uri = static::getFileUri($url);
      $file = static::getManagedFile($uri);
      static::updateFile($file, $entity);
    }
  }

  /**
   * Submit handler for remove button.
   */
  public static function removeSubmit(array $form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#array_parents'];
    $button_key = array_pop($parents);
    if (($button_key) == 'remove_button') {
      $form_state->set('file_remove', $parents);
    }
  }

  /**
   * Find recently-uploaded file entity, set to permanent and add usage.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity to update.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The entity to which the file is attached.
   */
  public static function updateFile(FileInterface $file, ?EntityInterface $entity): ?int {
    if (!$file) {
      return NULL;
    }

    $file->setPermanent();
    $file->save();

    // If we're working with an entity form, set up usage.
    if ($entity) {
      $fu = \Drupal::service('file.usage');
      /** @var Drupal\file\FileUsage\FileUsageInterface $fu */
      $usage = $fu->listUsage($file);
      // If the file is already used by this entity, don't add usage again.
      if (!$entity->isNew() && !isset($usage['json_form_widget'][$entity->getEntityTypeId()][$entity->id()])) {
        $fu->add($file, 'json_form_widget', $entity->getEntityTypeId(), $entity->id());
      }
    }
    return $file->id();
  }

}
