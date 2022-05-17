<?php

namespace Drupal\Tests\common\Traits;

trait GetDataTrait {

  private $S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';
  private $FILENAME_PREFIX = 'dkan_default_content_files_s3_amazonaws_com_phpunit_';

  private function getDownloadUrl(string $filename) {
    return $this->S3_PREFIX . '/' . $filename;
  }

  /**
   * @param string $filename
   *   Resource filename.
   *
   * @return string
   *   File path.
   */
  protected function getLocalFilePath(string $filename): string {
    return 'file://' . __DIR__ . '/../../data/' . $filename;
  }

  /**
   * Generate dataset metadata, possibly with multiple distributions.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param string $title
   *   Dataset title.
   * @param array $downloadUrls
   *   Array of resource files URLs for this dataset.
   * @param bool $localFiles
   *   Whether the resource files are local.
   *
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getDataset(string $identifier, string $title, array $downloadUrls, bool $localFiles = FALSE) {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = "Some description.";
    $data->identifier = $identifier;
    $data->accessLevel = "public";
    $data->modified = "06-04-2020";
    $data->keyword = ["some keyword"];
    $data->distribution = [];

    foreach ($downloadUrls as $key => $downloadUrl) {
      $distribution = new \stdClass();
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $localFiles ? $this->getLocalFilePath($downloadUrl) : $this->getDownloadUrl($downloadUrl);
      $distribution->mediaType = "text/csv";

      $data->distribution[] = $distribution;
    }

    return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

  /**
   * Generate data-dictionary metadata.
   *
   * @param array[] $fields
   *   Data-Dictionary fields.
   * @param string $identifier
   *   Data-Dictionary identifier.
   * @param string|null $title
   *   Data-Dictionary title.
   *
   * Input fields format:
   * ```php
   * [
   *   'name' => 'string',
   *   'title' => 'string',
   *   'type' => 'string',
   *   'format' => 'string'
   * ]
   * ```
   *
   *
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getDataDictionary(array $fields, string $identifier, string $title = 'Test DataDict') {
    return json_encode([
      'identifier' => $identifier,
      'title' => $title,
      'data' => [
        'fields' => $fields,
      ],
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

}
