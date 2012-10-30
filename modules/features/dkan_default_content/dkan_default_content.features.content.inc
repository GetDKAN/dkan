<?php
/**
 * @file
 * dkan_default_content.features.content.inc
 */

/**
 * Implements hook_content_defaults().
 */
function dkan_default_content_content_defaults() {
  $content = array();

  $content['aed-district-names'] = (object) array(
    'title' => 'District Names',
    'status' => '1',
    'promote' => '0',
    'sticky' => '0',
    'type' => 'resource',
    'language' => 'und',
    'created' => '1351485646',
    'comment' => '0',
    'translate' => '0',
    'machine_name' => 'aed-district-names',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'You can see this data plotted on a map, by clicking on \'Map\' below. Individual data records can be seen by clicking on each point.
',
          'summary' => '',
          'format' => 'plain_text',
          'safe_value' => '<p>You can see this data plotted on a map, by clicking on &#039;Map&#039; below. Individual data records can be seen by clicking on each point.</p>
',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_upload' => array(
      'und' => array(
        0 => array(
          'service_id' => NULL,
          'grid' => NULL,
          'graph' => NULL,
          'map' => NULL,
          'timeline' => NULL,
          'transform' => NULL,
          'filename' => 'district_centerpoints.csv',
          'uri' => 'public://district_centerpoints.csv',
          'filemime' => 'text/csv',
          'filesize' => '0',
          'status' => '1',
          'rdf_mapping' => array(),
          'file' => 'ImxvbiIsImxhdCIsIlVuaXRfVHlwZSIsIkRpc3RfTmFtZSIsIlByb3ZfTmFtZSIsIkRhcmlfRGlzdCIsIkRhcmlfUHJvdiIsIkRpc3RfSUQiLCJQcm92X0lEIgo2MS4zMywzMi40LCJEaXN0cmljdCIsIlFhbGEtZS1LYWgiLCJGYXJhaCIsItmC2YTYudmHINqp2KfZhyIsItmB2LHYp9mHIiwzMTA2LDMxCjYyLjA2LDMyLjQ5LCJEaXN0cmljdCIsIlB1c2h0IFJvZCIsIkZhcmFoIiwi2b7YtNiqINix2YjYryIsItmB2LHYp9mHIiwzMTA1LDMxCjYxLjM3LDMyLjExLCJEaXN0cmljdCIsIlNoaWIgS29oIiwiRmFyYWgiLCLZjti024zYqCDaqdmI2YciLCLZgdix2KfZhyIsMzEwNywzMQo2Mi4yMywzMi4yNCwiUHJvdmluY2lhbF9DZW50ZXIiLCJGYXJhaCIsIkZhcmFoIiwi2YHYsdin2YciLCLZgdix2KfZhyIsMzEwMSwzMQo2MS44MywzMS4zMiwiRGlzdHJpY3QiLCJLYW5nIiwiTmltcm96Iiwi2qnZhtqvIiwi2YbbjNmF2LHZiNiyIiwzNDAyLDM0CjYyLjA0LDMwLjkxLCJQcm92aW5jaWFsX0NlbnRlciIsIlphcmFuaiIsIk5pbXJveiIsItiy2LHZhtisIiwi2YbbjNmF2LHZiNiyIiwzNDAxLDM0CjYyLjg4LDMyLjIxLCJEaXN0cmljdCIsIkJha3dhIiwiRmFyYWgiLCLYqNqp2YjYp9mHIiwi2YHYsdin2YciLDMxMDIsMzEKNjUuMTcsMzAuMTIsIkRpc3RyaWN0IiwiUmVnIiwiS2FuZGFoYXIiLCLYsduM2q8iLCLaqdmG2K',
        ),
      ),
    ),
  );

  $content['afghan-election-districts'] = (object) array(
    'title' => 'Afghanistan Election Districts',
    'status' => '1',
    'promote' => '0',
    'sticky' => '0',
    'type' => 'dataset',
    'language' => 'und',
    'created' => '1351612344',
    'comment' => '0',
    'translate' => '0',
    'machine_name' => 'afghan-election-districts',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'This data created by the National Democratic Institute (NDI) in partnership with Development Seed, a Washington, D.C.-based online communications consultancy, is designed to make data from the August 20, 2009, Afghanistan presidential election accessible and transparent. We have provided this csv file as there is no download for the whole dataset. There are many more files on the website.',
          'summary' => '',
          'format' => 'plain_text',
          'safe_value' => '<p>This data created by the National Democratic Institute (NDI) in partnership with Development Seed, a Washington, D.C.-based online communications consultancy, is designed to make data from the August 20, 2009, Afghanistan presidential election accessible and transparent. We have provided this csv file as there is no download for the whole dataset. There are many more files on the website.</p>
',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_license' => array(
      'und' => array(
        0 => array(
          'value' => 'odc-by',
          'format' => NULL,
          'safe_value' => 'odc-by',
        ),
      ),
    ),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => '1',
        ),
        1 => array(
          'tid' => '2',
        ),
        2 => array(
          'tid' => '3',
        ),
        3 => array(
          'tid' => '4',
        ),
      ),
    ),
  );

return $content;
}
