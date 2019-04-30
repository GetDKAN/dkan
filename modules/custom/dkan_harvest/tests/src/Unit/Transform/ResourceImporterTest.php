<?php

namespace Drupal\Tests\dkan_harvest\Unit\Transform;

use Dkan\PhpUnit\DkanTestBase;
use Drupal\dkan_harvest\Transform\ResourceImporter;
use Drupal\dkan_harvest\Load\IFileHelper;

/**
 * @group dkan
 */
class ResourceImporterTest extends DkanTestBase {

  /**
   * Data provider for testRun.
   *
   * @return array
   */
  public function dataTestRun() {

    $datasetJson = <<<EOF
{
  "accessLevel": "public",
  "@type": "dcat:Dataset",
  "identifier": "testid",
  "title": "Test Dataset",
  "distribution": [
    {
    "@type": "dcat:Distribution",
    "downloadURL": "http://example.com/dist/test.csv",
    "mediaType": "text/csv"
    }
  ]
}
EOF;

    $originalDataset = json_decode($datasetJson);
    $modifiedDataset = json_decode($datasetJson);
    $modifiedDataset->distribution[0]->downloadURL = 'http://localhost/site/default/files/distribution/testid/test.csv';

    $expected[] = 'http://example.com/dist/test.csv';
    $expected[] = 'http://localhost/site/default/files/distribution/testid/test.csv';

    return [
      [[$originalDataset], $originalDataset, $expected[0]],
      [[$originalDataset], $modifiedDataset, $expected[1]]
    ];

  }

  /**
   * Test the ResourceImporter::run method.
   *
   * @dataProvider dataTestRun
   *
   * @param object $datasets
   * @param object $modifiedDataset
   * @param string $expected
   */
  public function testRun($datasets, $modifiedDataset, $expected) {

    // Create ResourceImporter stub.
    $resourceImporterStub = $this->getMockBuilder(ResourceImporter::class)
      ->setMethods(['updateDistributions'])
      ->disableOriginalConstructor()
      ->getMock();
    $resourceImporterStub->method('updateDistributions')
      ->willReturn($modifiedDataset);

    // Assert
    $resourceImporterStub->run($datasets);
    $this->assertEquals($expected, $datasets[0]->distribution[0]->downloadURL);

  }

  /**
   * Data provider for testUpdateDistributions.
   *
   * @return array
   */
  public function dataTestUpdateDistributions() {

    $datasetJson = <<<EOF
{
  "accessLevel": "public",
  "@type": "dcat:Dataset",
  "identifier": "testid",
  "title": "Test Dataset",
  "distribution": [
    {
    "@type": "dcat:Distribution",
    "downloadURL": "http://example.com/dist/test.csv",
    "mediaType": "text/csv"
    }
  ]
}
EOF;

    $dataset = json_decode($datasetJson);
    $dataset2 = json_decode($datasetJson);

    $updatedDist = $dataset2->distribution[0];
    $updatedDist->downloadURL = 'http://localhost/site/default/files/distribution/testid/test.csv';

    return [
      [$dataset, $dataset->distribution[0], 'http://example.com/dist/test.csv'],
      [$dataset, $updatedDist, 'http://localhost/site/default/files/distribution/testid/test.csv'],
    ];

  }

  /**
   * Test the ResourceImporter::updateDistributions method.
   *
   * @dataProvider dataTestUpdateDistributions
   *
   * @param $dataset
   * @param $dist
   * @param $expected
   */
  public function testUpdateDistributions($dataset, $dist, $expected) {

    // Create ResourceImporter stub.
    $resourceImporterStub = $this->getMockBuilder(ResourceImporter::class)
      ->setMethods(['updateDownloadUrl'])
      ->disableOriginalConstructor()
      ->getMock();

    $resourceImporterStub->method('updateDownloadUrl')
      ->willReturn($dist);

    // Assert
    $actualDataset = $this->invokeProtectedMethod($resourceImporterStub, 'updateDistributions', $dataset);
    $this->assertEquals($expected, $actualDataset->distribution[0]->downloadURL);

  }

  /**
   * Data provider for testUpdateDownloadUrl.
   *
   * @return array
   */
  public function dataTestUpdateDownloadUrl() {

    $datasetJson = <<<EOF
{
  "accessLevel": "public",
  "@type": "dcat:Dataset",
  "identifier": "testid",
  "title": "Test Dataset",
  "distribution": [
    {
    "@type": "dcat:Distribution",
    "downloadURL": "http://example.com/dist/test.csv",
    "mediaType": "text/csv"
    }
  ]
}
EOF;

    $dataset = json_decode($datasetJson);
    $newUrl = 'http://localhost/site/default/files/distribution/testid/test.csv';

    return [
      [$dataset, $dataset->distribution[0], FALSE, "http://example.com/dist/test.csv"],
      [$dataset, $dataset->distribution[0], $newUrl, $newUrl],
    ];

  }

  /**
   * Test the ResourceImporter::updateDistributions method.
   *
   * @dataProvider dataTestUpdateDownloadUrl
   *
   * @param $dataset
   * @param $dist
   * @param $url
   * @param $expected
   */
  public function testUpdateDownloadUrl($dataset, $dist, $url, $expected) {

    // Create ResourceImporter stub.
    $resourceImporterStub = $this->getMockBuilder(ResourceImporter::class)
      ->setMethods(['saveFile'])
      ->disableOriginalConstructor()
      ->getMock();

    $resourceImporterStub->method('saveFile')
      ->willReturn($url);

    // Assert
    $actualDist = $this->invokeProtectedMethod($resourceImporterStub, 'updateDownloadUrl', $dataset, $dist);
    $this->assertEquals($expected, $actualDist->downloadURL);

  }

  /**
   * Data provider for testSaveFile.
   *
   * @return array Array of arguments.
   */
  public function dataTestSaveFile() {

    return [
      ["data1.csv", "testid1", TRUE, "http://localhost/site/default/files/distribution/testid1/data1.csv"],
      ["data2.csv", "testid2", FALSE, FALSE],
    ];

  }

  /**
   * Tests the ResourceImporter::saveFile() method.
   *
   * @dataProvider dataTestSaveFile
   *
   * @param string $filename
   * @param string $datasetId
   * @param bool $isUrlValid
   * @param string $expected
   */
  public function testSaveFile($filename, $datasetId, $isUrlValid, $expected) {

    // Create FileHelper stub.
    $fileHelperStub = $this->createMock(IFileHelper::class);
    $fileHelperStub->method('prepareDir')
      ->willReturn(TRUE);
    $fileHelperStub->method('retrieveFile')
      ->willReturn($isUrlValid);
    $fileHelperStub->method('fileCreate')
      ->willReturn("http://localhost/site/default/files/distribution/$datasetId/$filename");

    // Create ResourceImporter stub.
    $resourceImporterStub = $this->getMockBuilder(ResourceImporter::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();
    $this->writeProtectedProperty($resourceImporterStub, 'fileHelper', $fileHelperStub);

    // Assert.
    $actual = $resourceImporterStub->saveFile("http://example.com/dist/$filename", $datasetId);
    $this->assertEquals($expected, $actual);

  }

}
