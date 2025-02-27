<?php

declare(strict_types=1);

namespace Drupal\Tests\common\Functional\Util;

use Drupal\Tests\BrowserTestBase;

/**
 * This is a copy of \Drupal\Tests\system\Functional\System\RetrieveFileTest.
 *
 * @covers \Drupal\common\Util\DrupalFiles
 * @coversDefaultClass \Drupal\common\Util\DrupalFiles
 *
 * @group dkan
 * @group common
 * @group functional
 *
 * @see \Drupal\Tests\system\Functional\System\RetrieveFileTest
 */
class DrupalFilesTest extends BrowserTestBase {

  protected static $modules = [
    'common',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * @covers ::retrieveRemoteFile
   */
  public function testFileRetrieving(): void {
    /** @var \Drupal\common\Util\DrupalFiles $drupal_files */
    $drupal_files = \Drupal::service('dkan.common.drupal_files');
    $ref_system_retrieve_file = new \ReflectionMethod($drupal_files, 'retrieveRemoteFile');

    // Test 404 handling by trying to fetch a randomly named file.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->mkdir($source_dir = 'public://' . $this->randomMachineName());
    // cSpell:disable-next-line
    $filename = 'Файл для тестирования ' . $this->randomMachineName();
    $url = \Drupal::service('file_url_generator')->generateAbsoluteString($source_dir . '/' . $filename);
    $retrieved_file = $ref_system_retrieve_file->invokeArgs($drupal_files, [$url]);
    $this->assertFalse($retrieved_file, 'Non-existent file not fetched.');

    // Actually create that file, download it via HTTP and test the returned path.
    file_put_contents($source_dir . '/' . $filename, 'testing');
    $retrieved_file = $ref_system_retrieve_file->invokeArgs($drupal_files, [$url]);

    // URLs could not contains characters outside the ASCII set so $filename
    // has to be encoded.
    $encoded_filename = rawurlencode($filename);

    $this->assertEquals('public://' . $encoded_filename, $retrieved_file, 'Sane path for downloaded file returned (public:// scheme).');
    $this->assertFileExists($retrieved_file);
    $this->assertEquals(7, filesize($retrieved_file), 'File size of downloaded file is correct (public:// scheme).');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->delete($retrieved_file);

    // Test downloading file to a different location.
    $file_system->mkdir($target_dir = 'temporary://' . $this->randomMachineName());
    $retrieved_file = $ref_system_retrieve_file->invokeArgs($drupal_files, [$url, $target_dir]);
    $this->assertEquals("{$target_dir}/{$encoded_filename}", $retrieved_file, 'Sane path for downloaded file returned (temporary:// scheme).');
    $this->assertFileExists($retrieved_file);
    $this->assertEquals(7, filesize($retrieved_file), 'File size of downloaded file is correct (temporary:// scheme).');
    $file_system->delete($retrieved_file);

    $file_system->deleteRecursive($source_dir);
    $file_system->deleteRecursive($target_dir);
  }

}
