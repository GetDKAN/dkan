<?php

namespace Drupal\harvest\Load;

/**
 * Helper to wrap drupal filesystem functions.
 *
 * Sideloaded as a trait for convenience.
 */
trait FileHelperTrait {

  /**
   * Private.
   *
   * @return IFileHelper
   *   FileHelper.
   */
  protected function getFileHelper() {
    return new FileHelper();
  }

}
