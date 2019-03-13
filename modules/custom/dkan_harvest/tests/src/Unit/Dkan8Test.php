<?php

namespace Drupal\Tests\dkan_harvest\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\dkan_harvest\Load\Dkan8;

/**
 * @group dkan
 */
class Dkan8Test extends UnitTestCase {

  protected $filesLocalSaveDirectory;

  protected function setUp() {
    parent::setUp();
    $this->docroot = dirname(__DIR__, 9);
    $this->filesLocalSaveDirectory = $this->docroot . "/sites/default/files" . "/distribution";
  }

  /**
   * Tests the Dkan8::saveDatasetFilesLocally() method.
   */
  public function testSaveDatasetFilesLocally() {
    $log = new \stdClass();
    $config = new \stdClass();
    $sourceId = "testSaveDatasetFilesLocally";
    $runId = 1;

    $dkan8 = new TestDkan8($log, $config, $sourceId, $runId);

    // Pass in $doc w/ distribution w/ downloadURL.
    $doc = new \stdClass();
    $distribution = array();
    $distribution1 = new \stdClass();
    $distribution1->downloadURL = 'https://www.example.com/example.csv';
    $distribution[] = $distribution1;
    $doc->distribution = $distribution;
    $dkan8->saveDatasetFilesLocally($doc);
    $destinationPath = 'public://distribution/' . basename($distribution1->downloadURL);
    // Assert that downloadURL value has changed after the function call.
    $this->assertEquals($destinationPath, $doc->distribution[0]->downloadURL);

    // Pass in $doc w/ 2 distributions, each w/ downloadURL.
    $doc = new \stdClass();
    $distribution = array();
    $distribution1 = new \stdClass();
    $distribution1->downloadURL = 'https://www.example.com/example.csv';
    $distribution[] = $distribution1;
    $distribution2 = new \stdClass();
    $distribution2->downloadURL = 'https://www.example.com/example2.csv';
    $distribution[] = $distribution2;
    $doc->distribution = $distribution;
    $dkan8->saveDatasetFilesLocally($doc);
    $destinationPath1 = 'public://distribution/' . basename($distribution1->downloadURL);
    $destinationPath2 = 'public://distribution/' . basename($distribution2->downloadURL);
    // Assert that downloadURL values have changed after the function call.
    $this->assertEquals($destinationPath1, $doc->distribution[0]->downloadURL);
    $this->assertEquals($destinationPath2, $doc->distribution[1]->downloadURL);

    // Pass in $doc w/ distribution w/o downloadURL.
    $doc = new \stdClass();
    $distribution = array();
    $distribution1 = new \stdClass();
    $distribution[] = $distribution1;
    $doc->distribution = $distribution;
    $dkan8->saveDatasetFilesLocally($doc);
    // Assert that the value is still not set after the function call.
    $this->assertFalse(isset($distribution->downloadURL));

    // Pass in $doc w/o distribution (w/o downloadURL)
    $doc = new \stdClass();
    $dkan8->saveDatasetFilesLocally($doc);
    // Assert that the value is still not set after the function call.
    $this->assertFalse(isset($distribution->downloadURL));
  }

}
