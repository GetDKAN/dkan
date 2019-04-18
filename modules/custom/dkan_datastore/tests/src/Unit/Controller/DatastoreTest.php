<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Drupal\dkan_datastore\Controller\Datastore;
use Dkan\PhpUnit\DkanTestBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\dkan_datastore\SqlParser;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\Datastore
 * @group dkan
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class DatastoreTest extends DkanTestBase {

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
                    'ORDER BY qrs ASC',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                    'ORDER BY qrs, tuv ASC',
                ]
            ],
            [
                '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv DESC][LIMIT 1 OFFSET 2];',
                [
                    'SELECT * FROM abc',
                    'WHERE def = "hij" AND klm = "nop"',
                    'ORDER BY qrs, tuv DESC ASC',
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
        $mock = $this->getMockBuilder(Datastore::class)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $actual =$this->invokeProtectedMethod($mock, 'explode', $sqlString);

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
        $mock = $this->getMockBuilder(Datastore::class)
                ->disableOriginalConstructor()
                ->setMethods(null)
                ->getMock();

        $actual =$this->invokeProtectedMethod($mock, 'getUuidFromSelect', $select);

        $this->assertEquals($expected, $actual);
    }

}
