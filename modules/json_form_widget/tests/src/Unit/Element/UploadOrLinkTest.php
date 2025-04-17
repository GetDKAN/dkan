<?php

namespace Drupal\Tests\json_form_widget\Unit\Element;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\json_form_widget\Element\UploadOrLink;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class UploadOrLinkTest extends TestCase {

  /**
   * Test the getFileUri method.
   *
   * @covers \Drupal\json_form_widget\Element\UploadOrLink::getFileUri
   *
   * @dataProvider getFileUriDataProvider
   */
  public function testGetFileUri($url, $relative, $scheme_path, $expected) {
    $options = (new Options())
      ->add('file_url_generator', FileUrlGenerator::class)
      ->add('config.factory', ConfigFactory::class)
      ->index(0);

    $containerMock = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(FileUrlGenerator::class, 'transformRelative', $relative)
      ->add(FileUrlGenerator::class, 'generateString', $scheme_path)
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 'public')
      ->getMock();

    \Drupal::setContainer($containerMock);

    $result = UploadOrLink::getFileUri($url);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testGetFileUri.
   *
   * Based on real-life tests.
   *
   * @return array
   *   An array of test data.
   */
  public static function getFileUriDataProvider(): array {
    return [
      [
        'url' => 'https//example.com/test1.csv',
        'relative' => 'https//example.com/test1.csv',
        'scheme_path' => '/sites/default/files/',
        'expected' => 'https//example.com/test1.csv',
      ],
      [
        'url' => 'https//example.com/sites/default/files/test1.csv',
        'relative' => 'https//example.com/sites/default/files/test1.csv',
        'scheme_path' => '/sites/default/files/',
        'expected' => 'https//example.com/sites/default/files/test1.csv',
      ],
      [
        'url' => 'http://localhost/test2.csv',
        'relative' => '/test2.csv',
        'scheme_path' => '/sites/default/files/',
        'expected' => '/test2.csv',
      ],
      [
        'url' => 'http://localhost/sites/default/files/test3.csv',
        'relative' => '/sites/default/files/test3.csv',
        'scheme_path' => '/sites/default/files/',
        'expected' => 'public://test3.csv',
      ],
      [
        'url' => "http://localhost/vfs://root/sites/simpletest/10834342/files/test4.csv",
        'relative' => '/vfs://root/sites/simpletest/10834342/files/test4.csv',
        'scheme_path' => '/vfs://root/sites/simpletest/10834342/files/',
        'expected' => 'public://test4.csv',
      ],
    ];
 }
}