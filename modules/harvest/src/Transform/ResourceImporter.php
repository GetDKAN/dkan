<?php

namespace Drupal\harvest\Transform;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Harvest\ETL\Transform\Transform;

/**
 * Defines a transform that saves the resources from a dataset.
 *
 * @codeCoverageIgnore
 */
class ResourceImporter extends Transform {

  /**
   * Drupal files.
   *
   * @var \Drupal\common\Util\DrupalFiles
   */
  private $drupalFiles;

  /**
   * Constructor.
   */
  public function __construct($harvest_plan) {
    parent::__construct($harvest_plan);
    $this->drupalFiles = \Drupal::service('dkan.common.drupal_files');
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function run($dataset) {
    $this->testEnvironment();
    return $this->updateDistributions($dataset);
  }

  /**
   * Test Environment.
   *
   * @codeCoverageIgnore
   */
  protected function testEnvironment() {
    $setting = Settings::get('file_public_base_url');
    if (!isset($setting) || empty($setting)) {
      throw new \Exception("file_public_base_url should be set.");
    }
    return TRUE;
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
   * If this method is called when PHP is running on the CLI (e.g. via drush),
   * `$settings['file_public_base_url']` must be configured in `settings.php`,
   * otherwise 'default' will be used as the hostname in the new URL.
   *
   * @param string $url
   *   External file URL.
   * @param string $dataset_id
   *   Dataset identifier used to group resources together.
   *
   * @return string|bool
   *   The URL for the newly created file, or FALSE if failure occurs.
   */
  public function saveFile($url, $dataset_id) {

    $targetDir = 'public://distribution/' . $dataset_id;

    $this->drupalFiles->getFilesystem()->prepareDirectory($targetDir, FileSystemInterface::CREATE_DIRECTORY);

    // Abort if file can't be saved locally.
    if (!($path = $this->drupalFiles->retrieveFile($url, $targetDir))) {
      return FALSE;
    }

    if (is_object($path)) {
      return \Drupal::service('file_url_generator')->generateAbsoluteString($path->uri->value);
    }
    else {
      return $this->drupalFiles->fileCreateUrl($path);
    }
  }

}
