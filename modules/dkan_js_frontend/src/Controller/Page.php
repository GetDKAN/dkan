<?php

namespace Drupal\dkan_js_frontend\Controller;

use Drupal\Core\Controller\ControllerBase;

// if (empty($pageContent)) {
//   $pageContent = $this->pageBuilder->build("404");
// }

//throw new NotFoundHttpException();


/**
 * The Page controller.
 */
class Page extends ControllerBase {

  /**
   * Returns a render-able array.
   */
  public function content() {
    return [
      '#theme' => 'page__dkan_js_frontend',
      '#attached' => [
        'drupalSettings' => [
          'dkan_js_frontend' => [
            'data' => 'test1',
          ],
        ],
      ],
    ];
  }

}
