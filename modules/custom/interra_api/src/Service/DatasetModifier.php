<?php

namespace Drupal\interra_api\Service;

/**
 * Refactor of some static methods out of the Interra API controller.
 */
class DatasetModifier {

  /**
   *
   * @param \stdClass $dataset
   * @return \stdClass
   */
  public function modifyDataset(\stdClass $dataset) {
    // @todo validate json via schema first?
    foreach ($dataset->distribution as $key => $distro) {
      $format = str_replace("text/", "", $distro->mediaType);
      if ($format === "csv") {
        $distro->format = $format;
        $dataset->distribution[$key] = $distro;
      }
      else {
        unset($dataset->distribution[$key]);
      }
    }

    if (isset($dataset->theme) && is_array($dataset->theme)) {
      $dataset->theme = $this->objectifyStringsArray($dataset->theme);
    }

    if (isset($dataset->keyword) && is_array($dataset->keyword)) {
      $dataset->keyword = $this->objectifyStringsArray($dataset->keyword);
    }

    return $dataset;
  }

  /**
   *
   */
  public function objectifyStringsArray(array $array) {
    $objects = [];
    foreach ($array as $string) {

      // @todo identifier is not immune to collisions.
      //   consider using https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Html.php/function/Html%3A%3AcleanCssIdentifier/8.6.x
      //       or some kind of hash.
      $identifier = strtolower(str_replace(" ", "", $string));

      $objects[] = (object) ['identifier' => $identifier, 'title' => $string];
    }

    return $objects;
  }

}
