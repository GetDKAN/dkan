<?php

namespace Drupal\interra_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\HttpResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\interra_frontend\InterraPage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* An ample controller.
*/
class FrontEndController extends ControllerBase {

  private $chunkId = '4883fea295316854f264';

  public function about ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function home ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function search ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function api ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function groups ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function org ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function dataset ( Request $request ) {
    return $this->buildPage( $request );
  }
  public function distribution ( Request $request ) {
    return $this->buildPage( $request );
  }

  public function buildPage ( Request $request ) {
    $path =  ltrim($request->getPathInfo(), '/');
    $page = new InterraPage($this->chunkId, $path);
    return new Response( $page->build() );
  }
}



