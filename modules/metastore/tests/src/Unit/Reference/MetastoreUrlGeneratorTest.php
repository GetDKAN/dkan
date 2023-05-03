<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\common\StreamWrapper\DkanStreamWrapper;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
use Drupal\metastore\Service;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MetastoreUrlGeneratorTest extends TestCase {

  /**
   * Test a valid local URL.
   */
  public function testUriFromUrl() {
    $url = "https://thisdomain.com/api/1/metastore/schemas/data-dictionary/items/111";
    $uri = "dkan://metastore/schemas/data-dictionary/items/111";
    $generator = $this->getGenerator();

    $this->assertEquals($uri, $generator->uriFromUrl($url));
  }

  public function testUriFromBadUrl() {
    $url = "another-domain.com/api/1/metastore/schemas/data-dictionary/items/111";
    $generator = $this->getGenerator();

    $this->expectExceptionMessage("Invalid URL");
    $generator->uriFromUrl($url);
  }

  public function testUriFromRemoteUrl() {
    $url = "https://another-domain.com/api/1/metastore/schemas/data-dictionary/items/111";
    $generator = $this->getGenerator();

    $this->expectExceptionMessage("does not match URL host another-domain.com");
    $generator->uriFromUrl($url);
  }

  public function testUriFromWrongPath() {
    $url = "https://thisdomain.com/api/data-dictionary/items/111";
    $generator = $this->getGenerator();

    $this->expectExceptionMessage("path does not match DKAN API path");
    $generator->uriFromUrl($url);
  }


  /**
   * Test the validateUri() method.
   */
  public function testValidateUri() {
    $generator = $this->getGenerator();
    $this->assertTrue($generator->validateUri("dkan://metastore/schemas/data-dictionary/items/111"));
    // ID that does not exist.
    $this->assertFalse($generator->validateUri("dkan://metastore/schemas/data-dictionary/items/222"));
    // Malformed URI path.
    $this->assertFalse($generator->validateUri("dkan://metastore/data-dictionary/items/111"));
  }

  private function getGenerator() {
    // Create and set Drupal service container.
    $container = (new Chain($this))
      ->add(Container::class, 'get', (new Options())
        ->add('path.validator', PathValidator::class)
        ->add('unrouted_url_assembler', UnroutedUrlAssembler::class)
        ->add('file_system', FileSystem::class)
        ->index(0)
      )
      ->add(PathValidator::class, 'getPathAttributes', [])
      ->add(UnroutedUrlAssembler::class, 'assemble', "/api/1/metastore/schemas/data-dictionary/items/111") 
      ->getMock();
    \Drupal::setContainer($container);

    // Create mock StreamWrapperManager.
    $streamWrapperManager = (new Chain($this))
      ->add(StreamWrapperManager::class, 'getViaScheme', DkanStreamWrapper::class)
      ->getMock();

    // Create mock metastore serivce.
    $metastore = (new Chain($this))
      ->add(Service::class, 'get', (new Options())
        ->add('111', new RootedJsonData("{}", "{}"))
        ->add('222', new MissingObjectException("222 not found"))
        ->index(1)
      )
      ->getMock();

    // Create mock RequestStack.
    $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'thisdomain.com']);
    $requestStack = $this->getMockBuilder(RequestStack::class)
      ->onlyMethods(['getCurrentRequest'])
      ->getMock();
    $requestStack->method('getCurrentRequest')
      ->will($this->returnValue($request));

    return new MetastoreUrlGenerator($streamWrapperManager, $metastore, $requestStack);
  }

}
