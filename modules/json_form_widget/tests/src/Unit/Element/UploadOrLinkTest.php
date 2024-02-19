<?php

namespace Drupal\Tests\json_form_widget\Unit\Element;

use Drupal\json_form_widget\Element\UploadOrLink;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\json_form_widget\Element\UploadOrLink
 * @coversDefaultClass \Drupal\json_form_widget\Element\UploadOrLink
 *
 * @group dkan
 * @group json_form_widget
 * @group unit
 */
class UploadOrLinkTest extends TestCase {

  public function provideGetUrlType() {
    return [
      [UploadOrLink::TYPE_REMOTE, []],
      ['file_url_type', ['#value' => ['file_url_type' => 'file_url_type']]],
      [UploadOrLink::TYPE_UPLOAD, ['#value' => ['fids' => ['some', 'fids']]]],
    ];
  }

  /**
   * @covers ::getUrlType
   * @dataProvider provideGetUrlType
   */
  public function testGetUrlType($expected, $element) {
    $ref_get_url_type = new \ReflectionMethod(UploadOrLink::class, 'getUrlType');
    $ref_get_url_type->setAccessible(TRUE);

    $this->assertEquals($expected,
      $ref_get_url_type->invokeArgs(NULL, [$element])
    );
  }

  /**
   * @covers ::unsetFilesWhenRemoving
   */
  public function testUnsetFilesWhenRemoving() {
    $triggering_element = ['#array_parents' => ['remove_button']];
    $element['#files'] = ['a_file', 'b_file'];

    $ref_unset_files_when_removing = new \ReflectionMethod(UploadOrLink::class, 'unsetFilesWhenRemoving');
    $ref_unset_files_when_removing->setAccessible(TRUE);

    $unset = $ref_unset_files_when_removing->invokeArgs(NULL, [$triggering_element, $element]);
    $this->assertArrayNotHasKey('#files', $unset);
  }

  /**
   * @covers ::unsetFids
   */
  public function testUnsetFids() {
    $fids = [1, 2, 3, 4];
    $element = ['#value' => ['fids' => $fids]];
    foreach ($fids as $fid) {
      $element['file_' . $fid] = $fid;
    }

    $ref_unset_fids = new \ReflectionMethod(UploadOrLink::class, 'unsetFids');
    $ref_unset_fids->setAccessible(TRUE);

    $unset = $ref_unset_fids->invokeArgs(NULL, [$element]);
    $this->assertEmpty($unset['#value']['fids']);
    foreach ($fids as $fid) {
      $this->assertArrayNotHasKey('file_' . $fid, $unset);
    }
  }

}
