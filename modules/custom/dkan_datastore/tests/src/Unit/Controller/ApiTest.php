<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Drupal\dkan_datastore\Controller\Api;
use Drupal\dkan_common\Tests\DkanTestBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\dkan_datastore\SqlParser;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\Api
 * @group dkan
 */
class ApiTest extends DkanTestBase {

    public function dataTestExplode() {

        return [
            ['foobar', []], // invalid but should still pass
            [
                '[SELECT * FROM abc];',
                ['SELECT * FROM abc',]
            ],
            [
                '[SELECT * FROM abc][WHERE def LIKE "hij"];',
                [
                    'SELECT * FROM abc',
                    'WHERE def LIKE "hij"',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                    'ORDER BY qrs',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                    'ORDER BY qrs, tuv',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv DESC][LIMIT 1 OFFSET 2];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                    'ORDER BY qrs, tuv DESC',
                    'LIMIT 1 OFFSET 2',
                ]
            ],
        ];
    }

    /**
     * Tests explode().
     *
     * @param string $sqlString
     * @param mixed $expected
     * @dataProvider dataTestExplode
     */
    public function testExplode($sqlString, $expected) {

        // mock with little changed
        $mock = $this->getMockBuilder(Api::class)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $actual = $this->invokeProtectedMethod($mock, 'explode', $sqlString);

        $this->assertArrayEquals($expected, $actual);
    }


    public function dataTestGetUuidFromSelect() {
        return [
            // tests garbage in/out at the same time
            ['foobar','foobar'],
            ['something from foo','something from foo'],
            ['something FROM foo','foo'],
            ['SELECT something FROM foo WHERE BAR=1','foo WHERE BAR=1'],
        ];
    }
    /**
     *
     * @param type $select
     * @param type $expected
     * @dataProvider dataTestGetUuidFromSelect
     */
    public function testGetUuidFromSelect($select, $expected) {
                // mock with little changed
        $mock = $this->getMockBuilder(Api::class)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $actual =$this->invokeProtectedMethod($mock, 'getUuidFromSelect', $select);

        $this->assertEquals($expected, $actual);
    }

}
