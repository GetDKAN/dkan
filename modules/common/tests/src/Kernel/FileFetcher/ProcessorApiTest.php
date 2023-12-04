<?php

namespace Drupal\Tests\common\Kernel\FileFetcher;

use Drupal\KernelTests\KernelTestBase;
use Drupal\processor_api_test\FileFetcher\FileFetcherFactory as CustomFileFetcherFactory;
use Drupal\processor_api_test\FileFetcher\NonProcessor;
use Drupal\processor_api_test\FileFetcher\YesProcessor;
use FileFetcher\FileFetcher;

/**
 * Ensures custom processor API is working.
 *
 * @group dkan
 * @group common
 * @group kernel
 */
class ProcessorApiTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'processor_api_test',
  ];

  public function test() {
    $identifier = 'foo';
    $factory = $this->container->get('dkan.common.file_fetcher');
    $this->assertInstanceOf(CustomFileFetcherFactory::class, $factory);

    $instance = $factory->getInstance($identifier, ['filePath' => 'asdf']);
    $this->assertInstanceOf(FileFetcher::class, $instance);

    $ref_custom_processors = new \ReflectionProperty($instance, 'customProcessorClasses');
    $ref_custom_processors->setAccessible(TRUE);

    // NonProcessor is always set by our custom file fetcher factory.
    $this->assertContains(NonProcessor::class, $ref_custom_processors->getValue($instance));

    $ref_get_processor = new \ReflectionMethod($instance, 'getProcessor');
    $ref_get_processor->setAccessible(TRUE);

    // NonProcessor will not process because it always returns false from
    // isServerCompatible(). So we get a NULL.
    $this->assertNull($ref_get_processor->invoke($instance));

    // Gather a file fetcher again, specifying another custom processor.
    $instance = $factory->getInstance($identifier, [
      'filePath' => 'asdf',
      'processors' => [YesProcessor::class],
    ]);

    // Both custom processors are still available.
    $this->assertContains(NonProcessor::class, $ref_custom_processors->getValue($instance));
    $this->assertContains(YesProcessor::class, $ref_custom_processors->getValue($instance));
    $this->assertInstanceOf(YesProcessor::class, $ref_get_processor->invoke($instance));

    // Gather a third time, and now don't specify a custom processor.
    $instance = $factory->getInstance($identifier, [
      'filePath' => 'asdf',
      'processors' => [YesProcessor::class],
    ]);

    $this->assertContains(NonProcessor::class, $ref_custom_processors->getValue($instance));
    $this->assertContains(YesProcessor::class, $ref_custom_processors->getValue($instance));
    // Processor will still be YesProcessor because previously declared custom
    // processors are stored in the file fetcher's job store, and NonProcessor
    // always returns false for isServerCompatible().
    $this->assertInstanceOf(YesProcessor::class, $ref_get_processor->invoke($instance));
  }

}
