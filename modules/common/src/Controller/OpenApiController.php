<?php

namespace Drupal\common\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan\Controller\OpenApiController as DkanOpenApiController;

/**
 * Serves openapi spec for dataset-related endpoints.
 */
class OpenApiController extends DkanOpenApiController implements ContainerInjectionInterface {
}
