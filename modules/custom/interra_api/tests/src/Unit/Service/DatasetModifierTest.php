<?php

namespace Drupal\Tests\interra_api\Unit\Service;

use Drupal\interra_api\Service\DatasetModifier;
use Drupal\dkan_common\Tests\DkanTestBase;

/**
 * Tests Drupal\interra_api\Service\DatasetModifier.
 *
 * @coversDefaultClass Drupal\interra_api\Service\DatasetModifier
 * @group interra_api
 */
class DatasetModifierTest extends DkanTestBase {

  /**
   * Data for testModifyDatasetFunctional.
   *
   * @return array
   *   Array of arguments.
   */
  public function dataTestModifyDatasetFunctional() {
    return [
        [
          (object) [
            'distribution' => [
              (object) [
                'mediaType' => 'text/csv',
              ],
              (object) [
                  // This should be removed.
                'mediaType' => 'foobar',
              ],
            ],
          ],
          (object) [
            'distribution' => [
              (object) [
                'mediaType' => 'text/csv',
                'format'    => 'csv',
              ],
            ],
          ],
        ],
        // Theme processing.
        [
          (object) [
            'distribution' => [],
            'theme' => [
              'Foo Bar Theme',
              'moomootheme',
            ],
          ],
          (object) [
            'distribution' => [],
            'theme' => [
              (object) [
                'identifier' => 'foobartheme',
                'title'      => 'Foo Bar Theme',
              ],
              (object) [
                'identifier' => 'moomootheme',
                'title'      => 'moomootheme',
              ],
            ],
          ],
        ],
        // Keyword processing.
        [
          (object) [
            'distribution' => [],
            'keyword' => [
              'Foo Bar Keyword',
              'moomookeyword',
            ],
          ],
          (object) [
            'distribution' => [],
            'keyword' => [
              (object) [
                'identifier' => 'foobarkeyword',
                'title'      => 'Foo Bar Keyword',
              ],
              (object) [
                'identifier' => 'moomookeyword',
                'title'      => 'moomookeyword',
              ],
            ],
          ],
        ],
    ];
  }

  /**
   * Tests both modifyDataset() and objectifyStringsArray() to some extent.
   *
   * @param \stdClass $dataset
   * @param \stdClass $expected
   *
   * @todo This is pretty much of a functional test.
   * @dataProvider dataTestModifyDatasetFunctional
   */
  public function testModifyDatasetFunctional(\stdClass $dataset, \stdClass $expected) {
    // Setup.
    $mock = $this->getMockBuilder(DatasetModifier::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    // Assert.
    $this->assertEquals($expected, $mock->modifyDataset($dataset));

    // Is a functional test, not really unit.
    // can be risky.
    $this->markAsRisky();
  }

}
