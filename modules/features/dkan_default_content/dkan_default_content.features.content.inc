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
          'safe_value' => 'You can see this data plotted on a map, by clicking on \'Map\' below. Individual data records can be seen by clicking on each point.',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_upload' => array(
      'und' => array(
        0 => array(
          'fid' => '2',
          'service_id' => NULL,
          'grid' => '1',
          'graph' => '0',
          'map' => '1',
          'timeline' => '0',
          'transform' => '0',
          'uid' => '1',
          'filename' => 'district_centerpoints (1).csv',
          'uri' => 'public://district_centerpoints (1).csv',
          'filemime' => 'text/csv',
          'filesize' => '32378',
          'status' => '1',
          'timestamp' => '1351485718',
          'rdf_mapping' => array(),
        ),
      ),
    ),
    'field_format' => array(
      'und' => array(
        0 => array(
          'tid' => '24',
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
    'created' => '1351485587',
    'comment' => '0',
    'translate' => '0',
    'machine_name' => 'afghan-election-districts',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'This data created by the National Democratic Institute (NDI) in partnership with Development Seed, a Washington, D.C.-based online communications consultancy, is designed to make data from the August 20, 2009, Afghanistan presidential election accessible and transparent. We have provided this csv file as there is no download for the whole dataset. There are many more files on the website.',
          'summary' => '',
          'format' => 'plain_text',
          'safe_value' => 'This data created by the National Democratic Institute (NDI) in partnership with Development Seed, a Washington, D.C.-based online communications consultancy, is designed to make data from the August 20, 2009, Afghanistan presidential election accessible and transparent. We have provided this csv file as there is no download for the whole dataset. There are many more files on the website.',
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
          'tid' => '20',
        ),
        1 => array(
          'tid' => '21',
        ),
        2 => array(
          'tid' => '22',
        ),
        3 => array(
          'tid' => '23',
        ),
      ),
    ),
    'field_additional_info' => array(
      'und' => array(
        0 => array(
          'first' => 'Source',
          'second' => 'http://afghanistanelectiondata.org/',
        ),
      ),
    ),
  );

return $content;
}
