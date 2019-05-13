<?php

namespace Drupal\interra_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An ample controller.
 */
class FrontEndController extends ControllerBase {

  /**
   *
   * @todo this is never used.
   * @var strign
   */
  private $chunkId = '4883fea295316854f264';

  /**
   *
   */
  public function about(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function home(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function search(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function api(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function groups(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function org(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function dataset(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   */
  public function distribution(Request $request) {
    return $this->buildPage($request);
  }

  /**
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\Response
   * @throws NotFoundHttpException If page is not found
   */
  public function buildPage(Request $request) {
    $page        = \Drupal::service('interra_frontend.interra_page');
    $factory     = \Drupal::service('dkan.factory');
    $pageContent = $page->build();
    if (empty($pageContent)) {
      throw new NotFoundHttpException('Page could not be loaded');
    }
    return $factory->newHttpResponse($pageContent);
  }

}
