<?php

namespace Drupal\Tests\json_form_widget\Kernel\Element;

use Drupal\json_form_widget\Element\UploadOrLink;
use Drupal\KernelTests\KernelTestBase;

class UploadOrLinkTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'json_form_widget',
    'file',
    'node',
    'user',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('file');
    $this->installEntitySchema('node');
  }

  public function testGetFileUri() {
    // Host is probably localhost, but let's set it just in case.
    $host = \Drupal::request()->getHost();
    $scheme = 'public';
    $this->config('system.file')
      ->set('default_scheme', $scheme)
      ->save();

    // A URL with a different host will be preserved.
    $url = "http://example.com/sites/default/files/something.txt";
    $uri = UploadOrLink::getFileUri($url);
    $this->assertEquals($url, $uri);

    // Files need download access in order for the reference to be accessible.
    $file = UploadOrLink::getManagedFile($uri);
    $this->assertEquals($file->access('download'), 'ALLOWED');

    // We need to get the files path dynamically, in a kernel test running in
    // Docker it is likely something like
    // "/vfs://root/sites/simpletest/95827600/files/"
    $scheme_path = \Drupal::service('file_url_generator')->generateString("{$scheme}://");
    // A URL from the same domain will be converted to a file URI.
    $url = "http://{$host}{$scheme_path}something.txt";
    $uri = UploadOrLink::getFileUri($url);
    $this->assertEquals("public://something.txt", $uri);
  }
}
