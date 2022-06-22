<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\Response;

/**
 * Cachable Response Trait.
 */
trait CacheableResponseTrait {

  /**
   * Cache page max age config value.
   *
   * @var int
   */
  protected $cacheMaxAge;

  /**
   * Adds cache headers to the response.
   *
   * @todo implement more flexible caching and move the code out of the trait.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   Symfony response.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Symfony response.
   *
   * @throws \Exception
   */
  protected function addCacheHeaders(Response $response) : Response {
    $this->setCacheMaxAge();

    if ($this->cacheMaxAge !== 0) {
      $response->setCache([
        'public' => TRUE,
        'private' => FALSE,
        'max_age' => $this->cacheMaxAge,
        'last_modified' => new \DateTime(),
      ]);
    }
    return $response;
  }

  /**
   * Sets cache max age.
   */
  protected function setCacheMaxAge() {
    if (!isset($this->cacheMaxAge)) {
      // A hack to bypass the controllers' tests.
      if (\Drupal::hasService('config.factory')) {
        $this->cacheMaxAge = \Drupal::config('system.performance')->get('cache.page.max_age');
      }
      else {
        $this->cacheMaxAge = 0;
      }
    }
  }

}
