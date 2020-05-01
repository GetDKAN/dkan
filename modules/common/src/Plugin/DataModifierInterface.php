<?php

namespace Drupal\common\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Data modifier plugins.
 *
 * Plugins of this type may have different conditions and outcomes, but all act
 * on the following publicly accessible API endpoints:
 *   - The metastore's GET collection and GET item
 *   - The dataset-specific Api Docs
 *   - The datastore's SQL query.
 */
interface DataModifierInterface extends PluginInspectionInterface {

  /**
   * Checks if the schema or data needs modifying.
   *
   * @param string $schema
   *   The schema id.
   * @param mixed $data
   *   The data object, json string or identifier.
   *
   * @return bool
   *   TRUE if the data requires modification, FALSE otherwise.
   */
  public function requiresModification(string $schema, $data);

  /**
   * Modify data.
   *
   * @param string $schema
   *   The schema id.
   * @param mixed $data
   *   The object, json string or identifier whose data needs modifying.
   *
   * @return mixed
   *   The modified data object, or FALSE.
   */
  public function modify(string $schema, $data);

  /**
   * Translate and render the result annotation.
   *
   * @return string
   *   A message explaining the outcome.
   */
  public function message() : string;

  /**
   * Return the http code annotation.
   *
   * @return int
   *   The http code.
   */
  public function httpCode() : int;

}
