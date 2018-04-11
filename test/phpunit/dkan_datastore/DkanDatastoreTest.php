<?php

/**
 * @file
 * Base phpunit tests for DkanDatastoreFastImport class.
 */

/**
 * DkanDatastoreFastImport class.
 */
class DkanDatastoreTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test updateFromFileUri.
   *
   * @covers DkanDatastore::updateFromFileUri
   */
  public function testUpdateFromFileUri() {
    // Create a stub for the DkanDatastore class.
    $dkanDatastorestub = $this->getMockBuilder(DkanDatastore::class)
      ->setMethods(['updateFromFile'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    // Configure the stub.
    $dkanDatastorestub->expects($this->once())
      ->method('updateFromFile')
      ->willReturn(TRUE);

    $testfileuri = __DIR__ . '/data/countries.csv';
    // Calling $stub->doSomething() will now return
    // 'foo'.
    $this->assertEquals(TRUE, $dkanDatastorestub->updateFromFileUri($testfileuri));
  }

  /**
   * Test updateFromFileUri when no file is provided.
   *
   * @covers DkanDatastore::updateFromFileUri
   */
  public function testUpdateFromFileUriNoFile() {
    // Create a stub for the DkanDatastore class.
    $dkanDatastorestub = $this->getMockBuilder(DkanDatastore::class)
      ->setMethods(['updateFromFile'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    // Configure the DkanDatastorestub method.
    $dkanDatastorestub->expects($this->never())
      ->method('updateFromFile')
      ->willReturn(TRUE);

    $this->assertEquals(FALSE, $dkanDatastorestub->updateFromFileUri());
  }

}
