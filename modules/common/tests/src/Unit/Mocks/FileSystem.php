<?php

namespace Drupal\Tests\common\Unit\Mocks;

use Drupal\Core\File\FileSystem as DrupalFilesystem;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Site\Settings;

/**
 *
 */
class FileSystem extends DrupalFileSystem {

  /**
   *
   */
  public function __construct(TestCase $testCase, ContainerInterface $container) {
    $logger = (new Chain($testCase))
      ->add(LoggerInterface::class)
      ->getMock();

    parent::__construct($container->get('stream_wrapper_manager'), Settings::getInstance(), $logger);
  }

}
