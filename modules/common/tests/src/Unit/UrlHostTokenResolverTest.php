<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\UrlHostTokenResolver;
use Drupal\Core\StreamWrapper\PublicStream;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test the UrlHostTokenResolver class.
 *
 * @covers \Drupal\common\UrlHostTokenResolver
 *
 * @group dkan
 * @group common
 * @group unit
 */
class UrlHostTokenResolverTest extends TestCase {

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  public const HOST = 'http://example.com';

  /**
   * HTTP file path for testing download URL.
   *
   * @var string
   */
  public const FILE_PATH = 'tmp/mycsv.csv';

  /**
   *
   */
  public function test() {
    $options = (new Options())
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'replacement')
      ->getMock();

    \Drupal::setContainer($container);

    $string = 'blahj do bla da bla ' . UrlHostTokenResolver::TOKEN . ' after token.';
    $newString = UrlHostTokenResolver::resolve($string);
    $this->assertEquals('blahj do bla da bla replacement after token.', $newString);
  }


  /**
   * Test the `Referencer::hostify()` method.
   */
  public function testHostify(): void {
    // Initialize `\Drupal::container`.
    $options = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class);
    \Drupal::setContainer($container_chain->getMock());
    // Ensure the hostify method is properly resolving the supplied URL.
    $this->assertEquals(
      'http://' . UrlHostTokenResolver::TOKEN . '/' . self::FILE_PATH,
      UrlHostTokenResolver::hostify(self::HOST . '/' . self::FILE_PATH));
  }

}
