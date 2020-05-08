<?php

use Drupal\Core\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\common\UrlHostTokenResolver;
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
    $container = (new Chain($this))
      ->add(Container::class, "get", RequestStack::class)
      ->add(RequestStack::class, "getCurrentRequest", Request::class)
      ->add(Request::class, "getHost", "replacement")
      ->getMock();

    \Drupal::setContainer($container);

    $string = "blahj do bla da bla " . UrlHostTokenResolver::TOKEN . " after token.";
    $newString = UrlHostTokenResolver::resolve($string);
    $this->assertEquals("blahj do bla da bla replacement after token.", $newString);
  }

}
