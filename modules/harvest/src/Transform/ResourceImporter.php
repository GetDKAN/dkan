<?php

namespace Drupal\harvest\Transform;

use Drupal\common\Util\DrupalFiles;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\harvest\ETL\Transform\Transform;

/**
 * Moves local files to public:// and alters the downloadUrl field.
 *
 * Used by the sample_content harvest.
 *
 * @see modules/sample_content/harvest_plan.json
 */
class ResourceImporter extends Transform {

  /**
   * DKAN's Drupal files service.
   */
  private DrupalFiles $drupalFiles;

  /**
   * File URL generator service.
   */
  private FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Drupal's file system service.
   */
  private FileSystemInterface $fileSystem;

  /**
   * Constructor.
   */
  public function __construct($harvest_plan) {
    parent::__construct($harvest_plan);
    $this->drupalFiles = \Drupal::service('dkan.common.drupal_files');
    $this->fileUrlGenerator = \Drupal::service('file_url_generator');
    $this->fileSystem = \Drupal::service('file_system');
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function run($item) {
    return $this->updateDistributions($item);
  }

  /**
   * Update the distributions attached to a dataset.
   *
   * @param object $dataset
   *   JSON decoded dataset.
   *
   * @return object
   *   The original dataset with updated distributions.
   */
  protected function updateDistributions($dataset) {
    // Abort if there's no distributions.
    if (empty($dataset->distribution)) {
      return $dataset;
    }

    $distributions = [];

    // Loop through distributions.
    foreach ($dataset->distribution as $dist) {
      $distributions[] = $this->updateDownloadUrl($dataset, $dist);
    }

    // Update distributions.
    $dataset->distribution = $distributions;

    return $dataset;
  }

  /**
   * Attempt to import distribution file and update downloadURL property.
   *
   * @param object $dataset
   *   JSON decoded dataset.
   * @param object $dist
   *   JSON decoded distribution.
   *
   * @return object
   *   The updated distribution.
   */
  protected function updateDownloadUrl($dataset, $dist) {
    // Abort if there's no downloadURL property.
    if (empty($dist->downloadURL)) {
      return $dist;
    }

    // Import distribution file.
    $newUrl = $this->saveFile($dist->downloadURL, $dataset->identifier);

    // If successful, update downloadURL.
    if ($newUrl) {
      $dist->downloadURL = $newUrl;
    }

    return $dist;

  }

  /**
   * Pulls down external file and saves it locally.
   *
   * @param string $url
   *   External file URL.
   * @param string $dataset_id
   *   Dataset identifier used to group resources together.
   *
   * @return string|bool
   *   The URL for the newly created file, or FALSE if failure occurs.
   */
  public function saveFile(string $url, string $dataset_id) {
    $targetDir = 'public://distribution/' . $dataset_id;

    $this->fileSystem
      ->prepareDirectory($targetDir, FileSystemInterface::CREATE_DIRECTORY);

    // Abort if file can't be saved locally.
    if (!($path = $this->drupalFiles->retrieveFile($url, $targetDir))) {
      return FALSE;
    }

    if (is_object($path)) {
      return $this->fileUrlGenerator->generateAbsoluteString($path->uri->value);
    }
    return $this->drupalFiles->fileCreateUrl($path);
  }

}
