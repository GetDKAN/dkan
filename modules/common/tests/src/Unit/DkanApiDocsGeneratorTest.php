<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\Site\Settings;
use Drupal\common\DkanApiDocsGenerator;
use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\common\Plugin\OpenApiSpec;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for DkanApiDocsGenerator.
 */
class DkanApiDocsGeneratorTest extends TestCase {

  /**
   *
   */
  public function testBuildSpecWithoutDkanApiBase() {

    $generator = new DkanApiDocsGenerator(
      $this->getManagerChain()->getMock(),
      new Settings([])
    );
    $result = $generator->buildSpec();

    $paths = array_keys($result->get('$.paths'));
    $expected = [
      '/api/1/some/path',
    ];
    $this->assertEquals($expected, $paths);
  }

  /**
   *
   */
  public function testBuildSpecWithDkanApiBase() {

    $dkanApiPath = '/foo/bar';

    $generator = new DkanApiDocsGenerator(
      $this->getManagerChain()->getMock(),
      new Settings(['dkan_api_base' => $dkanApiPath])
    );
    $result = $generator->buildSpec();

    $paths = array_keys($result->get('$.paths'));
    $expected = [
      $dkanApiPath . '/api/1/some/path',
    ];
    $this->assertEquals($expected, $paths);
  }

  /**
   *
   */
  private function getSpec(): array {
    return [
      'openapi' => '3.0.2',
      'info' => [
        'title' => '',
        'version' => '',
      ],
      'paths' => [
        '/api/1/some/path' => new \stdClass(),
      ],
    ];
  }

  private function getManagerChain() {
    $definitions = [
      ['id' => 'api-1-some-path'],
    ];

    $spec = $this->getSpec();

    return (new Chain($this))
      ->add(DkanApiDocsPluginManager::class, 'getDefinitions', $definitions)
      ->addd('createInstance', DkanApiDocsBase::class)
      ->add(DkanApiDocsBase::class, 'spec', $spec);
  }

}
