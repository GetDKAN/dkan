<?php

namespace Drupal\frontend\Controller;

use Drupal\frontend\Page as PageBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * An ample controller.
 */
class Page implements ContainerInjectionInterface {

  private $pageBuilder;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new Page($container->get('frontend.page'));
  }

  /**
   * Constructor.
   */
  public function __construct(PageBuilder $pageBuilder) {
    $this->pageBuilder = $pageBuilder;
  }

  /**
   * Controller method.
   */
  public function page($name) {
    $pageContent = $this->pageBuilder->build($name);
    if (empty($pageContent)) {
      $pageContent = $this->pageBuilder->build("404");
    }
    return Response::create($pageContent);
  }

}
