<?php

namespace Drupal\Tests\dkan_common\Traits;

/**
 * Trait for getting local data for tests.
 */
trait GetLocalDataTrait {

  /**
   * Get the download URL for a file.
   *
   * @param string $filename
   *   The filename to get the download URL for. There are a number of test
   *   files available in modules/dkan_common/tests/files.
   *
   * @return string
   *   The download URL for the file.
   */
  private function getDownloadUrl(string $filename) {
    $files_dir = realpath(__DIR__ . '/../../files');
    if (!$files_dir || !is_dir($files_dir)) {
      throw new \RuntimeException("Test files directory not found relative to trait location: " . __DIR__);
    }
    return 'file://' . $files_dir . '/' . $filename;
  }

  /**
   * Generate dataset metadata, possibly with multiple distributions.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param string $title
   *   Dataset title.
   * @param array $filenames
   *   Array of resource files URLs for this dataset.
   * @param string|null $describedBy
   *   (Optional) URI for describedBy for all the download URLs. describedByType
   *   will be set to 'application/vnd.tableschema+json' if present.
   *
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getDataset(string $identifier, string $title, array $filenames, ?string $describedBy = NULL) {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = 'This & that description. <a onauxclick=prompt(document.domain)>Right click me</a>.';
    $data->identifier = $identifier;
    $data->accessLevel = "public";
    $data->modified = "06-04-2020";
    $data->keyword = ["some keyword"];
    $data->distribution = [];
    $data->publisher = (object) [
      'name' => 'Test Publisher',
    ];
    $data->contactPoint = (object) [
      'fn' => 'Test Name',
      'hasEmail' => 'test@example.com',
    ];

    foreach ($filenames as $key => $filename) {
      if (str_contains($filename, '://')) {
        $downloadUrl = $filename;
      }
      else {
        $downloadUrl = $this->getDownloadUrl($filename);
      }
      $distribution = new \stdClass();
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $downloadUrl;
      $distribution->mediaType = "text/csv";
      if ($describedBy) {
        $distribution->describedBy = $describedBy;
        $distribution->describedByType = 'application/vnd.tableschema+json';
      }

      $data->distribution[] = $distribution;
    }

    return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

  /**
   * Generate data-dictionary metadata.
   *
   * Input fields format:
   * ```php
   * [
   *   'name' => string,
   *   'title' => string,
   *   'type' => string,
   *   'format' => string,
   * ]
   * ```
   *
   * Input indexes format:
   * ```php
   * [
   *   'fields' => [
   *     'name' => string,
   *     'length' => integer,
   *   ]
   *   'type' => enum('index', 'fulltext'),
   *   'description' => string,
   * ]
   * ```
   *
   * @param array[] $fields
   *   Data-Dictionary fields.
   * @param array[] $indexes
   *   Data-Dictionary indexes.
   * @param string $identifier
   *   Data-Dictionary identifier.
   * @param string|null $title
   *   Data-Dictionary title.
   *
   * @return string|false
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getDataDictionary(array $fields, array $indexes, string $identifier, string $title = 'Test DataDict') {
    return json_encode([
      'identifier' => $identifier,
      'data' => [
        'title' => $title,
        'fields' => $fields,
        'indexes' => $indexes,
      ],
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

}
