<?php

namespace Drupal\Tests\dkan_datastore\Unit\Storage;

use Dkan\PhpUnit\DkanTestBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_datastore\Storage\Variable;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\Config;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Storage\Variable
 * @group dkan_datastore
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class VariableTest extends DkanTestBase {

    public function dataTestConstruct() {

        return [
            [['foo'], ['foo']], // successfull getAll
            [FALSE, []], // unsuccessfull getAll
        ];
    }

    /**
     * 
     * @dataProvider dataTestConstruct
     * @param type $getAll
     * @param type $expectedStore
     */
    public function testConstruct($getAll, $expectedStore) {

        $mockConfigInterface = $this->createMock(ConfigFactoryInterface::class);


        $mock = $this->getMockBuilder(Variable::class)
                ->setMethods([
                    'getAll'
                ])
                // defer calling constructor
                ->disableOriginalConstructor()
                ->getMock();


        $mock->expects($this->once())
                ->method('getAll')
                ->willReturn($getAll);

        //Assert

        $mock->__construct($mockConfigInterface);

        $actualConfigFactory = $this->accessProtectedProperty($mock, 'configFactory');
        $actualStore = $this->accessProtectedProperty($mock, 'store');

        $this->assertSame($mockConfigInterface, $actualConfigFactory);
        $this->assertEquals($actualStore, $expectedStore);
    }

    public function testSet() {

        // setup
        $mock = $this->getMockBuilder(Variable::class)
                ->setMethods([
                    'pushAll'
                ])
                ->disableOriginalConstructor()
                ->getMock();

        $testKey = uniqid('foo');
        $testValue = uniqid('bar');

        // expect

        $mock->expects($this->once())
                ->method('pushAll');

        // assert
        $mock->set($testKey, $testValue);
        $store = $this->accessProtectedProperty($mock, 'store');
        $this->assertEquals($testValue, $store[$testKey]);
    }

    /**
     * Data provider for testGet.
     * 
     * @return array Array of arguments.
     */
    public function dataTestGet() {

        $store = [
            'exists' => 'foobar',
        ];
        return [
            [$store, 'exists', NULL, 'foobar'],
            [$store, 'exists', 'default', 'foobar'],
            [$store, 'notexists', NULL, NULL],
            [$store, 'notexists', 'default', 'default'],
        ];
    }

    /**
     * Tests get().
     * 
     * @param array $store
     * @param string $key
     * @param mixed $default
     * @param mixed $expected
     * @dataProvider dataTestGet
     */
    public function testGet(array $store, $key, $default, $expected) {

        // setup
        $mock = $this->getMockBuilder(Variable::class)
                ->setMethods(NULL)
                ->disableOriginalConstructor()
                ->getMock();

        $this->writeProtectedProperty($mock, 'store', $store);

        // assert
        $actual = $mock->get($key, $default);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests getAll().
     */
    public function testGetAll() {

        //setup
        $mock = $this->getMockBuilder(Variable::class)
                ->setMethods(NULL)
                ->disableOriginalConstructor()
                ->getMock();

        $mockConfigFactory = $this->getMockBuilder(ConfigFactoryInterface::class)
                ->setMethods(['get'])
                ->getMockForAbstractClass();

        $mockImmutableConfig = $this->getMockBuilder(ImmutableConfig::class)
                ->setMethods(['get'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->writeProtectedProperty($mock, 'configFactory', $mockConfigFactory);

        $expected = ['foo' => 'bar'];

        // expects

        $mockConfigFactory->expects($this->once())
                ->method('get')
                ->with('dkan_datastore.keyvalue')
                ->willReturn($mockImmutableConfig);

        $mockImmutableConfig->expects($this->once())
                ->method('get')
                ->with('data')
                ->willReturn(serialize($expected));

        // assert
        $actual = $this->invokeProtectedMethod($mock, 'getAll');

        $this->assertArrayEquals($actual, $expected);
    }

    /**
     * Tests pushAll().
     */
    public function testPushAll() {
        //setup
        $mock = $this->getMockBuilder(Variable::class)
                ->setMethods(NULL)
                ->disableOriginalConstructor()
                ->getMock();

        $mockConfigFactory = $this->getMockBuilder(ConfigFactoryInterface::class)
                ->setMethods(['getEditable'])
                ->getMockForAbstractClass();

        $mockConfig = $this->getMockBuilder(Config::class)
                ->setMethods([
                    'set',
                    'save',
                ])
                ->disableOriginalConstructor()
                ->getMock();

        $store = ['foo' => 'bar'];
        $this->writeProtectedProperty($mock, 'configFactory', $mockConfigFactory);
        $this->writeProtectedProperty($mock, 'store', $store);

        // expects

        $mockConfigFactory->expects($this->once())
                ->method('getEditable')
                ->with('dkan_datastore.keyvalue')
                ->willReturn($mockConfig);

        $mockConfig->expects($this->once())
                ->method('set')
                ->with('data', serialize($store))
                ->willReturnSelf();

        $mockConfig->expects($this->once())
                ->method('save');

        // assert
        $actual = $this->invokeProtectedMethod($mock, 'pushAll');
    }

}
