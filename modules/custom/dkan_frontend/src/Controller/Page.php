<?php

namespace Drupal\dkan_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An ample controller.
 */
class Page extends ControllerBase {

  /**
   * Controller method.
   */
  public function page($name) {
    return $this->buildPage($name);
  }

  /**
   * Private..
   */
  private function buildPage($name) {
    $page        = \Drupal::service('dkan_frontend.page');
    $factory     = \Drupal::service('dkan.factory');
    $pageContent = $page->build($name);
    if (empty($pageContent)) {
      throw new NotFoundHttpException('Page could not be loaded');
    }
    return $factory->newHttpResponse($pageContent);
  }

}
