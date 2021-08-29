<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\UrlHostTokenResolver;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class UrlHostTokenResolverTest extends TestCase {

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

}
