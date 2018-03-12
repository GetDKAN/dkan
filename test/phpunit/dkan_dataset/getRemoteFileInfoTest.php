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
    $urls = [];
    $urls[0]['url'] = 'https://data.wa.gov/api/views/mu24-67ke/rows.csv?accessType=DOWNLOAD';
    $urls[0]['type'] = 'text/csv';
    $urls[0]['extension'] = 'csv';
    $urls[0]['name'] = "Hospital_Inpatient_Discharges_by_DRG__Northwest__FY2011.csv";

    $urls[1]['url'] = "https://data.ca.gov/node/1801/download";
    $urls[1]['type'] = 'text/csv';
    $urls[1]['extension'] = 'csv';
    $urls[1]['name'] = "uw_supplier_data030618.csv";

    $urls[2]['url'] = "https://s3.amazonaws.com/dkan-default-content-files/files/albo.xls";
    $urls[2]['type'] = 'application/vnd.ms-excel';
    $urls[2]['extension'] = 'xls';
    $urls[2]['name'] = "albo.xls";

    $urls[3]['url'] = "https://data.chhs.ca.gov/dataset/596b5eed-31de-4fd8-a645-249f3f9b19c4/resource/57da6c9a-41a7-44b0-ab8d-815ff2cd5913/download/cscpopendata.csv";
    $urls[3]['type'] = 'text/csv';
    $urls[3]['extension'] = 'csv';
    $urls[3]['name'] = "cscpopendata.csv";

    foreach ($urls as $key => $info) {
      $fileInfo = new getRemoteFileInfo($info['url'], 'test', TRUE);
      $this->assertEquals($fileInfo->getType(), $info['type']);
      $this->assertEquals($fileInfo->getExtension(), $info['extension']);
      $this->assertEquals($fileInfo->getName(), $info['name']);
    }
  }

}
