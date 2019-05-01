<?php

namespace Drupal\dkan_harvest\Transform;

use Harvest\Transform\Transform;
use Drupal\dkan_harvest\Load\FileHelperTrait;

/**
 * Defines a transform that saves the resources from a dataset.
 */
class ResourceImporter extends Transform {

  use FileHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function run(&$datasets) {
    // Loop through datasets.
    foreach ($datasets as $dataset_key => $dataset) {
      $datasets[$dataset_key] = $this->updateDistributions($dataset);
    }
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
    foreach ($dataset->distribution as $dist_index => $dist) {
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
    $new_url = $this->saveFile($dist->downloadURL, $dataset->identifier);

    // If successful, update downloadURL.
    if ($new_url) {
      $dist->downloadURL = $new_url;
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

    $fileHelper = $this->getFileHelper();

    $fileHelper->prepareDir($targetDir);

    // Abort if file can't be saved locally.
    if (!($path = $fileHelper->retrieveFile($url, $targetDir))) {
      return FALSE;
    }

    return $fileHelper->fileCreate($path);
  }

}
