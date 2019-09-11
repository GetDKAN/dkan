<?php

namespace Drupal\dkan_lunr\Service;

/**
 * Refactor of some static methods out of the API controller.
 *
 * @codeCoverageIgnore
 */
class DatasetModifier {

  /**
   * Modify Dataset.
   *
   * @param object $dataset
   *   Dataset.
   *
   * @return object
   *   Object.
   */
  public function modifyDataset($dataset) {
    // @todo validate json via schema first?
    $only_csvs = array_filter($dataset->distribution, [$this, "isCsv"]);
    $dataset->distribution = array_map([$this, "addFormat"], $only_csvs);

    if (isset($dataset->theme) && is_array($dataset->theme)) {
      $dataset->theme = $this->objectifyStringsArray($dataset->theme);
    }

    if (isset($dataset->keyword) && is_array($dataset->keyword)) {
      $dataset->keyword = $this->objectifyStringsArray($dataset->keyword);
    }

    return $dataset;
  }

  /**
   * Private.
   */
  private function isCsv($distribution) {
    return $this->getFormat($distribution) === "csv";
  }

  /**
   * Private.
   */
  private function addFormat($distribution) {
    $distribution->format = $this->getFormat($distribution);
    return $distribution;
  }

  /**
   * Private.
   */
  private function getFormat($distribution) {
    return str_replace("text/", "", $distribution->mediaType);
  }

  /**
   * Public.
   */
  public function objectifyStringsArray(array $array) {
    $objects = [];
    foreach ($array as $string) {

      /* @todo identifier is not immune to collisions.
       *
       * Consider using https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Html.php/function/Html%3A%3AcleanCssIdentifier/8.6.x
       * or some kind of hash. */
      $identifier = strtolower(str_replace(" ", "", $string));

      $objects[] = (object) ['identifier' => $identifier, 'title' => $string];
    }

    return $objects;
  }

}
