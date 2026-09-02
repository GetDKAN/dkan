<?php

namespace Drupal\Tests\dkan_datastore_preview\Unit\Service;

use Drupal\dkan_common\DataResource;
use Drupal\dkan_datastore\Service\Info\ImportInfo;
use Drupal\dkan_datastore_preview\Service\ImportStatusMessage;
use Drupal\dkan_metastore\ResourceMapper;
use Drupal\Tests\UnitTestCase;
use Procrastinator\Result;

/**
 * @covers \Drupal\dkan_datastore_preview\Service\ImportStatusMessage
 * @coversDefaultClass \Drupal\dkan_datastore_preview\Service\ImportStatusMessage
 *
 * @group dkan
 * @group dkan_datastore_preview
 * @group unit
 */
class ImportStatusMessageTest extends UnitTestCase {

  const GENERIC = 'Data preview is not yet available.';

  /**
   * Get a service whose mapper knows the resource and reports the statuses.
   */
  protected function getService(bool $known, ?string $fetcherStatus = NULL, ?string $importerStatus = NULL): ImportStatusMessage {
    $mapper = $this->createMock(ResourceMapper::class);
    $mapper->method('get')->willReturn($known ? $this->createMock(DataResource::class) : NULL);

    $importInfo = $this->createMock(ImportInfo::class);
    $importInfo->method('getItem')->willReturn((object) [
      'fileFetcherStatus' => $fetcherStatus ?? Result::WAITING,
      'importerStatus' => $importerStatus ?? Result::WAITING,
    ]);

    $service = new ImportStatusMessage($importInfo, $mapper);
    $service->setStringTranslation($this->getStringTranslationStub());
    return $service;
  }

  /**
   * The render array carries the message and the module's CSS class.
   */
  public function testBuildStructure(): void {
    $build = $this->getService(TRUE)->build('abc__1');
    $this->assertSame('p', $build['#tag']);
    $this->assertSame(['dkan-datastore-preview__message'], $build['#attributes']['class']);
    $this->assertStringContainsString('still being processed', $build['#value']);
  }

  /**
   * Message selection per import state.
   *
   * @dataProvider messageProvider
   */
  public function testMessages(bool $known, string $fetcher, string $importer, string $expectedFragment): void {
    $message = $this->getService($known, $fetcher, $importer)->build('abc__1')['#value'];
    $this->assertStringContainsString($expectedFragment, $message);
  }

  /**
   * Data provider for testMessages().
   */
  public static function messageProvider(): array {
    return [
      'waiting' => [TRUE, Result::WAITING, Result::WAITING, 'still being processed'],
      'fetching' => [TRUE, Result::IN_PROGRESS, Result::WAITING, 'still being processed'],
      'importing' => [TRUE, Result::DONE, Result::IN_PROGRESS, 'still being processed'],
      'fetch error' => [TRUE, Result::ERROR, Result::WAITING, 'could not be generated'],
      'import error' => [TRUE, Result::DONE, Result::ERROR, 'could not be generated'],
      'stopped' => [TRUE, Result::DONE, Result::STOPPED, self::GENERIC],
      'done but no table' => [TRUE, Result::DONE, Result::DONE, self::GENERIC],
      'unknown resource' => [FALSE, Result::WAITING, Result::WAITING, self::GENERIC],
    ];
  }

  /**
   * Unknown resources are never described as processing.
   */
  public function testUnknownResourceIsNotProcessing(): void {
    $message = $this->getService(FALSE)->build('abc__1')['#value'];
    $this->assertStringNotContainsString('processed', $message);
  }

  /**
   * A resource id without a version yields the generic message.
   */
  public function testMalformedResourceId(): void {
    $this->assertSame(self::GENERIC, $this->getService(TRUE)->build('no-version')['#value']);
  }

  /**
   * Service failures degrade to the generic message.
   */
  public function testExceptionYieldsGeneric(): void {
    $mapper = $this->createMock(ResourceMapper::class);
    $mapper->method('get')->willThrowException(new \RuntimeException('db down'));
    $service = new ImportStatusMessage($this->createMock(ImportInfo::class), $mapper);
    $service->setStringTranslation($this->getStringTranslationStub());

    $this->assertSame(self::GENERIC, $service->build('abc__1')['#value']);
  }

}
