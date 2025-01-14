<?php

namespace modules\dkan_js_frontend\tests\Functional\Controller;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\RequestOptions;

class DatasetControllerTest extends BrowserTestBase {

  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    // Set up a Guzzle client using our service.
    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
        'http_errors' => FALSE,
      ]);
  }

  /**
   * Test that invalid dataset id returns a 404.
   */
  public function testInvalidDatasetId() {
    $url = Url::fromUri('base:/dataset/123');
    $response = $this->httpClient->request('GET', $url->toString(), []);
    $this->assertEquals(404, $response->getStatusCode());
  }

}
