<?php

/**
 * @file
 * PHPUnit test file for the GetRemoteFileInfoTest class.
 */

use dkanDataset\getRemoteFileInfo;

/**
 * Tests for GetRemoteFileInfo class.
 *
 * @class GetRemoteFileInfoTest
 */
class GetRemoteFileInfoTest extends PHPUnit_Framework_TestCase {

  /**
   * Helper method for the test data.
   */
  public function getHeaders($url) {
    switch ($url) {
      case 'https://data.wa.gov/api/views/mu24-67ke/rows.csv?accessType=DOWNLOAD':
        return 'HTTP/1.1 200 OK
Server: nginx
Date: Mon, 28 Sep 2015 19:38:32 GMT
Content-Type: text/csv; charset=utf-8
Content-Length: 8004
Connection: keep-alive
Access-Control-Allow-Origin: *
ETag: "c633f8807b3c7e27082d8d4d05bb6a16"
Last-Modified: Tue, 28 Oct 2014 08:38:05 PDT
Content-disposition: attachment; filename=Hospital_Inpatient_Discharges_by_DRG__Northwest__FY2011.csv
Cache-Control: public, must-revalidate, max-age=21600
X-Socrata-Region: production
Age: 5730';
    }
  }

  /**
   * Run test URLs threw the getRemoteFileInfo class.
   */
  public function testUrls() {
    $url = 'https://data.wa.gov/api/views/mu24-67ke/rows.csv?accessType=DOWNLOAD';
    $fileInfo = new getRemoteFileInfo($url, 'test', TRUE);
    $this->assertEquals($fileInfo->getType(), 'text/csv');
    $this->assertEquals($fileInfo->getName(), 'Hospital_Inpatient_Discharges_by_DRG__Northwest__FY2011.csv');
  }

  /**
   * Test URL extension.
   *
   * Mimetype can have multiple extensions associated to it. This test make sure
   * that the returned extension matches both the Mimetype and the actual file
   * extension.
   */
  public function testUrlExtension() {
    $url = "http://opendata.comune.bari.it/dataset/a66a3b73-5a39-42b6-84ac-7c8260f3f6d1/resource/c397042b-f530-46f6-8987-210b3a215ff4/download/20121212t163303albo.xls";
    $fileInfo = new getRemoteFileInfo($url, 'test', TRUE);
    $this->assertEquals($fileInfo->getType(), 'application/vnd.ms-excel');
    $this->assertEquals($fileInfo->getExtension(), 'xls');
  }

}
