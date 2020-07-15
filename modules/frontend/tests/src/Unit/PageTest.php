<?php

use Drupal\frontend\Page;
use PHPUnit\Framework\TestCase;
use Drupal\node\NodeStorageInterface;

/**
 *
 */
class PageTest extends TestCase {
  private $node;

  /**
   *
   */
  private function getNodeStorageMock() {
    $nodeStorage = $this->getMockBuilder(NodeStorageInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['loadByProperties'])
      ->getMockForAbstractClass();

    $nodeStorage->method('loadByProperties')
      ->willReturn([$this->node]);

    return $nodeStorage;
  }

  /**
   *
   */
  private function getNodeMock() {
    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['uuid'])
      ->getMockForAbstractClass();

    $node->method('uuid')
      ->willReturn(['123']);

    return $node;
  }

  /**
   * Test regular page.
   */
  public function test() {
    $page = new Page(__DIR__ . "/../../gatsby", $this->getNodeStorageMock());
    $content = $page->build('home');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $content);

    $content = $page->build('/about');
    $this->assertEquals("<h1>!!!Hello World!!!</h1>\n", $content);
  }

  /**
   * Test nonvalid UUID.
   */
  public function testNoDataset() {
    $page = new Page(__DIR__ . "/../../gatsby", $this->getNodeStorageMock());
    $content = $page->buildDataset('444');
    $this->assertEquals("<h1>!!!Hello World!!!</h1>\n", $content);
  }

  /**
   * Test valid UUID.
   */
  public function testDataset() {
    $page = new Page(__DIR__ . "/../../gatsby", $this->getNodeStorageMock());
    $content = $page->buildDataset('123');
    $this->assertEquals("<h1>Hello World!!!</h1>\n", $content);
  }

}
