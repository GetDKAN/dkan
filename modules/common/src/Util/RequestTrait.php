<?php

namespace Drupal\common\Util;

/**
 * Utilities relating to current request.
 */
trait RequestTrait {

  /**
   * Request object from current stack.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Request.
   *
   * @_codeCoverageIgnore
   */
  protected function getCurrentRequest() {
    return \Drupal::request();
  }

  /**
   * Current request uri.
   *
   * @return string
   *   String.
   *
   * @_codeCoverageIgnore
   */
  protected function getCurrentRequestUri() {
    return $this->getCurrentRequest()
      ->getRequestUri();
  }

  /**
   * Current request body.
   *
   * @return string|resource
   *   String or resource.
   *
   * @_codeCoverageIgnore
   */
  protected function getCurrentRequestContent() {
    return $this->getCurrentRequest()
      ->getContent();
  }

}
