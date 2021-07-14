<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\Response;

/**
 * Cachable Response Trait.
 */
trait CachableResponseTrait {

  /**
   * Cache page max age config value.
   *
   * @var int
   */
  protected $maxAge;

  /**
   * Adds cache headers to the response.
   *
   * TODO: implement more flexible caching and move the code out of the trait.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   Symfony response.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Symfony response.
   *
   * @throws \Exception
   */
  private function addCacheHeaders(Response $response) : Response {
    if (!isset($this->maxAge)) {
      $this->maxAge = \Drupal::config('system.performance')->get('cache.page.max_age');
    }
    $response->setCache([
      'public' => TRUE,
      'private' => FALSE,
      'max_age' => $this->maxAge,
      'last_modified' => new \DateTime(),
    ]);
    $response->setVary('Cookie');
    return $response;
  }

}
