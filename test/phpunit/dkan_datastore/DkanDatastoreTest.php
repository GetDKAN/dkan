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

  /**
   * Test importByCli.
   *
   * @covers DkanDatastore::importByCli
   */
  public function testImportByCli() {
    $feedsSourceStub = $this->getMockBuilder(FeedsSource::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    $feedsSourceStub->expects($this->once())
      ->method('import')
      ->willReturn(FEEDS_BATCH_COMPLETE);

    // Create a stub for the DkanDatastore class.
    $dkanDatastoreStub = $this->getMockBuilder(DkanDatastore::class)
      ->setMethods(['source', 'setupSourceBackground'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('source')
      ->willReturn($feedsSourceStub);

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('setupSourceBackground')
      ->willReturn(TRUE);

    $this->assertEquals(TRUE, $dkanDatastoreStub->importByCli());
  }

  /**
   * Test importByCli when Feeds Source preparation fails.
   *
   * @covers DkanDatastore::importByCli
   */
  public function testImportByCliFailedSourcePreparation() {
    $feedsSourceStub = $this->getMockBuilder(FeedsSource::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    $feedsSourceStub->expects($this->never())
      ->method('import');

    // Create a stub for the DkanDatastore class.
    $dkanDatastoreStub = $this->getMockBuilder(DkanDatastore::class)
      ->setMethods(['source', 'setupSourceBackground'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('source')
      ->willReturn($feedsSourceStub);

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('setupSourceBackground')
      ->willReturn(FALSE);

    $this->assertEquals(FALSE, $dkanDatastoreStub->importByCli());
  }

  /**
   * Test importByCli when the Feed Source import fails.
   *
   * @covers DkanDatastore::importByCli
   */
  public function testImportByCliFailImport() {
    $feedsSourceStub = $this->getMockBuilder(FeedsSource::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    $feedsSourceStub->expects($this->once())
      ->method('import')
      ->will($this->throwException(new Exception()));

    // Create a stub for the DkanDatastore class.
    $dkanDatastoreStub = $this->getMockBuilder(DkanDatastore::class)
      ->setMethods(['source', 'setupSourceBackground'])
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->getMock();

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('source')
      ->willReturn($feedsSourceStub);

    // Configure the DkanDatastorestub method.
    $dkanDatastoreStub->expects($this->once())
      ->method('setupSourceBackground')
      ->willReturn(TRUE);

    $this->assertEquals(FALSE, $dkanDatastoreStub->importByCli());
  }

}
