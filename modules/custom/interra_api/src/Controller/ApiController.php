<?php

namespace Drupal\interra_api\Controller;

use Drupal\Core\Controller\ControllerBase;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* An example controller.
*/
class ApiController extends ControllerBase {

  /**
  * Returns a render-able array for a test page.
  */
  public function routes( Request $request ) {
    $response['data'] = 'Some test data to return';
    $response['method'] = 'GET';

    return new JsonResponse( $response );
  }
}

function getRoutes() {
}

function getCollections() {
  return array(
    'datasets',
    'organizations',
  );
}
