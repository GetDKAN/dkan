<?php

use Drupal\frontend\Page;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use PHPUnit\Framework\TestCase;
use Drupal\node\NodeStorageInterface;
use MockChain\Chain;
use MockChain\Options;

/**
 *
 */
class PageTest extends TestCase {
  private $node;

  /**
   * Private.
   */
  private function getConfigFactory() {
    $options = (new Options())
      ->add('routes', ["home,/home", "dataset,/dataset/123", "about,/about"])
      ->add('build_folder', '/build')
      ->add('frontend_path', '/frontend')
      ->index(0);

    /* Test Frontend Config Factory */
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', $options)
      ->getMock();

    return $configFactory;
  }

  /**
   *
   */
  private function getNodeStorageMock() {
    $nodeStorage = $this->getMockBuilder(NodeStorageInterface::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['loadByProperties'])
      ->getMockForAbstractClass();

    $nodeStorage->method('loadByProperties')
      ->willReturn([$this->node]);

    return $nodeStorage;
  }

  /**
   * Private.
   */
  private function getNodeMock() {
    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['uuid'])
      ->getMockForAbstractClass();

    $node->method('uuid')
      ->willReturn(['123']);

    return $node;
  }

  /**
   * Test regular page.
   */
  public function test() {
    $page = new Page(__DIR__ . "/../../cra", $this->getNodeStorageMock(), $this->getConfigFactory());
    $content = $page->build('home');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $content);

    $content = $page->build('/about');
    $this->assertEquals("<h1>!!!Hello World!!!</h1>\n", $content);
  }

  /**
   * Test nonvalid UUID.
   */
  public function testNoDataset() {
    $page = new Page(__DIR__ . "/../../cra", $this->getNodeStorageMock(), $this->getConfigFactory());
    $content = $page->buildDataset('444');
    $this->assertEquals("<h1>!!!Hello World!!!</h1>\n", $content);
  }

  /**
   * Test valid UUID.
   */
  public function testDataset() {
    $page = new Page(__DIR__ . "/../../cra", $this->getNodeStorageMock(), $this->getConfigFactory());
    $content = $page->buildDataset('123');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $content);
  }

}
