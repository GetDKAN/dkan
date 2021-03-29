<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\metastore\Reference\Referencer;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReferencerTest extends TestCase {

  public function testHostify() {
    $container = (new Chain($this))
      ->add(Container::class, 'get', RequestStack::class)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->getMock();

    \Drupal::setContainer($container);

    $this->assertEquals(
      'http://h-o.st/mycsv.txt',
      Referencer::hostify("http://test.test/mycsv.txt"));
  }

}
