<?php

namespace Drupal\Tests\common\Kernel\FileFetcher;

use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_processor_test\FileFetcher\CustomFileFetcherFactory;
use Drupal\custom_processor_test\FileFetcher\NonProcessor;
use Drupal\custom_processor_test\FileFetcher\YesProcessor;
use FileFetcher\FileFetcher;

/**
 * Ensures custom processor API is working.
 *
 * @group dkan
 * @group common
 * @group kernel
 */
class CustomProcessorTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'custom_processor_test',
  ];

  public function test() {
    $identifier = 'my_identifier';
    // Services from custom_processor_test module should decorate
    // dkan.common.file_fetcher so that we get the custom file fetcher instead.
    $factory = $this->container->get('dkan.common.file_fetcher');
    $this->assertInstanceOf(CustomFileFetcherFactory::class, $factory);

    /** @var \FileFetcher\FileFetcher $instance */
    $instance = $factory->getInstance($identifier, ['filePath' => 'asdf']);
    $this->assertInstanceOf(FileFetcher::class, $instance);

    $ref_custom_processors = new \ReflectionProperty($instance, 'customProcessorClasses');
    $ref_custom_processors->setAccessible(TRUE);

    // NonProcessor is always set by our custom file fetcher factory.
    $this->assertContains(
      NonProcessor::class,
      $ref_custom_processors->getValue($instance)
    );

    $ref_get_processor = new \ReflectionMethod($instance, 'getProcessor');
    $ref_get_processor->setAccessible(TRUE);

    // NonProcessor will not process because it always returns false from
    // isServerCompatible(). Also our file path of 'asdf' results in false from
    // isServerCompatible() from the default processors as well, so we get NULL.
    $this->assertNull($ref_get_processor->invoke($instance));

    // Gather a file fetcher again, specifying another custom processor.
    $instance = $factory->getInstance($identifier, [
      'filePath' => 'asdf',
      'processors' => [YesProcessor::class],
    ]);

    // Both custom processors are still available because our factory always
    // specifies NonProcessor in addition to whatever is in $config.
    $this->assertEquals([
      NonProcessor::class,
      YesProcessor::class,
    ], $ref_custom_processors->getValue($instance));
    $this->assertInstanceOf(
      YesProcessor::class,
      $ref_get_processor->invoke($instance)
    );

    // Gather a third time, and now don't specify a custom processor.
    $instance = $factory->getInstance($identifier, ['filePath' => 'asdf']);

    // NonProcessor and YesProcessor are still available because they're both
    // serialized into the jobstore for this file fetcher.
    $this->assertEquals([
      NonProcessor::class,
      YesProcessor::class,
    ], $ref_custom_processors->getValue($instance));
    // Processor will still be YesProcessor because NonProcessor always returns
    // false for isServerCompatible() which rules it out.
    $this->assertInstanceOf(YesProcessor::class, $ref_get_processor->invoke($instance));
  }

}
