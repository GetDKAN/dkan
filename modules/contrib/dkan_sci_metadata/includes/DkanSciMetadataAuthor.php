<?php

/**
 * Class DkanSciMetadataAuthor
 *
 * Abstract class for Author validation / helper functions.
 */
abstract class DkanSciMetadataAuthor {
  const AUTHOR_BUNDLE = 'sci_author';

  /**
   * DkanSciMetadataAuthor constructor.
   * @throws \Exception
   */
  public function __construct() {
    if (!defined('static::AUTHOR_ID_TYPE')) {
      throw new Exception('Constant AUTHOR_ID_TYPE is not defined on subclass ' . get_class($this));
    }

    if (!defined('static::AUTHOR_TAXONOMY_NAME')) {
      throw new Exception('Constant AUTHOR_TAXONOMY_NAME is not defined on subclass ' . get_class($this));
    }
  }

  /**
   * Validate Author ID.
   *
   * @param string $id
   *   ID to validate.
   *
   * @return mixed
   *   NULL if valid, error message string otherwise.
   */
  abstract public static function validate($id);
}
