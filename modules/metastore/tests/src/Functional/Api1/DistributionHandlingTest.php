<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

use function PHPUnit\Framework\assertEquals;

class DistributionHandlingTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  public function testPostDataDictionary() {
    $response = $this->addDataDictionary();
    $this->assertEquals(201, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"201MetadataCreated"}->content->{"application/json"}->schema;

    $this->assertJsonIsValid($responseSchema, $responseBody);
    $dictionaryId = $responseBody->identifier;
    assertEquals($dictionaryId, "17c142da-b433-478f-a2db-b0a36fa9c335");

    $datasetMetadata = $this->getSampleDataset(0);
    $uri = "dkan://metastore/schema/data-dictionary/items/17c142da-b433-478f-a2db-b0a36fa9c335";
    $url = \Drupal::service('dkan.metastore.url_generator')->generateAbsoluteString($uri);
    $this->assertEquals("http://web/api/1/schema/data-dictionary/items/17c142da-b433-478f-a2db-b0a36fa9c335", $url);
    $datasetMetadata->distribution[0]->describedBy = $url;
    $datasetMetadata->distribution[0]->describedByType = 'application/vnd.tableschema+json';

    $response = $this->post($datasetMetadata, FALSE);
    $responseBody = json_decode($response->getBody());
    $datasetId = $responseBody->identifier;

    $dataset_endpoint = $this->getEndpoint();
    $response = $this->http->get("$dataset_endpoint/$datasetId");
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($url, $responseBody->distribution[0]->describedBy);

    // Now try with URI.
    $patch = (object) ["distribution" => [clone $responseBody->distribution[0]]];
    $patch->distribution[0]->describedBy = $uri;
    $response = $this->http->patch("$dataset_endpoint/$datasetId", [
      RequestOptions::JSON => $patch,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());

    $response = $this->http->get("$dataset_endpoint/$datasetId");
    $this->assertEquals($url, $responseBody->distribution[0]->describedBy);
  }

  private function addDataDictionary() {    
    $dictionaryId = "17c142da-b433-478f-a2db-b0a36fa9c335";
    $dictionary = (object) [
      "identifier" => $dictionaryId,
      "title" => "Bike lanes data dictionary",
      "data" => (object) [
        "fields" => [
          (object) [
            "name" => "objectid",
            "title" => "OBJECTID",
            "type" => "integer",
            "description" => "Internal feature number.",
          ],
          (object) [
            "name" => "roadway",
            "title" => "ROADWAY",
            "type" => "string",
            "description" => "A unique 8-character identification number assigned to a roadway or section of a roadway either On or Off the State Highway System for which information is maintained in the Department's Roadway Characteristics Inventory (RCI).",
          ],
          (object) [
            "name" => "road_side",
            "title" => "ROAD_SIDE",
            "type" => "string",
            "constraints" => (object) [
              "maxLength" => 1,
              "minLength" => 1,
              "enum" => ["R", "L", "C"],
            ],
            "description" => "Side of the road. C = Composite; L = Left side; R = Right side",
          ],
          (object) [
            "name" => "lncd",
            "title" => "LNCD",
            "type" => "integer",
            "constraints" => (object) [
              "maxLength" => 1,
              "minLength" => 1,
              "maximum" => 5,
              "minimum" => 0,
            ],
            "description" => "Codes 0 = UNDESIGNATED; 1 = DESIGNATED; 2 = BUFFERED; 3 = COLORED; 4 = BOTH 2 AND 3; 5 = SHARROW",
          ],
          (object) [
            "name" => "descr",
            "title" => "DESCR",
            "type" => "string",
            "constraints" => (object) [
              "maxLength" => 30,
              "enum" => ["UNDESIGNATED", "DESIGNATED"],
            ],
            "description" => "Designation description.",
          ],
          (object) [
            "name" => "begin_post",
            "title" => "BEGIN_POST",
            "type" => "number",
            "description" => "Denotes the lowest milepoint for the record.",
          ],
          (object) [
            "name" => "end_post",
            "title" => "END_POST",
            "type" => "number",
            "description" => "Denotes the highest milepoint for the record.",
          ],
          (object) [
            "name" => "shape_len",
            "title" => "Shape_Leng",
            "type" => "number",
            "description" => "Length in meters",
          ],
        ],
      ],
    ];

    $response = $this->http->post('api/1/metastore/schemas/data-dictionary/items', [
      RequestOptions::JSON => $dictionary,
      RequestOptions::AUTH => $this->auth,
    ]);

    return $response;
  }

}
