<?php
/**
 * @file
 * Contains helper functions that build content arrays to be loaded
 * as demo content.
 */

/**
 * Returns an array of available datasets slugs to use wit
 */
function dkan_default_content_available_datasets(){
  return array(
    'wisconsin_polling',
    'national_foreclosure',
    'gold_prices',
    'afghan_election_districts'
  );
}

/**
 * Builds an array of Wisconsin Polling Places Dataset.
 */
function dkan_default_content_wisconsin_polling_dataset() {
  return array(
    'title' => 'Wisconsin Polling Places',
    'sort_value' => '11',
    'created' => '1360559620',
    'name' => 'admin',
    'uid' => 1,
    'machine_name' => 'polling',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'Polling places in the state of Wisconsin',
          'summary' => '',
          'format' => 'html',
          'safe_value' => '<p>Polling places in the state of Wisconsin</p>',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_license' => array(
      'und' => array(
        0 => array(
          'value' => 'cc-by',
          'format' => NULL,
          'safe_value' => 'cc-by',
        ),
      ),
    ),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => '2',
        ),
      ),
    ),
    'og_group_ref' => array(
      'und' => array(
        0 => array(
          'title' => 'Geospatial Data Explorer Examples',
        ),
      ),
    ),
    'field_spatial' => array(
      'und' => array(
        0 => array(
          'wkt' => 'POLYGON ((-90.415429 46.568478, -90.229213 46.508231, -90.119674 46.338446, -89.09001 46.135799, -88.662808 45.987922, -88.531362 46.020784, -88.10416 45.922199, -87.989145 45.796229, -87.781021 45.675736, -87.791975 45.500474, -87.885083 45.363551, -87.649574 45.341643, -87.742682 45.199243, -87.589328 45.095181, -87.627666 44.974688, -87.819359 44.95278, -87.983668 44.722749, -88.043914 44.563917, -87.928898 44.536533, -87.775544 44.640595, -87.611236 44.837764, -87.403112 44.914442, -87.238804 45.166381, -87.03068 45.22115, -87.047111 45.089704, -87.189511 44.969211, -87.468835 44.552964, -87.545512 44.322932, -87.540035 44.158624, -87.644097 44.103854, -87.737205 43.8793, -87.704344 43.687607, -87.791975 43.561637, -87.912467 43.249452, -87.885083 43.002989, -87.76459 42.783912, -87.802929 42.493634, -88.788778 42.493634, -90.639984 42.510065, -90.711184 42.636034, -91.067185 42.75105, -91.143862 42.909881, -91.176724 43.134436, -91.056231 43.254929, -91.204109 43.353514, -91.215062 43.501391, -91.269832 43.616407, -91.242447 43.775238, -91.43414 43.994316, -91.592971 44.032654, -91.877772 44.202439, -91.927065 44.333886, -92.233773 44.443425, -92.337835 44.552964, -92.545959 44.569394, -92.808852 44.750133, -92.737652 45.117088, -92.75956 45.286874, -92.644544 45.440228, -92.770513 45.566198, -92.885529 45.577151, -92.869098 45.719552, -92.639067 45.933153, -92.354266 46.015307, -92.29402 46.075553, -92.29402 46.667063, -92.091373 46.749217, -92.014696 46.705401, -91.790141 46.694447, -91.09457 46.864232, -90.837154 46.95734, -90.749522 46.88614, -90.886446 46.754694, -90.55783 46.584908))',
          'geo_type' => 'polygon',
          'lat' => '44.635',
          'lon' => '-90.0142',
          'left' => '-92.8855',
          'top' => '46.9573',
          'right' => '-87.0307',
          'bottom' => '42.4936',
          'srid' => null,
          'accuracy' => null,
          'source' => null,
        )
      )
    ),
    'field_contact_email' => array(
      'und' => array(
        0 => array(
          'value' => variable_get('site_mail', NULL),
        ),
      ),
    ),
  );
}

/*
 * Builds an array of Winsconsin Polling Places Dataset's resources
 */
function dkan_default_content_wisconsin_polling_resources(){
  return array(
    array(
      'exported_path' => 'dataset/wisconsin-polling-places/resource/0a6ef5bc-340c-40d7-b8ea-f7f12c390b1f',
      'title' => 'Madison Polling Places',
      'uid' => 1,
      'status' => '1',
      'sort_value' => '10',
      'promote' => '0',
      'sticky' => '0',
      'type' => 'resource',
      'language' => 'und',
      'created' => '1360559790',
      'comment' => '0',
      'translate' => '0',
      'machine_name' => 'polling-madison',
      'body' => array(
        'und' => array(
          0 => array(
            'value' => 'This is a list and map of polling places in Madison, WI. 

  Original data here: 
  https://data.cityofmadison.com/Polling-Places/Polling-Places/rtyh-6ucr
  ',
            'summary' => '',
            'format' => 'html',
            'safe_value' => '<p>This is a list and map of polling places in Madison, WI.</p>

  <p>Original data here: 
  <a href="https://data.cityofmadison.com/Polling-Places/Polling-Places/rtyh-6ucr">https://data.cityofmadison.com/Polling-Places/Polling-Places/rtyh-6ucr</a></p>
  ',
            'safe_summary' => '',
          ),
        ),
      ),
      'field_format' => array(
        'und' => array(
          0 => array(
            'tid' => '8',
          ),
        ),
      ),
      'field_upload' => array(
        'und' => array(
          0 => array(
            'service_id' => NULL,
            'grid' => '1',
            'graph' => '0',
            'map' => '1',
            'timeline' => '0',
            'transform' => '0',
            'filename' => 'Polling_Places_Madison.csv',
            'uri' => 'public://Polling_Places_Madison.csv',
            'filemime' => 'text/csv',
            'filesize' => '17468',
            'status' => '1',
            'uuid' => 'd8c82777-f8ab-4020-ab1a-177b9774c1b5',
            'rdf_mapping' => array(),
            'file' => 'V2FyZCxBbGRlcm1hbmljIERpc3RyaWN0LE5hbWUsQWRkcmVzcyxDb21tZW50cyxIYW5kaWNhcCBBY2Nlc3NpYmxlLExhdGl0dWRlLExvbmdpdHVkZQ0KMSwxNixHbGVuZGFsZSBFbGVtZW50YXJ5IFNjaG9vbCwiMTIwMSBUb21wa2lucyBEciBNYWRpc29uLCBXSSAoNDMuMDU4NTM0MDUzMjUwNTI2LCAtODkuMzEyNDY4NTk1MzQ5NjkpIixMTUMsVFJVRSw0My4wNTg1MzQwNSwtODkuMzEyNDY4Ng0KMiwxNixHbGVuZGFsZSBFbGVtZW50YXJ5IFNjaG9vbCwiMTIwMSBUb21wa2lucyBEciBNYWRpc29uLCBXSSAoNDMuMDU4NTM0MDUzMjUwNTI2LCAtODkuMzEyNDY4NTk1MzQ5NjkpIixMTUMsVFJVRSw0My4wNTg1MzQwNSwtODkuMzEyNDY4Ng0KMywxNixDaXR5IENodXJjaCwiNDkwOSBCdWNrZXllIFJkIE1hZGlzb24sIFdJICg0My4wNzM1NjE3MjQzNjAxNiwgLTg5LjMxMTk1NTk4MTc5MykiLExvd2VyIExldmVsIC0gUmVhciBFbnRyYW5jZSxUUlVFLDQzLjA3MzU2MTcyLC04OS4zMTE5NTU5OA0KNCwxNixDaXR5IENodXJjaCwiNDkwOSBCdWNrZXllIFJkIE1hZGlzb24sIFdJICg0My4wNzM1NjE3MjQzNjAxNiwgLTg5LjMxMTk1NTk4MTc5MykiLExvd2VyIExldmVsIC0gUmVhciBFbnRyYW5jZSxUUlVFLDQzLjA3MzU2MTcyLC04OS4zMTE5NTU5OA0KNSwxNixFbHZlaGplbSBFbGVtZW50YXJ5LCI1MTA2IEFjYWRlbXkgRHIgTWFkaXNvbiwgV0kgKDQzLjA3Nzk4NjM4ODc0NDMxLCAtODkuMjk0OTQ5ODAyMjE4OTIpIiwiV2VzdCBIYWxsIGJ5IEd5bSBCLCBHeW0gQiB1c2VkIGluIE5vdmVtYmVyIixUUlVFLDQzLjA3Nzk4NjM5LC04OS4yOTQ5NDk4DQo2LDE2LEVsdmVoamVtIEVsZW1lbnRhcnksIjUxMDYgQWNhZGVteSBEciBNYWRpc29uLCBXSSAoNDMuMDc3OTg2Mzg4NzQ0MzEsIC04OS4yOTQ5NDk4MDIyMTg5MikiLCJXZXN0IEhhbGwgYnkgR3ltIEIsIEd5bSBCIHVzZWQgaW4gTm92ZW1iZXIiLFRSVUUsNDMuMDc3OTg2MzksLTg5LjI5NDk0OTgNCjcsMTYsQ2l0eSBDaHVyY2gsIjQ5MDkgQnVja2V5ZSBSZCBNYWRpc29uLCBXSSAoNDMuMDczNTYxNzI0MzYwMTYsIC04OS4zMTE5NTU5ODE3OTMpIixMb3dlciBMZXZlbCAtIFJlYXIgRW50cmFuY2UsVFJVRSw0My4wNzM1NjE3MiwtODkuMzExOTU1OTgNCjgsMyxEb29yIENyZWVrIENodXJjaCwiNjYwMiBEb21pbmlvbiBEciBNYWRpc29uLCBXSSAoNDMuMDkxMDQ0NTQ0NjA5NzYsIC04OS4yNjQ0NDQ3OTg5ODUwNCkiLEd5bSxUUlVFLDQzLjA5MTA0NDU0LC04OS4yNjQ0NDQ4DQo5LDMsRWFzdCBQb2xpY2UgRGlzdHJpY3QsIjgwOSBUaG9tcHNvbiBEciBNYWRpc29uLCBXSSAoNDMuMTA4OTE3MDI4ODcxNTIsIC04OS4yOTgyNDM3NDA1MjcyOCkiLENvbW11bml0eSByb29tLFRSVUUsNDMuMTA4OTE3MDMsLTg5LjI5ODI0Mzc0DQoxMCwzLEtlbm5lZHkgRWxlbWVudGFyeSwiMjIxIE1lYWRvd2xhcmsgRHIgTWFkaXNvbiwgV0kgKDQzLjA5NDQ1Mjk3NTEwMDgxLCAtODkuMjk4MTY5Mzc1MDgzODMpIiwiTE1DIGZvciBtb3N0IGVsZWN0aW9ucywgZ3ltIGZvciBOb3ZlbWJlciIsVFJVRSw0My4wOTQ0NTI5OCwtODkuMjk4MTY5MzgNCjExLDMsQW1lcmljYW4gRmFtaWx5IEluc3VyYW5jZSwiMzAyIFdhbGJyaWRnZSBBdmUgTWFkaXNvbiwgV0kgKDQzLjEwMjg0NDA4NTM1NjU2NiwgLTg5LjMxMTQ4MjAxMDkwOTA5KSIsV2FsYnJpZGdlIFJvb20gLSBwYXJrIGluIGZyb250ICYgdXNlIG1haW4gZW50cmFuY2UsVFJVRSw0My4xMDI4NDQwOSwtODkuMzExNDgyMDENCjEyLDMsTmV3IEJlZ2lubmluZ3MgQ2h1cmNoLCI2MDIgQWNld29vZCBCbHZkIE1hZGlzb24sIFdJICg0My4wODkzNDQ4MjU5NzQzNjUsIC04OS4zMDIzNDIyMjkyOTk3NykiLEVudGVyIG9mZiBwYXJraW5nIGxvdCAtIGxvd2VyIGxldmVsLFRSVUUsNDMuMDg5MzQ0ODMsLTg5LjMwMjM0MjIzDQoxMywxNSxMYUZvbGxldHRlIEhpZ2ggU2Nob29sLCI3MDAgUGZsYXVtIFJkIE1hZGlzb24sIFdJICg0My4wNjIxNDIxNDg3MTA2OCwgLTg5LjMxODc0OTAxNzA3NjQpIixHeW0sVFJVRSw0My4wNjIxNDIxNSwtODkuMzE4NzQ5MDINCjE0LDE1LExhRm9sbGV0dGUgSGlnaCBTY2hvb2wsIjcwMCBQZmxhdW0gUmQgTWFkaXNvbiwgV0kgKDQzLjA2MjE0MjE0ODcxMDY4LCAtODkuMzE4NzQ5MDE3MDc2NCkiLEd5bSxUUlVFLDQzLjA2MjE0MjE1LC04OS4zMTg3NDkwMg0KMTUsMTUsTGFGb2xsZXR0ZSBIaWdoIFNjaG9vbCwiNzAwIFBmbGF1bSBSZCBNYWRpc29uLCBXSSAoNDMuMDYyMTQyMTQ4NzEwNjgsIC04OS4zMTg3NDkwMTcwNzY0KSIsR3ltLFRSVUUsNDMuMDYyMTQyMTUsLTg5LjMxODc0OTAyDQoxNiwxNSxXaGl0ZWhvcnNlIE1pZGRsZSBTY2hvb2wsIjIxOCBTY2hlbmsgU3QgTWFkaXNvbiwgV0kgKDQzLjA5Mjc1NTY3NzIzNTczNiwgLTg5LjMyMzIwMjUwMTk5MTA4KSIsR3ltLFRSVUUsNDMuMDkyNzU1NjgsLTg5LjMyMzIwMjUNCjE3LDE1LEFtZXJpY2FuIEZhbWlseSBJbnN1cmFuY2UsIjMwMiBXYWxicmlkZ2UgQXZlIE1hZGlzb24sIFdJICg0My4xMDI4NDQwODUzNTY1NjYsIC04OS4zMTE0ODIwMTA5MDkwOSkiLFdhbGJyaWRnZSBSb29tIC0gcGFyayBpbiBmcm9udCAmIHVzZSBtYWluIGVudHJhbmNlLFRSVUUsNDMuMTAyODQ0MDksLTg5LjMxMTQ4MjAxDQoxOCwxNSxIeS1WZWUsIjM4MDEgV2FzaGluZ3RvbiBBdmUgTWFkaXNvbiwgV0kgKDQzLjExODg3NDQzNTA1NTIzLCAtODkuMzIxMjYzNDY5MTg5KSIsQ29tbXVuaXR5IFJvb20sVFJVRSw0My4xMTg4NzQ0NCwtODkuMzIxMjYzNDcNCjE5LDE1LE1hZGlzb24gQXJtb3J5LCIxNDAyIFdyaWdodCBTdCBNYWRpc29uLCBXSSAoNDMuMTE1NzEyOTQ3ODczNDIsIC04OS4zMzA2ODg4NTk2NjMzMykiLCJEcmlsbCBSb29tLCB1c2UgZnJvbnQgZW50cmFuY2Ugb24gV3JpZ2h0IFN0IixUUlVFLDQzLjExNTcxMjk1LC04OS4zMzA2ODg4Ng0KMjAsMTUsTWFkaXNvbiBBcm1vcnksIjE0MDIgV3JpZ2h0IFN0IE1hZGlzb24sIFdJICg0My4xMTU3MTI5NDc4NzM0MiwgLTg5LjMzMDY4ODg1OTY2MzMzKSIsIkRyaWxsIFJvb20sIHVzZSBmcm9udCBlbnRyYW5jZSBvbiBXcmlnaHQgU3QiLFRSVUUsNDMuMTE1NzEyOTUsLTg5LjMzMDY4ODg2DQoyMSwxNyxIeS1WZWUsIjM4MDEgV2FzaGluZ3RvbiBBdmUgTWFkaXNvbiwgV0kgKDQzLjExODg3NDQzNTA1NTIzLCAtODkuMzIxMjYzNDY5MTg5KSIsQ29tbXVuaXR5IFJvb20sVFJVRSw0My4xMTg4NzQ0NCwtODkuMzIxMjYzNDcNCjIyLDE3LFN0cmVldHMgRWFzdCwiNDYwMiBTeWNhbW9yZSBBdmUgTWFkaXNvbiwgV0kgKDQzLjExNDAxODkwOTEyNjEzLCAtODkuMzAzNDkwODE5NDg1MTYpIiwgLFRSVUUsNDMuMTE0MDE4OTEsLTg5LjMwMzQ5MDgyDQoyMywxNyxGaXJlIFN0YXRpb24gIzExLCI0MDExIE1vcmdhbiBXYXkgTWFkaXNvbiwgV0kgKDQzLjE0NDEzMTUyMzk5OTMwNiwgLTg5LjI4MDI5OTg0NjM2MTYzKSIsICxUUlVFLDQzLjE0NDEzMTUyLC04OS4yODAyOTk4NQ0KMjQsMTcsT2Frd29vZCBWaWxsYWdlIFByYWlyaWUgUmlkZ2UsIjU1NjUgVGFuY2hvIERyIE1hZGlzb24sIFdJICg0My4xNTQ3NDc1NDc0MTk3MywgLTg5LjI4MzkwMDAxNjMwMTY0KSIsQ2hhcGVsLFRSVUUsNDMuMTU0NzQ3NTUsLTg5LjI4MzkwMDAyDQoyNSwxNyxFYXN0c2lkZSBMdXRoZXJhbiwiMjMxMCBJbmRlcGVuZGVuY2UgTG4gTWFkaXNvbiwgV0kgKDQzLjEyOTQ3MjU3NDkyMTc1LCAtODkuMzEwMDc2Mzk4NzUxNzMpIixHeW0sVFJVRSw0My4xMjk0NzI1NywtODkuMzEwMDc2NA0KMjYsMTcsRWFzdHNpZGUgTHV0aGVyYW4sIjIzMTAgSW5kZXBlbmRlbmNlIExuIE1hZGlzb24sIFdJICg0My4xMjk0NzI1NzQ5MjE3NSwgLTg5LjMxMDA3NjM5ODc1MTczKSIsR3ltLFRSVUUsNDMuMTI5NDcyNTcsLTg5LjMxMDA3NjQNCjI3LDEyLE1hZGlzb24gQ29sbGVnZSAtIENvbW1lcmNpYWwgQXZlbnVlLCIyMTI1IENvbW1lcmNpYWwgQXZlIE1hZGlzb24sIFdJICg0My4xMDY3NzExMTk3MjY2NSwgLTg5LjM1NzQzMjU0Mjk4NTg1KSIsQ29tbW9ucyxUUlVFLDQzLjEwNjc3MTEyLC04OS4zNTc0MzI1NA0KMjgsMTIsTWFkaXNvbiBDb2xsZWdlIC0gQ29tbWVyY2lhbCBBdmVudWUsIjIxMjUgQ29tbWVyY2lhbCBBdmUgTWFkaXNvbiwgV0kgKDQzLjEwNjc3MTExOTcyNjY1LCAtODkuMzU3NDMyNTQyOTg1ODUpIixDb21tb25zLFRSVUUsNDMuMTA2NzcxMTIsLTg5LjM1NzQzMjU0DQoyOSwxMixFYXN0IEhpZ2ggU2Nob29sLCIyMjIyIFdhc2hpbmd0b24gQXZlIE1hZGlzb24sIFdJICg0My4wOTY0NTgzNzg2NjIzLCAtODkuMzUzOTEwNTU3MTUyMDUpIiwiRmlmdGggU3RyZWV0IGVudHJhbmNlLCBlbnRlciBhdCBkb29yICMxMCxHeW0gaW4gTm92ZW1iZXIiLFRSVUUsNDMuMDk2NDU4MzgsLTg5LjM1MzkxMDU2DQozMCwxMixTaGVybWFuIE1pZGRsZSBTY2hvb2wsIjE2MTAgUnVza2luIFN0IE1hZGlzb24sIFdJICg0My4xMTc4NDQzNDEwODI5LCAtODkuMzYxNzYwNDM1Nzc1NjQpIixXb29kIEd5bSxUUlVFLDQzLjExNzg0NDM0LC04OS4zNjE3NjA0NA0KMzEsMTIsU3QgUGF1bCBMdXRoZXJhbiwiMjEyNiBTaGVybWFuIEF2ZSBNYWRpc29uLCBXSSAoNDMuMTAxNzU0NjY1NDQ0OCwgLTg5LjM2NTEzODI2OTIyNzAzKSIsRmVsbG93c2hpcCBIYWxsIC0gZW50ZXIgb2ZmIHJlYXIgcGFya2luZyBsb3QsVFJVRSw0My4xMDE3NTQ2NywtODkuMzY1MTM4MjcNCjMyLDEyLFBhY2tlcnMgQXZlIFRvd25ob21lcywiMTkyNyBOb3J0aHBvcnQgRHIgTWFkaXNvbiwgV0kgKDQzLjEyODI0NDQzNTUzMTcsIC04OS4zNTc2NDM2ODA3NTU0NikiLCAsVFJVRSw0My4xMjgyNDQ0NCwtODkuMzU3NjQzNjgNCjMzLDEyLFdhcm5lciBQYXJrIENvbW11bml0eSBSZWNyZWF0aW9uIENlbnRlciwiMTYyNSBOb3J0aHBvcnQgRHIgTWFkaXNvbiwgV0kgKDQzLjEzMjI2MTkwMzg3NzYyLCAtODkuMzY3MzU0MDgwMTkzOCkiLE1lZXRpbmcgUm9vbSxUUlVFLDQzLjEzMjI2MTksLTg5LjM2NzM1NDA4DQozNCwxOCxXYXJuZXIgUGFyayBDb21tdW5pdHkgUmVjcmVhdGlvbiBDZW50ZXIsIjE2MjUgTm9ydGhwb3J0IERyIE1hZGlzb24sIFdJICg0My4xMzIyNjE5MDM4Nzc2MiwgLTg5LjM2NzM1NDA4MDE5MzgpIixNZWV0aW5nIFJvb20sVFJVRSw0My4xMzIyNjE5LC04OS4zNjczNTQwOA0KMzUsMTgsTWVuZG90YSBFbGVtZW50YXJ5LCI0MDAyIFNjaG9vbCBSZCBNYWRpc29uLCBXSSAoNDMuMTM2NzIxMTEwNDc3ODgsIC04OS4zODMxNTUzMDY4NTI2NCkiLCJNYWluIEVudHJhbmNlLCBHeW0gaW4gTm92ZW1iZXIiLFRSVUUsNDMuMTM2NzIxMTEsLTg5LjM4MzE1NTMxDQozNiwxOCxNZW5kb3RhIEVsZW1lbnRhcnksIjQwMDIgU2Nob29sIFJkIE1hZGlzb24sIFdJICg0My4xMzY3MjExMTA0Nzc4OCwgLTg5LjM4MzE1NTMwNjg1MjY0KSIsIk1haW4gRW50cmFuY2UsIEd5bSBpbiBOb3ZlbWJlciIsVFJVRSw0My4xMzY3MjExMSwtODkuMzgzMTU1MzENCjM3LDE4LExpbmRiZXJnaCBFbGVtZW50YXJ5LCI0NTAwIEtlbm5lZHkgUmQgTWFkaXNvbiwgV0kgKDQzLjE0MzY0NTg5MDEwMzUzLCAtODkuMzg3ODg1NzQwNzI2OCkiLEd5bSxUUlVFLDQzLjE0MzY0NTg5LC04OS4zODc4ODU3NA0KMzgsMTgsQmxhY2toYXdrIE1pZGRsZSBTY2hvb2wsIjE0MDIgV3lvbWluZyBXYXkgTWFkaXNvbiwgV0kgKDQzLjE0NjEzNzAxMjEyNDY1NSwgLTg5LjM3MDkzMzUyMDQ3NDQ3KSIsTE1DLFRSVUUsNDMuMTQ2MTM3MDEsLTg5LjM3MDkzMzUyDQozOSw2LEhhd3Rob3JuZSBCcmFuY2ggTGlicmFyeSwiMjcwNyBXYXNoaW5ndG9uIEF2ZSBNYWRpc29uLCBXSSAoNDMuMTAxOTIwODkxODYzNTMsIC04OS4zNDcwMzEwMjU3OTY1OCkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMTAxOTIwODksLTg5LjM0NzAzMTAzDQo0MCw2LE9sYnJpY2ggR2FyZGVucywiMzMzMCBBdHdvb2QgQXZlIE1hZGlzb24sIFdJICg0My4wOTE2MjAxMTA3MDg1MDQsIC04OS4zMzU2NDQxMjQwNDQ3MykiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDkxNjIwMTEsLTg5LjMzNTY0NDEyDQo0MSw2LE8nS2VlZmZlIE1pZGRsZSBTY2hvb2wsIjUxMCBUaG9ybnRvbiBBdmUgTWFkaXNvbiwgV0kgKDQzLjA4Njk3OTM2NDgyMzE1LCAtODkuMzU3MDIzNDQyMTE1OTYpIixDYWZldGVyaWEgLSBFbnRlciBvZmYgU3BhaWdodCBTdHJlZXQsVFJVRSw0My4wODY5NzkzNiwtODkuMzU3MDIzNDQNCjQyLDYsV2lsLU1hciBOZWlnaGJvcmhvb2QgQ2VudGVyLCI5NTMgSmVuaWZlciBTdCBNYWRpc29uLCBXSSAoNDMuMDc5OTU1OTAzOTg4NDksIC04OS4zNjcxODMzNDc2MTIzKSIsWWFoYXJhIFJvb20sVFJVRSw0My4wNzk5NTU5LC04OS4zNjcxODMzNQ0KNDMsNixNYWRpc29uIE11bmljaXBhbCBCdWlsZGluZywiMjE1IE1hcnRpbiBMdXRoZXIgS2luZyBKciBCbHZkIE1hZGlzb24sIFdJICg0My4wNzI4NjAyNTMxNzcyOCwgLTg5LjM4MTY3MTQyNTUwNDMpIixGaXJzdCBGbG9vciBMb2JieSxUUlVFLDQzLjA3Mjg2MDI1LC04OS4zODE2NzE0Mw0KNDQsMixUZW5uZXkgUGFyayBQYXZpbGlvbiwiNDAyIFRob3JudG9uIEF2ZW51ZSBNYWRpc29uLCBXSSAoNDMuMDkzNTA3MDAzODQ1NjEsIC04OS4zNjg2MjMwMjU2Nzc0NikiLCAsVFJVRSw0My4wOTM1MDcsLTg5LjM2ODYyMzAzDQo0NSwyLExhcGhhbSBFbGVtZW50YXJ5LCIxMDQ1IERheXRvbiBTdCBNYWRpc29uLCBXSSAoNDMuMDg1NTQ4ODQxNjIzOTA0LCAtODkuMzcyNzAyMzk3NDQ5OTQpIiwiSW5nZXJzb2xsIEVudHJhbmNlLCBHeW0gaW4gTm92ZW1iZXIiLFRSVUUsNDMuMDg1NTQ4ODQsLTg5LjM3MjcwMjQNCjQ2LDIsR2F0ZXMgb2YgSGVhdmVuLCIzMDIgR29yaGFtIFN0cmVldCBNYWRpc29uLCBXSSAoNDMuMDc5NjU3MTk3MTQ4ODksIC04OS4zODQ5MjU2MDgxNjk3NSkiLCAsVFJVRSw0My4wNzk2NTcyLC04OS4zODQ5MjU2MQ0KNDcsMixMb3dlbGwgQ2VudGVyLCI2MTAgTGFuZ2RvbiBTdCBNYWRpc29uLCBXSSAoNDMuMDc1ODAxMDM2MjEzMTM2LCAtODkuMzk1ODM1NzQ3NDY1ODEpIixGcm9udCBMb2JieSxUUlVFLDQzLjA3NTgwMTA0LC04OS4zOTU4MzU3NQ0KNDgsMixMb3dlbGwgQ2VudGVyLCI2MTAgTGFuZ2RvbiBTdHJlZXQgTWFkaXNvbiwgV0kgKDQzLjA3NTgwMTAzNjIxMzEzNiwgLTg5LjM5NTgzNTc0NzQ2NTgxKSIsRnJvbnQgTG9iYnksVFJVRSw0My4wNzU4MDEwNCwtODkuMzk1ODM1NzUNCjQ5LDQsTWFkaXNvbiBGcmVzaCBNYXJrZXQsIjcwMyBVbml2ZXJzaXR5IEF2ZW51ZSBNYWRpc29uLCBXSSAoNDMuMDczMDg0OTQyMDU4ODcsIC04OS4zOTc0ODA1MTQzNjExMykiLEV2ZW50IENlbnRlciBvbiBzZWNvbmQgZmxvb3IsVFJVRSw0My4wNzMwODQ5NCwtODkuMzk3NDgwNTENCjUwLDQsRG95bGUgQWRtaW5pc3RyYXRpb24sIjU0NSBEYXl0b24gU3QgTWFkaXNvbiwgV0kgKDQzLjA3MDc4MjgxMTUzOTc5LCAtODkuMzk0ODE2Nzk3MjA3ODEpIixBdWRpdG9yaXVtLFRSVUUsNDMuMDcwNzgyODEsLTg5LjM5NDgxNjgNCjUxLDQsTWFkaXNvbiBTZW5pb3IgQ2VudGVyLCIzMzAgTWlmZmxpbiBTdCBNYWRpc29uLCBXSSAoNDMuMDc3ODQxNjkxNjc5Mzk2LCAtODkuMzgyMDk3NjA4NTA0MjgpIiwgLFRSVUUsNDMuMDc3ODQxNjksLTg5LjM4MjA5NzYxDQo1Miw0LE1hZGlzb24gTXVuaWNpcGFsIEJ1aWxkaW5nLCIyMTUgTWFydGluIEx1dGhlciBLaW5nIEpyIEJsdmQgTWFkaXNvbiwgV0kgKDQzLjA3Mjg2MDI1MzE3NzI4LCAtODkuMzgxNjcxNDI1NTA0MykiLEZyb250IExvYmJ5LFRSVUUsNDMuMDcyODYwMjUsLTg5LjM4MTY3MTQzDQo1Myw0LENhcGl0b2wgTGFrZXMgUmV0aXJlbWVudCwiMzMzIE1haW4gU3QgTWFkaXNvbiwgV0kgKDQzLjA3NjExNDE4NzQ0MDU3LCAtODkuMzc5NzU4MDIxMTQ2MjkpIiwgLFRSVUUsNDMuMDc2MTE0MTksLTg5LjM3OTc1ODAyDQo1NCw4LCJTZXB0IC0gTWF5OiBVVyBXZWxjb21lIENlbnRlciwgMjEgTiBQYXJrIFN0IiwiNTQ1IERheXRvbiBTdCBNYWRpc29uLCBXSSAoNDMuMDcwNzgyODExNTM5NzksIC04OS4zOTQ4MTY3OTcyMDc4MSkiLCAsVFJVRSw0My4wNzA3ODI4MSwtODkuMzk0ODE2OA0KNTUsOCxQb3JjaGxpZ2h0LCIzMDYgQnJvb2tzIFN0IE1hZGlzb24sIFdJICg0My4wNzIyNTg3MDg5MjAyNSwgLTg5LjQwMjUwMDc0MTExODkpIixEaW5pbmcgUm9vbSxUUlVFLDQzLjA3MjI1ODcxLC04OS40MDI1MDA3NA0KNTYsOCwiU2VwdCAtIE1heTogR29yZG9uIERpbmluZywgNzcwIFcgRGF5dG9uIFN0IiwiNzI4IFN0YXRlIFN0IE1hZGlzb24sIFdJICg0My4wNzQ5MTA3MDc0MDQxMTUsIC04OS4zOTg0OTc3NDA2NzI1KSIsICxUUlVFLDQzLjA3NDkxMDcxLC04OS4zOTg0OTc3NA0KNTcsOCxVVyBNZW1vcmlhbCBMaWJyYXJ5LCI3MjggU3RhdGUgU3QgTWFkaXNvbiwgV0kgKDQzLjA3NDkxMDcwNzQwNDExNSwgLTg5LjM5ODQ5Nzc0MDY3MjUpIixSb29tIDExNixUUlVFLDQzLjA3NDkxMDcxLC04OS4zOTg0OTc3NA0KNTgsOCxVVyBNZW1vcmlhbCBVbmlvbiwiODAwIExhbmdkb24gU3QgTWFkaXNvbiwgV0kgKDQzLjA3NTg4MzAwMzI2NTk5LCAtODkuMzk4OTM5Mzc2NDUyOTcpIixQYXVsIEJ1bnlhbiBSb29tIG9yIFRyaXBwIENvbW1vbnMsVFJVRSw0My4wNzU4ODMsLTg5LjM5ODkzOTM4DQo1OSw4LCJTZXB0IC0gTWF5OiBIb2x0IENvbW1vbnMsIDE2NDAgS3JvbnNoYWdlIERyIiwiODAwIExhbmdkb24gU3QgTWFkaXNvbiwgV0kgKDQzLjA3NTg4MzAwMzI2NTk5LCAtODkuMzk4OTM5Mzc2NDUyOTcpIiwgLFRSVUUsNDMuMDc1ODgzLC04OS4zOTg5MzkzOA0KNjAsNSxFYWdsZSBIZWlnaHRzIENvbW11bml0eSBDZW50ZXIsIjYxMSBFYWdsZSBIdHMgTWFkaXNvbiwgV0kgKDQzLjA4NzY3MzcyMzAwODQ2NCwgLTg5LjQzNjI0NDYyMzczOTIxKSIsUm9vbSAxMDEsVFJVRSw0My4wODc2NzM3MiwtODkuNDM2MjQ0NjINCjYxLDUsRmlyc3QgQ29uZ3JlZ2F0aW9uYWwgQ2h1cmNoLCIxNjA5IFVuaXZlcnNpdHkgQXZlIE1hZGlzb24sIFdJICg0My4wNzMzOTA4NTMxNzQ2LCAtODkuNDE0MTI3OTU3NTQxNzgpIixGaXJzdCBGbG9vciBTdHVkZW50IExvdW5nZSBvciBMb3dlciBMZXZlbCBHeW0sVFJVRSw0My4wNzMzOTA4NSwtODkuNDE0MTI3OTYNCjYyLDUsQmxlc3NlZCBTYWNyYW1lbnQgQ2F0aG9saWMgQ2h1cmNoLCIyMTMxIFJvd2xleSBBdmUgTWFkaXNvbiwgV0kgKDQzLjA2NzA0MTYzOTY0NzcsIC04OS40MjM1NjE4NDU2MjkxNCkiLExvd2VyIExldmVsIEZyaWFyeSxUUlVFLDQzLjA2NzA0MTY0LC04OS40MjM1NjE4NQ0KNjMsNSxXZXN0IEhpZ2ggU2Nob29sLCIzMCBBc2ggU3QgTWFkaXNvbiwgV0kgKDQzLjA2OTAzODEzNDU1Mjc4NCwgLTg5LjQyNTU2NzMzMzc1NDQzKSIsVmFuIEhpc2UgR3ltIEVudHJhbmNlLFRSVUUsNDMuMDY5MDM4MTMsLTg5LjQyNTU2NzMzDQo2NCw1LEhveXQgU2Nob29sLCIzODAyIFJlZ2VudCBTdCBNYWRpc29uLCBXSSAoNDMuMDY4MDEyOTA3NDM5MzcsIC04OS40MzkyNzMwMDE0ODA5NikiLCJSb29tIDEzLCBHeW0gaW4gTm92ZW1iZXIiLFRSVUUsNDMuMDY4MDEyOTEsLTg5LjQzOTI3Mw0KNjUsMTMsV2luZ3JhIFNjaG9vbCwiNzE4IEdpbG1vcmUgU3QgTWFkaXNvbiwgV0kgKDQzLjA1NTExNjYyOTUzOTIxNCwgLTg5LjQzMzgyMzEwOTk4MzQ1KSIsRmlyc3QgRmxvb3IgQ29tbW9ucyAtIEVudGVyIG9mZiByZWFyIHBhcmtpbmcgbG90LFRSVUUsNDMuMDU1MTE2NjMsLTg5LjQzMzgyMzExDQo2NiwxMyxTdCBKYW1lcyBDYXRob2xpYyBTY2hvb2wsIjEyMDQgU3QgSmFtZXMgQ3QgTWFkaXNvbiwgV0kgKDQzLjA2NTg4MTUxNDIyOTk0LCAtODkuNDA1ODAwMjM1NTUwNDMpIixDaHVyY2ggYmFzZW1lbnQsVFJVRSw0My4wNjU4ODE1MSwtODkuNDA1ODAwMjQNCjY3LDEzLEJyaXR0aW5naGFtIEFwYXJ0bWVudHMsIjc1NSBCcmF4dG9uIFBsIE1hZGlzb24sIFdJICg0My4wNjYyMjMyNTY2MDEwNiwgLTg5LjM5ODQ5Nzc0MDY3MjUpIixMaWJyYXJ5LFRSVUUsNDMuMDY2MjIzMjYsLTg5LjM5ODQ5Nzc0DQo2OCwxMyxUcmluaXR5IFVuaXRlZCBNZXRob2Rpc3QgQ2h1cmNoLCIxMTIzIFZpbGFzIEF2ZSBNYWRpc29uLCBXSSAoNDMuMDYzMTExNjAyMzc5NzA2LCAtODkuNDA1MDcxNzg0NzA2NykiLExvd2VyIExldmVsIEZlbGxvd3NoaXAgSGFsbCAtIGVudGVyIGZyb20gd2VzdCBsb3QsVFJVRSw0My4wNjMxMTE2LC04OS40MDUwNzE3OA0KNjksMTMsQmphcm5lIFJvbW5lcyBBcGFydG1lbnRzLCI1NDAgT2xpbiBBdmUgTWFkaXNvbiwgV0kgKDQzLjA1NDAzOTk4MTIxMzIsIC04OS4zOTA4NDM3OTA2MzAxMykiLENvbW11bml0eSBSb29tIC0gTG93ZXIgTGV2ZWwsVFJVRSw0My4wNTQwMzk5OCwtODkuMzkwODQzNzkNCjcwLDE0LEJyaWRnZSAtIExha2UgUG9pbnQgLSBXYXVub25hIENvbW11bml0eSBDZW50ZXIsIjE5MTcgTGFrZSBQb2ludCBEciBNYWRpc29uLCBXSSAoNDMuMDQ4MTkxODQ5OTEzNjIsIC04OS4zNDcxMzc0NTk1NzkxOSkiLEZyb250IFJvb20sVFJVRSw0My4wNDgxOTE4NSwtODkuMzQ3MTM3NDYNCjcxLDE0LEJhZGdlciBSb2NrIE1pZGRsZSBTY2hvb2wsIjUwMSBCYWRnZXIgUm9hZCBNYWRpc29uLCBXSSAoNDMuMDM4MjUyNjg2MjI5NDEsIC04OS4zNzg5OTQ2MDQ3Mzc4NCkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDM4MjUyNjksLTg5LjM3ODk5NDYNCjcyLDE0LEJveXMgJiBHaXJscyBDbHViLCIyMDAxIFRhZnQgU3QgTWFkaXNvbiwgV0kgKDQzLjA0MzkyMDA3MDI3NDM5NiwgLTg5LjM5MzAwMjg4MzA3MzUpIiwiU2Vjb25kIEZsb29yIGNvbmZlcmVuY2Ugcm9vbSwgb3IgZ3ltIixUUlVFLDQzLjA0MzkyMDA3LC04OS4zOTMwMDI4OA0KNzMsMTQsVmlsbGFnZSBvbiBQYXJrLCIyMzAwIFBhcmsgU3QgTWFkaXNvbiwgV0kgKDQzLjA0MDYwNDgwOTU0NTU4NCwgLTg5LjM5NDE4NDA1ODk5ODM5KSIsQ29tbXVuaXR5IFJvb20sVFJVRSw0My4wNDA2MDQ4MSwtODkuMzk0MTg0MDYNCjc0LDE0LExlb3BvbGQgRWxlbWVudGFyeSwiMjYwMiBQb3N0IFJkIE1hZGlzb24sIFdJICg0My4wMjY1NjMxNTUxMDg5MywgLTg5LjQyMTc0NTIxNTEyOTkzKSIsIkxvYmJ5LCBHeW0gaW4gTm92ZW1iZXIiLFRSVUUsNDMuMDI2NTYzMTYsLTg5LjQyMTc0NTIyDQo3NSwxNCxBcmJvciBHYXRlIENlbnRlciwiMjUwMSBCZWx0bGluZSBId3kgTWFkaXNvbiwgV0kgKDQzLjAzNDc1MzA3NDU5Njc4NiwgLTg5LjQxOTY0NDMzNzcxMzA1KSIsTG9iYnksVFJVRSw0My4wMzQ3NTMwNywtODkuNDE5NjQ0MzQNCjc2LDEwLEhlYWQgU3RhcnQsIjIwOTYgUmVkIEFycm93IFRybCBNYWRpc29uLCBXSSAoNDMuMDMxNzQzMjQ5OTk3NywgLTg5LjQ1Njk2MjY2NTc5NzMzKSIsTXVsdGlwdXJwb3NlIFJvb20sVFJVRSw0My4wMzE3NDMyNSwtODkuNDU2OTYyNjcNCjc3LDEwLFRva2kgTWlkZGxlIFNjaG9vbCwiNTYwNiBSdXNzZXR0IFJkIE1hZGlzb24sIFdJICg0My4wMzMwNDAzMjY2MTM5NSwgLTg5LjQ3NTE3OTY4NDE2MDk4KSIsTE1DLFRSVUUsNDMuMDMzMDQwMzMsLTg5LjQ3NTE3OTY4DQo3OCwxMCxUaG9yZWF1IEVsZW1lbnRhcnksIjM4NzAgTmFrb21hIFJkIE1hZGlzb24sIFdJICg0My4wNDU3MDA3Mjc4OTI0NCwgLTg5LjQ0MTgzNjA2OTI2NDQ3KSIsTE1DLFRSVUUsNDMuMDQ1NzAwNzMsLTg5LjQ0MTgzNjA3DQo3OSwxMCxTZXF1b3lhIEJyYW5jaCBMaWJyYXJ5LCI0MzQwIFRva2F5IEJvdWxldmFyZCBNYWRpc29uLCBXSSAoNDMuMDUzNjQ4MTU5NDA0NjQsIC04OS40NTAwOTMwNTg2NTg5NCkiLExhcmdlIG1lZXRpbmcgcm9vbSxUUlVFLDQzLjA1MzY0ODE2LC04OS40NTAwOTMwNg0KODAsMTEsTWlkdmFsZSBFbGVtZW50YXJ5LCI1MDIgQ2Fyb21hciBEciBNYWRpc29uLCBXSSAoNDMuMDU3MzM3OTU0OTUxNjA2LCAtODkuNDQ5MjM3NDg5NTY1NjMpIixMb2JieS9mcm9udCBmb3llciBvciBsb3dlciBsZXZlbCBneW0sVFJVRSw0My4wNTczMzc5NSwtODkuNDQ5MjM3NDkNCjgxLDExLE1pZHZhbGUgRWxlbWVudGFyeSwiNTAyIENhcm9tYXIgRHIgTWFkaXNvbiwgV0kgKDQzLjA1NzMzNzk1NDk1MTYwNiwgLTg5LjQ0OTIzNzQ4OTU2NTYzKSIsTG9iYnkvZnJvbnQgZm95ZXIgb3IgbG93ZXIgbGV2ZWwgZ3ltLFRSVUUsNDMuMDU3MzM3OTUsLTg5LjQ0OTIzNzQ5DQo4MiwxMSxIb3l0IFNjaG9vbCwiMzgwMiBSZWdlbnQgU3QgTWFkaXNvbiwgV0kgKDQzLjA2ODAxMjkwNzQzOTM3LCAtODkuNDM5MjczMDAxNDgwOTYpIiwiUm9vbSAxMywgR3ltIGluIE5vdmVtYmVyIixUUlVFLDQzLjA2ODAxMjkxLC04OS40MzkyNzMNCjgzLDExLENvdmVuYW50IFByZXNieXRlcmlhbiBDaHVyY2gsIjMyNiBTZWdvZSBSZCBNYWRpc29uLCBXSSAoNDMuMDcwNTA1Mzk1MTY0Mjc1LCAtODkuNDU0NjA1MTU0Njk2MikiLE5hcnRoZXgsVFJVRSw0My4wNzA1MDU0LC04OS40NTQ2MDUxNQ0KODQsMTEsSGlsbCBGYXJtIFN0YXRlIE9mZmljZSBCdWlsZGluZywiNDgwMiBTaGVib3lnYW4gQXZlIE1hZGlzb24sIFdJICg0My4wNzI4OTYyMjYwNTg0NSwgLTg5LjQ2MDY1ODg3OTMzNzc2KSIsTWFpbiBFbnRyYW5jZSxUUlVFLDQzLjA3Mjg5NjIzLC04OS40NjA2NTg4OA0KODUsMTEsU3RlcGhlbnMgRWxlbWVudGFyeSwiMTIwIFJvc2EgUmQgTWFkaXNvbiwgV0kgKDQzLjA2OTM2NzAzNjczMDQzLCAtODkuNDc4MjE0NzQ3NzE3MikiLCJVc2UgbWFpbiBlbnRyYW5jZSwgR3ltIGluIE5vdmVtYmVyIixUUlVFLDQzLjA2OTM2NzA0LC04OS40NzgyMTQ3NQ0KODYsMTksU3ByaW5nIEhhcmJvciBNaWRkbGUgU2Nob29sLCIxMTEwIFNwcmluZyBIYXJib3IgRHIgTWFkaXNvbiwgV0kgKDQzLjA4MDc4MzI4MDI1NTQ0NiwgLTg5LjQ3MTQ0MTc1MDQ2OTA3KSIsUm9vbSA3LFRSVUUsNDMuMDgwNzgzMjgsLTg5LjQ3MTQ0MTc1DQo4NywxOSxDcmVzdHdvb2QgRWxlbWVudGFyeSwiNTkzMCBPbGQgU2F1ayBSZCBNYWRpc29uLCBXSSAoNDMuMDc1MjkxNjM4MDgyNzMsIC04OS40ODE5MDAxMDQzOTk5MSkiLExvdW5nZSAtIFJvb20gMTAyLFRSVUUsNDMuMDc1MjkxNjQsLTg5LjQ4MTkwMDENCjg4LDE5LEFsaWNpYSBBc2htYW4gQnJhbmNoIExpYnJhcnksIjczMyBIaWdoIFBvaW50IFJkIE1hZGlzb24sIFdJICg0My4wNzU3NDUyOTcyODMyOCwgLTg5LjUxODUyODk2NDA3MjY3KSIsTWVldGluZyBSb29tLFRSVUUsNDMuMDc1NzQ1MywtODkuNTE4NTI4OTYNCjg5LDE5LE9ha3dvb2QgVmlsbGFnZSBVbml2ZXJzaXR5IFdvb2RzLCI2MjA1IE1pbmVyYWwgUG9pbnQgUmQgTWFkaXNvbiwgV0kgKDQzLjA2MDcxNDg0ODg0MjkzNSwgLTg5LjQ4NzYwNDI3NDA4MzkpIixOYWtvbWEvV2VzdG1vcmxhbmQgUm9vbXMsVFJVRSw0My4wNjA3MTQ4NSwtODkuNDg3NjA0MjcNCjkwLDE5LE1hZGlzb24gSWNlIEFyZW5hLCI3MjUgRm9yd2FyZCBEciBNYWRpc29uLCBXSSAoNDMuMDQ4NDI1NjczNjQxMTksIC04OS40ODgxNzgxMzM0MzQzKSIsQ29uZmVyZW5jZSBSb29tLFRSVUUsNDMuMDQ4NDI1NjcsLTg5LjQ4ODE3ODEzDQo5MSwyMCxGYWxrIEVsZW1lbnRhcnksIjYzMjMgV29vZGluZ3RvbiBXYXkgTWFkaXNvbiwgV0kgKDQzLjA0MDg2MjM3NTM3NDc4NiwgLTg5LjQ5MDc1OTE4NzY1ODQpIixGcm9udCBFbnRyYW5jZSxUUlVFLDQzLjA0MDg2MjM4LC04OS40OTA3NTkxOQ0KOTIsMjAsT3VyIFJlZGVlbWVyIEx1dGhlcmFuIENodXJjaCwiMTcwMSBNY0tlbm5hIEJsdmQgTWFkaXNvbiwgV0kgKDQzLjAzMzczMDc1MTY4MjQ3LCAtODkuNDk2ODI5NjExMzU2MTkpIixDb21tdW5pdHkgUm9vbSxUUlVFLDQzLjAzMzczMDc1LC04OS40OTY4Mjk2MQ0KOTMsMjAsR29vZCBTaGVwaGVyZCBMdXRoZXJhbiBDaHVyY2gsIjU3MDEgUmF5bW9uZCBSZCBNYWRpc29uLCBXSSAoNDMuMDMxODE1MTk1NzYwMDMsIC04OS40Nzc3OTk5NTcyMTYxOCkiLEZlbGxvd3NoaXAgSGFsbCAtIEZyb250IEVudHJhbmNlLFRSVUUsNDMuMDMxODE1MiwtODkuNDc3Nzk5OTYNCjk0LDIwLEdvb2QgU2hlcGhlcmQgTHV0aGVyYW4gQ2h1cmNoLCI1NzAxIFJheW1vbmQgUmQgTWFkaXNvbiwgV0kgKDQzLjAzMTgxNTE5NTc2MDAzLCAtODkuNDc3Nzk5OTU3MjE2MTgpIixGZWxsb3dzaGlwIEhhbGwgLSBGcm9udCBFbnRyYW5jZSxUUlVFLDQzLjAzMTgxNTIsLTg5LjQ3Nzc5OTk2DQo5NSwyMCxIdWVnZWwgRWxlbWVudGFyeSwiMjYwMSBQcmFpcmllIFJkIE1hZGlzb24sIFdJICg0My4wMjQ0NTg3NDE1NjAzNSwgLTg5LjQ4ODg0MzYzMTczNjAyKSIsR3ltLFRSVUUsNDMuMDI0NDU4NzQsLTg5LjQ4ODg0MzYzDQo5NiwyMCxIZXJpdGFnZSBDb25ncmVnYXRpb25hbCBDaHVyY2gsIjMxMDIgUHJhaXJpZSBSZCBNYWRpc29uLCBXSSAoNDMuMDE5MzUwNTkyNDMzOTY2LCAtODkuNDkzMDg4NDMxNzE0MjgpIixDaHVyY2ggTmFydGhleCxUUlVFLDQzLjAxOTM1MDU5LC04OS40OTMwODg0Mw0KOTcsNyxTdCBNYXJ5J3MgQ2FyZSBDZW50ZXIsIjM0MDEgTWFwbGUgR3JvdmUgUmQgTWFkaXNvbiwgV0kgKDQzLjAxMDg0NzUzNjExMDMwNSwgLTg5LjQ5ODg5NDE0NzQ4NDIpIixBY3Rpdml0aWVzIFJvb20sVFJVRSw0My4wMTA4NDc1NCwtODkuNDk4ODk0MTUNCjk4LDcsQ2hhdmV6IEVsZW1lbnRhcnksIjM1MDIgTWFwbGUgR3JvdmUgRHIgTWFkaXNvbiwgV0kgKDQzLjAxMDI0OTQ1MzQ5NzQxLCAtODkuNDk5MDE0OTYzODg3MzYpIiwiUm9vbSAxMDItQywgR3ltIGluIE5vdmVtYmVyIixUUlVFLDQzLjAxMDI0OTQ1LC04OS40OTkwMTQ5Ng0KOTksNyxNZXJpdGVyIE1jS2VlIENsaW5pYywiMzEwMiBNZXJpdGVyIFdheSBNYWRpc29uLCBXSSAoNDMuMDcyOTUwMDAwMjQyNDg2LCAtODkuMzg2Njg5OTk5NzQzNTcpIixDb21tdW5pdHkgUm9vbSxUUlVFLDQzLjA3Mjk1LC04OS4zODY2OQ0KMTAwLDcsTWVyaXRlciBNY0tlZSBDbGluaWMsIjMxMDIgTWVyaXRlciBXYXkgTWFkaXNvbiwgV0kgKDQzLjA3Mjk1MDAwMDI0MjQ4NiwgLTg5LjM4NjY4OTk5OTc0MzU3KSIsQ29tbXVuaXR5IFJvb20sVFJVRSw0My4wNzI5NSwtODkuMzg2NjkNCjEwMSwxLFdlc3QgUG9saWNlIERpc3RyaWN0LCIxNzEwIE1jS2VubmEgQmx2ZCBNYWRpc29uLCBXSSAoNDMuMDMzOTM3NTk1NzQ5MTk0LCAtODkuNDk2OTE5NTQzNTU5MSkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDMzOTM3NiwtODkuNDk2OTE5NTQNCjEwMiwxLFdlc3QgUG9saWNlIERpc3RyaWN0LCIxNzEwIE1jS2VubmEgQmx2ZCBNYWRpc29uLCBXSSAoNDMuMDMzOTM3NTk1NzQ5MTk0LCAtODkuNDk2OTE5NTQzNTU5MSkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDMzOTM3NiwtODkuNDk2OTE5NTQNCjEwMywxLE91ciBSZWRlZW1lciBMdXRoZXJhbiBDaHVyY2gsIjE3MDEgTWNLZW5uYSBCbHZkIE1hZGlzb24sIFdJICg0My4wMzM3MzA3NTE2ODI0NywgLTg5LjQ5NjgyOTYxMTM1NjE5KSIsQ29tbXVuaXR5IFJvb20sVFJVRSw0My4wMzM3MzA3NSwtODkuNDk2ODI5NjENCjEwNCwxLE1hZGlzb24gSWNlIEFyZW5hLCI3MjUgRm9yd2FyZCBEciBNYWRpc29uLCBXSSAoNDMuMDQ4NDI1NjczNjQxMTksIC04OS40ODgxNzgxMzM0MzQzKSIsQ29uZmVyZW5jZSBSb29tLFRSVUUsNDMuMDQ4NDI1NjcsLTg5LjQ4ODE3ODEzDQoxMDUsMSxDb3ZlbnRyeSBWaWxsYWdlLCI3NzA3IEJyb29rbGluZSBEciBNYWRpc29uLCBXSSAoNDMuMDQ5NTA4ODMwMzYxOTIsIC04OS41MTc5MzMzMDA5NjcxMykiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDQ5NTA4ODMsLTg5LjUxNzkzMzMNCjEwNiwxLEJsYWNraGF3ayBDaHVyY2gsIjk2MjAgQnJhZGVyIFdheSBNYWRpc29uLCBXSSAoNDMuMDYxMzkzODk3MzAzNzMsIC04OS41NTQwNDQ0Nzg4NjAyNSkiLE1lZXRpbmcgUm9vbSBvbiBzZWNvbmQgZmxvb3IsVFJVRSw0My4wNjEzOTM5LC04OS41NTQwNDQ0OA0KMTA3LDksQ292ZW50cnkgVmlsbGFnZSwiNzcwNyBCcm9va2xpbmUgRHIgTWFkaXNvbiwgV0kgKDQzLjA0OTUwODgzMDM2MTkyLCAtODkuNTE3OTMzMzAwOTY3MTMpIixDb21tdW5pdHkgUm9vbSxUUlVFLDQzLjA0OTUwODgzLC04OS41MTc5MzMzDQoxMDgsOSxMdXNzaWVyIENvbW11bml0eSBFZHVjYXRpb24gQ2VudGVyLCI1NSBHYW1tb24gUmQgTWFkaXNvbiwgV0kgKDQzLjA2OTA0MjY0NzA0ODUsIC04OS41MDI0OTIyMzk1ODgzOCkiLENsYXNzcm9vbSxUUlVFLDQzLjA2OTA0MjY1LC04OS41MDI0OTIyNA0KMTA5LDksSGlnaCBQb2ludCBDaHVyY2gsIjc3MDIgT2xkIFNhdWsgUmQgTWFkaXNvbiwgV0kgKDQzLjA3NTA1NDU5ODkyODc3LCAtODkuNTE2OTIwNDY1NDkwNzMpIiwiTWljYWggQ2VudGVyIC0gUGFyayBiZWhpbmQgYnVpbGRpbmcsIHNpZGUgZW50cmFuY2UiLFRSVUUsNDMuMDc1MDU0NiwtODkuNTE2OTIwNDcNCjExMCw5LEF0dGljIEFuZ2VsIEFzc29jaWF0aW9uLCI2NDAgSnVuY3Rpb24gUm9hZCBNYWRpc29uLCBXSSAoNDMuMDczODEzNTM0NTI4MzcsIC04OS41MjY4NDg5ODA2OTQyNCkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDczODEzNTMsLTg5LjUyNjg0ODk4DQoxMTEsOSxUaGUgSmVmZmVyc29uLCI5NDAxIE9sZCBTYXVrIFJkIE1hZGlzb24sIFdJICg0My4wNzQ3Mzk4MzYyMTg1NTUsIC04OS41NTA0OTIxNTY4NDQ1NCkiLEFjdGl2aXR5IFJvb20sVFJVRSw0My4wNzQ3Mzk4NCwtODkuNTUwNDkyMTYNCjExMiwxOCxCbGFja2hhd2sgTWlkZGxlIFNjaG9vbCwiMTQwMiBXeW9taW5nIFdheSBNYWRpc29uLCBXSSAoNDMuMTQ2MTM3MDEyMTI0NjU1LCAtODkuMzcwOTMzNTIwNDc0NDcpIixMTUMsVFJVRSw0My4xNDYxMzcwMSwtODkuMzcwOTMzNTINCjExMywxNCxCcmlkZ2UgLSBMYWtlIFBvaW50IC0gV2F1bm9uYSBDb21tdW5pdHkgQ2VudGVyLCIxOTE3IExha2UgUG9pbnQgRHIgTWFkaXNvbiwgV0kgKDQzLjA0ODE5MTg0OTkxMzYyLCAtODkuMzQ3MTM3NDU5NTc5MTkpIixGcm9udCBSb29tLFRSVUUsNDMuMDQ4MTkxODUsLTg5LjM0NzEzNzQ2DQoxMTQsMSxPdXIgUmVkZWVtZXIgTHV0aGVyYW4gQ2h1cmNoLCIxNzAxIE1jS2VubmEgQmx2ZCBNYWRpc29uLCBXSSAoNDMuMDMzNzMwNzUxNjgyNDcsIC04OS40OTY4Mjk2MTEzNTYxOSkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDMzNzMwNzUsLTg5LjQ5NjgyOTYxDQoxMTUsMSxPdXIgUmVkZWVtZXIgTHV0aGVyYW4gQ2h1cmNoLCIxNzAxIE1jS2VubmEgQmx2ZCBNYWRpc29uLCBXSSAoNDMuMDMzNzMwNzUxNjgyNDcsIC04OS40OTY4Mjk2MTEzNTYxOSkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDMzNzMwNzUsLTg5LjQ5NjgyOTYxDQoxMTYsMSxPdXIgUmVkZWVtZXIgTHV0aGVyYW4gQ2h1cmNoLCIxNzAxIE1jS2VubmEgQmx2ZCBNYWRpc29uLCBXSSAoNDMuMDMzNzMwNzUxNjgyNDcsIC04OS40OTY4Mjk2MTEzNTYxOSkiLENvbW11bml0eSBSb29tLFRSVUUsNDMuMDMzNzMwNzUsLTg5LjQ5NjgyOTYxDQoxMTcsMTksTWFkaXNvbiBJY2UgQXJlbmEsIjcyNSBGb3J3YXJkIERyaXZlIE1hZGlzb24sIFdJICg0My4wNDg0MjU2NzM2NDExOSwgLTg5LjQ4ODE3ODEzMzQzNDMpIiwgLFRSVUUsNDMuMDQ4NDI1NjcsLTg5LjQ4ODE3ODEzDQo=',
          ),
        ),
      ),
    )
  );
}

/**
 * Builds an array of US National Foreclosure Statistics January 2012 Dataset
 */
function dkan_default_content_national_foreclosure_dataset(){
  return array(
    'exported_path' => 'us-national-foreclosure-statistics-january-2012',
    'sort_value' => '9',
    'title' => 'US National Foreclosure Statistics January 2012',
    'uid' => 1,
    'created' => '1360557746',
    'machine_name' => 'national-foreclose',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'County data: http://www.npr.org/templates/story/story.php?storyId=111494514

State data: http://statehealthfacts.org/comparetable.jsp?ind=649&cat=1',
          'summary' => '',
          'format' => 'html',
          'safe_value' => '<p>County data: <a href="http://www.npr.org/templates/story/story.php?storyId=111494514">http://www.npr.org/templates/story/story.php?storyId=111494514</a></p><p>State data: <a href="http://statehealthfacts.org/comparetable.jsp?ind=649&amp;cat=1">http://statehealthfacts.org/comparetable.jsp?ind=649&amp;cat=1</a></p>',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_license' => array(
      'und' => array(
        0 => array(
          'value' => 'notspecified',
          'format' => NULL,
          'safe_value' => 'notspecified',
        ),
      ),
    ),
    'og_group_ref' => array(
      'und' => array(
        0 => array(
          'title' => 'Data Explorer Examples',
        ),
      ),
    ),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => '4',
        ),
        1 => array(
          'tid' => '3',
        ),
      ),
    ),
    'field_contact_email' => array(
      'und' => array(
        0 => array(
          'value' => variable_get('site_mail', NULL),
        ),
      ),
    ),
  );
}

/**
 * Builds an array of US National Foreclosure Statistics January 2012 Dataset Resources
 */
function dkan_default_content_national_foreclosure_resources(){
  return array(
    array(
      'exported_path' => 'dataset/us-national-foreclosure-statistics-january-2012/resource/d9ed9373-4371-4f94-a17d',
      'title' => '1-2012-Foreclosures-by-State',
      'sort_value' => '8',
      'uid' => 1,
      'status' => '1',
      'promote' => '0',
      'sticky' => '0',
      'type' => 'resource',
      'language' => 'und',
      'created' => '1360558980',
      'comment' => '0',
      'translate' => '0',
      'machine_name' => 'foreclosure-jan-state',
      'body' => array(
        'und' => array(
          0 => array(
            'value' => 'US National Foreclosure Statistics - By State - January 2012',
            'summary' => '',
            'format' => 'html',
            'safe_value' => '<p>US National Foreclosure Statistics - By State - January 2012</p>',
            'safe_summary' => '',
          ),
        ),
      ),
      'field_format' => array(
        'und' => array(
          0 => array(
            'tid' => '8',
          ),
        ),
      ),
      'field_upload' => array(
        'und' => array(
          0 => array(
            'service_id' => NULL,
            'grid' => '1',
            'graph' => '1',
            'map' => '0',
            'timeline' => '0',
            'transform' => '0',
            'filename' => 'us_foreclosures_jan_2012_by_state.csv',
            'uri' => 'public://us_foreclosures_jan_2012_by_state.csv',
            'filemime' => 'text/csv',
            'filesize' => '1015',
            'status' => '1',
            'uuid' => '59f55f0d-eb69-4339-bd9f-e5e82a564178',
            'rdf_mapping' => array(),
            'file' => 'c3RhdGUsIHRvdGFsLmZvcmVjbG9zdXJlcywgZm9yZWNsb3N1cmUucmF0aW8gCk5ldmFkYSw1OTMxLDE5OApDYWxpZm9ybmlhLDUxNTg0LDI2NQpBcml6b25hLDg3NDksMzI1Ckdlb3JnaWEsMTI0NjcsMzI4Ck1pY2hpZ2FuLDEyODA4LDM1NApGbG9yaWRhLDI0NzgzLDM2MwpJbGxpbm9pcywxNDM0OSwzNjkKRGVsYXdhcmUsMTA4OCwzNzMKQ29sb3JhZG8sNDIyOCw1MjMKSW5kaWFuYSw1MDM5LDU1NQpOZXcgSGFtcHNoaXJlLDEwNTMsNTg0Ck9oaW8sODMyNSw2MTYKVXRhaCwxNTg0LDYxOQpXaXNjb25zaW4sNDIzMSw2MjAKU291dGggQ2Fyb2xpbmEsMzI4Niw2NTEKSWRhaG8sODYxLDc3NgpNaW5uZXNvdGEsMzAxNyw3NzgKSGF3YWlpLDY1Miw3OTcKSW93YSwxMzY1LDk3OQpPcmVnb24sMTYzMCwxMDI4Ck5ldyBNZXhpY28sODc2LDEwMjkKTG91aXNpYW5hLDE4NzAsMTA1MQpNaXNzb3VyaSwyNTI1LDEwNzQKVGV4YXMsOTA4NSwxMDk4ClZpcmdpbmlhLDMwMzMsMTEwOQpNYXNzYWNodXNldHRzLDIzNTcsMTE5MQpQZW5uc3lsdmFuaWEsNDU2NiwxMjE5Ck9rbGFob21hLDEzNTcsMTIyNwpSaG9kZSBJc2xhbmQsMzc0LDEyMzkKS2Fuc2FzLDk2NywxMjc1ClRlbm5lc3NlZSwyMTA3LDEzMzUKQ29ubmVjdGljdXQsMTA2NywxMzk0Cldhc2hpbmd0b24sMjAyMiwxNDI3CkFsYWJhbWEsMTQ4OSwxNDU5Ck5lYnJhc2thLDUyOSwxNTA2Ck5vcnRoIENhcm9saW5hLDI1NDEsMTcwMwpLZW50dWNreSwxMDY4LDE4MDQKTWFyeWxhbmQsMTI1MiwxOTAwCk5ldyBKZXJzZXksMTczNCwyMDQ5CkFsYXNrYSwxNDAsMjEzNApTb3V0aCBEYWtvdGEsMTM3LDI2NTMKV3lvbWluZyw4MiwzMTk0Ck1vbnRhbmEsMTI4LDM3NzIKTWFpbmUsMTkwLDM3OTkKTWlzc2lzc2lwcGksMjkzLDQzNTEKQXJrYW5zYXMsMjc0LDQ4MDQKTmV3IFlvcmssMTY3Miw0ODQ5Cldlc3QgVmlyZ2luaWEsMTQ4LDU5NTkKVmVybW9udCwxNSwyMTUwMwpEaXN0cmljdCBvZiBDb2x1bWJpYSw4LDM3MDkwCk5vcnRoIERha290YSw1LDYzNTAwCg==',
          ),
        ),
      ),
    )
  );
}

/**
 * Builds an array of Gold Prices in London 1950-2008 Dataset
 */
function dkan_default_content_gold_prices_dataset(){
  return array(
    'exported_path' => 'dataset/gold-prices-london-1950-2008-monthly',
    'title' => 'Gold Prices in London 1950-2008 (Monthly)',
    'uid' => 1,
    'sort_value' => '7',
    'created' => '1360555545',
    'machine_name' => 'gold-prices',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'Monthly gold prices (USD) in London from Bundesbank.

General: 1 ounce of fine gold = 31.1034768g. Method of calculation:

*   Since 1 April 1968, calculated from the daily morning fixing;
*   From January 1950 to 21 March 1954, calculated using the Bank of England\'s gold purchasing price (1 ounce of fine = pound 12.40) in connection with the average exchange rate for the pound in New York (up to the end of 1952; source: Federal Reserve Bulletin) and, from January 1953, midpoint exchange rates for the US dollar in London (source: Financial Times (FT)).
*   From 22 March 1954 to December 1959, calculated using the fixing price for gold bars of approx. 12 1/2 kg and 995/1000 fineness and over (so-called standard bars) according to data from Metallgesellschaft AG, Frankfurt am Main, in connection with the average midpoint exchange rates for the US dollar in London (source: FT).
*   From January 1960 to 14 March 1968, average fixing price for standard bars as specified in the Bank of England\'s Quarterly Bulletin.
*   On 15 March 1968, fixing price suspended. Gold market split into an official (reserved for central banks) and a free market as a result of the Washington Communique of 17 March 1968. Gold trading suspended from 18 to 29 March 1968.
*   Sources for daily prices: April 1968 - March 1974: FT; April 1974 - December 1980: Samuel Montagu & Co. Ltd.; January 1981 - December 2005: FT; January 2006 - present: Reuters.
*   Comment on 1968-03: Average from 1 to 14 March 1968.

License: PDDL (Source indicates no restrictions on data).',
          'summary' => '',
          'format' => 'html',
          'safe_value' => '<p>Monthly gold prices (USD) in London from Bundesbank.</p>

<p>General: 1 ounce of fine gold = 31.1034768g. Method of calculation:</p>

<ul><li>Since 1 April 1968, calculated from the daily morning fixing;</li>
<li>From January 1950 to 21 March 1954, calculated using the Bank of England\'s gold purchasing price (1 ounce of fine = pound 12.40) in connection with the average exchange rate for the pound in New York (up to the end of 1952; source: Federal Reserve Bulletin) and, from January 1953, midpoint exchange rates for the US dollar in London (source: Financial Times (FT)).</li>
<li>From 22 March 1954 to December 1959, calculated using the fixing price for gold bars of approx. 12 1/2 kg and 995/1000 fineness and over (so-called standard bars) according to data from Metallgesellschaft AG, Frankfurt am Main, in connection with the average midpoint exchange rates for the US dollar in London (source: FT).</li>
<li>From January 1960 to 14 March 1968, average fixing price for standard bars as specified in the Bank of England\'s Quarterly Bulletin.</li>
<li>On 15 March 1968, fixing price suspended. Gold market split into an official (reserved for central banks) and a free market as a result of the Washington Communique of 17 March 1968. Gold trading suspended from 18 to 29 March 1968.</li>
<li>Sources for daily prices: April 1968 - March 1974: FT; April 1974 - December 1980: Samuel Montagu &amp; Co. Ltd.; January 1981 - December 2005: FT; January 2006 - present: Reuters.</li>
<li>Comment on 1968-03: Average from 1 to 14 March 1968.</li>
</ul><p>License: PDDL (Source indicates no restrictions on data).</p>
',
          'safe_summary' => '',
        ),
      ),
    ),
    'field_license' => array(
      'und' => array(
        0 => array(
          'value' => 'odc-pddl',
          'format' => NULL,
          'safe_value' => 'odc-pddl',
        ),
      ),
    ),
    'og_group_ref' => array(
      'und' => array(
        0 => array(
          'title' => 'Data Explorer Examples',
        ),
      ),
    ),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => '6',
        ),
        1 => array(
          'tid' => '7',
        ),
      ),
    ),
    'field_contact_email' => array(
      'und' => array(
        0 => array(
          'value' => variable_get('site_mail', NULL),
        ),
      ),
    ),
  );
}

/**
 * Builds an array of Gold Prices in London 1950-2008 Dataset Resoures
 */
function dkan_default_content_gold_prices_resources(){
  return array(
    array(
      'exported_path' => 'dataset/gold-prices-london-1950-2008-monthly/resource/f913d1f0-4e4e-459b-ad8d-57cf18d6fcdf',
      'title' => 'Table of Gold Prices',
      'uid' => 1,
      'sort_value' => '6',
      'status' => '1',
      'promote' => '0',
      'sticky' => '0',
      'type' => 'resource',
      'language' => 'und',
      'created' => '1360555641',
      'comment' => '0',
      'translate' => '0',
      'machine_name' => 'table-of-gold',
      'body' => array(
        'und' => array(
          0 => array(
            'value' => 'CSV file extracted and cleaned from source excel.

  Click on Graph button under Preview and plot date (x-axis) against price (y-axis) to see a line graph. Hovering over a point gives you the value of that data entry.',
            'summary' => '',
            'format' => 'html',
            'safe_value' => '<p>CSV file extracted and cleaned from source excel.</p>

  <p>Click on Graph button under Preview and plot date (x-axis) against price (y-axis) to see a line graph. Hovering over a point gives you the value of that data entry.</p>
  ',
            'safe_summary' => '',
          ),
        ),
      ),
      'field_format' => array(
        'und' => array(
          0 => array(
            'tid' => '8',
          ),
        ),
      ),
      'field_upload' => array(
        'und' => array(
          0 => array(
            'service_id' => NULL,
            'grid' => '1',
            'graph' => '1',
            'map' => '0',
            'timeline' => '0',
            'transform' => '0',
            'filename' => 'data.csv',
            'uri' => 'public://data.csv',
            'filemime' => 'text/csv',
            'filesize' => '14502',
            'status' => '1',
            'uuid' => '97c6ae34-58c0-4e02-85a3-6a042a516227',
            'rdf_mapping' => array(),
            'file' => 'ZGF0ZSxwcmljZQ0KMTk1MC0wMS0wMSwzNC43Mw0KMTk1MC0wMi0wMSwzNC43Mw0KMTk1MC0wMy0wMSwzNC43Mw0KMTk1MC0wNC0wMSwzNC43Mw0KMTk1MC0wNS0wMSwzNC43Mw0KMTk1MC0wNi0wMSwzNC43Mw0KMTk1MC0wNy0wMSwzNC43Mw0KMTk1MC0wOC0wMSwzNC43Mw0KMTk1MC0wOS0wMSwzNC43Mw0KMTk1MC0xMC0wMSwzNC43Mw0KMTk1MC0xMS0wMSwzNC43Mw0KMTk1MC0xMi0wMSwzNC43Mg0KMTk1MS0wMS0wMSwzNC43Mg0KMTk1MS0wMi0wMSwzNC43Mw0KMTk1MS0wMy0wMSwzNC43Mw0KMTk1MS0wNC0wMSwzNC43Mw0KMTk1MS0wNS0wMSwzNC43Mw0KMTk1MS0wNi0wMSwzNC43Mw0KMTk1MS0wNy0wMSwzNC43Mg0KMTk1MS0wOC0wMSwzNC43MQ0KMTk1MS0wOS0wMSwzNC43MQ0KMTk1MS0xMC0wMSwzNC43MQ0KMTk1MS0xMS0wMSwzNC43Mw0KMTk1MS0xMi0wMSwzNC42Ng0KMTk1Mi0wMS0wMSwzNC40OQ0KMTk1Mi0wMi0wMSwzNC40OQ0KMTk1Mi0wMy0wMSwzNC42OA0KMTk1Mi0wNC0wMSwzNC44Mg0KMTk1Mi0wNS0wMSwzNC43Mw0KMTk1Mi0wNi0wMSwzNC41Mw0KMTk1Mi0wNy0wMSwzNC41Nw0KMTk1Mi0wOC0wMSwzNC41OA0KMTk1Mi0wOS0wMSwzNC41MQ0KMTk1Mi0xMC0wMSwzNC42Mw0KMTk1Mi0xMS0wMSwzNC43Mg0KMTk1Mi0xMi0wMSwzNC43OQ0KMTk1My0wMS0wMSwzNC44OA0KMTk1My0wMi0wMSwzNC45NQ0KMTk1My0wMy0wMSwzNC45Mg0KMTk1My0wNC0wMSwzNC45Mw0KMTk1My0wNS0wMSwzNC45MA0KMTk1My0wNi0wMSwzNC44OQ0KMTk1My0wNy0wMSwzNC45MQ0KMTk1My0wOC0wMSwzNC45MQ0KMTk1My0wOS0wMSwzNC43Ng0KMTk1My0xMC0wMSwzNC43OQ0KMTk1My0xMS0wMSwzNC44Ng0KMTk1My0xMi0wMSwzNC44NQ0KMTk1NC0wMS0wMSwzNC44Ng0KMTk1NC0wMi0wMSwzNC44OA0KMTk1NC0wMy0wMSwzNC45Mg0KMTk1NC0wNC0wMSwzNS4wNA0KMTk1NC0wNS0wMSwzNS4wOA0KMTk1NC0wNi0wMSwzNS4wNw0KMTk1NC0wNy0wMSwzNS4wOA0KMTk1NC0wOC0wMSwzNS4wNQ0KMTk1NC0wOS0wMSwzNS4wOA0KMTk1NC0xMC0wMSwzNS4wOA0KMTk1NC0xMS0wMSwzNS4wNg0KMTk1NC0xMi0wMSwzNS4wNA0KMTk1NS0wMS0wMSwzNS4wNA0KMTk1NS0wMi0wMSwzNS4wNQ0KMTk1NS0wMy0wMSwzNS4wNg0KMTk1NS0wNC0wMSwzNS4wNQ0KMTk1NS0wNS0wMSwzNS4wNQ0KMTk1NS0wNi0wMSwzNS4wNA0KMTk1NS0wNy0wMSwzNS4wNQ0KMTk1NS0wOC0wMSwzNS4wMw0KMTk1NS0wOS0wMSwzNC45OA0KMTk1NS0xMC0wMSwzNC45OA0KMTk1NS0xMS0wMSwzNC45OA0KMTk1NS0xMi0wMSwzNC45Nw0KMTk1Ni0wMS0wMSwzNC45OQ0KMTk1Ni0wMi0wMSwzNC45OQ0KMTk1Ni0wMy0wMSwzNC45OQ0KMTk1Ni0wNC0wMSwzNC45OA0KMTk1Ni0wNS0wMSwzNC45OQ0KMTk1Ni0wNi0wMSwzNC45OA0KMTk1Ni0wNy0wMSwzNS4wMA0KMTk1Ni0wOC0wMSwzNS4wMg0KMTk1Ni0wOS0wMSwzNS4wMQ0KMTk1Ni0xMC0wMSwzNS4wMg0KMTk1Ni0xMS0wMSwzNC45OA0KMTk1Ni0xMi0wMSwzNC45MA0KMTk1Ny0wMS0wMSwzNC44OA0KMTk1Ny0wMi0wMSwzNC44OQ0KMTk1Ny0wMy0wMSwzNC45MQ0KMTk1Ny0wNC0wMSwzNC45Mg0KMTk1Ny0wNS0wMSwzNC45Mw0KMTk1Ny0wNi0wMSwzNC45OQ0KMTk1Ny0wNy0wMSwzNC45OQ0KMTk1Ny0wOC0wMSwzNC45Nw0KMTk1Ny0wOS0wMSwzNC45Ng0KMTk1Ny0xMC0wMSwzNC45Nw0KMTk1Ny0xMS0wMSwzNC45Nw0KMTk1Ny0xMi0wMSwzNC45OQ0KMTk1OC0wMS0wMSwzNS4wMQ0KMTk1OC0wMi0wMSwzNS4wOA0KMTk1OC0wMy0wMSwzNS4wOQ0KMTk1OC0wNC0wMSwzNS4xMA0KMTk1OC0wNS0wMSwzNS4xMQ0KMTk1OC0wNi0wMSwzNS4xMA0KMTk1OC0wNy0wMSwzNS4xMQ0KMTk1OC0wOC0wMSwzNS4xMA0KMTk1OC0wOS0wMSwzNS4xMA0KMTk1OC0xMC0wMSwzNS4xMQ0KMTk1OC0xMS0wMSwzNS4xMA0KMTk1OC0xMi0wMSwzNS4wOQ0KMTk1OS0wMS0wMSwzNS4wNw0KMTk1OS0wMi0wMSwzNS4wNg0KMTk1OS0wMy0wMSwzNS4wNg0KMTk1OS0wNC0wMSwzNS4wOA0KMTk1OS0wNS0wMSwzNS4xMg0KMTk1OS0wNi0wMSwzNS4xMg0KMTk1OS0wNy0wMSwzNS4xMw0KMTk1OS0wOC0wMSwzNS4xMg0KMTk1OS0wOS0wMSwzNS4xMw0KMTk1OS0xMC0wMSwzNS4xMA0KMTk1OS0xMS0wMSwzNS4wNg0KMTk1OS0xMi0wMSwzNS4wNQ0KMTk2MC0wMS0wMSwzNS4wOQ0KMTk2MC0wMi0wMSwzNS4wOQ0KMTk2MC0wMy0wMSwzNS4wOQ0KMTk2MC0wNC0wMSwzNS4xMQ0KMTk2MC0wNS0wMSwzNS4xMA0KMTk2MC0wNi0wMSwzNS4wOQ0KMTk2MC0wNy0wMSwzNS4xMA0KMTk2MC0wOC0wMSwzNS4xMw0KMTk2MC0wOS0wMSwzNS4yMg0KMTk2MC0xMC0wMSwzNS43OA0KMTk2MC0xMS0wMSwzNS44Mg0KMTk2MC0xMi0wMSwzNS41NA0KMTk2MS0wMS0wMSwzNS41Ng0KMTk2MS0wMi0wMSwzNS4xNw0KMTk2MS0wMy0wMSwzNS4wOA0KMTk2MS0wNC0wMSwzNS4wOA0KMTk2MS0wNS0wMSwzNS4wNg0KMTk2MS0wNi0wMSwzNS4wNw0KMTk2MS0wNy0wMSwzNS4xMQ0KMTk2MS0wOC0wMSwzNS4xNg0KMTk2MS0wOS0wMSwzNS4xOQ0KMTk2MS0xMC0wMSwzNS4xOQ0KMTk2MS0xMS0wMSwzNS4xOQ0KMTk2MS0xMi0wMSwzNS4xNQ0KMTk2Mi0wMS0wMSwzNS4xNg0KMTk2Mi0wMi0wMSwzNS4xMw0KMTk2Mi0wMy0wMSwzNS4wOQ0KMTk2Mi0wNC0wMSwzNS4wOA0KMTk2Mi0wNS0wMSwzNS4wOA0KMTk2Mi0wNi0wMSwzNS4xMA0KMTk2Mi0wNy0wMSwzNS4xMg0KMTk2Mi0wOC0wMSwzNS4xMg0KMTk2Mi0wOS0wMSwzNS4xMw0KMTk2Mi0xMC0wMSwzNS4xNA0KMTk2Mi0xMS0wMSwzNS4wOQ0KMTk2Mi0xMi0wMSwzNS4wOA0KMTk2My0wMS0wMSwzNS4wNg0KMTk2My0wMi0wMSwzNS4wOA0KMTk2My0wMy0wMSwzNS4xMA0KMTk2My0wNC0wMSwzNS4xMA0KMTk2My0wNS0wMSwzNS4wOA0KMTk2My0wNi0wMSwzNS4wOA0KMTk2My0wNy0wMSwzNS4wOQ0KMTk2My0wOC0wMSwzNS4xMA0KMTk2My0wOS0wMSwzNS4wOA0KMTk2My0xMC0wMSwzNS4wOA0KMTk2My0xMS0wMSwzNS4wOA0KMTk2My0xMi0wMSwzNS4wOA0KMTk2NC0wMS0wMSwzNS4wOA0KMTk2NC0wMi0wMSwzNS4wOA0KMTk2NC0wMy0wMSwzNS4wOA0KMTk2NC0wNC0wMSwzNS4wOA0KMTk2NC0wNS0wMSwzNS4wOA0KMTk2NC0wNi0wMSwzNS4wOA0KMTk2NC0wNy0wMSwzNS4wOA0KMTk2NC0wOC0wMSwzNS4wOA0KMTk2NC0wOS0wMSwzNS4wOQ0KMTk2NC0xMC0wMSwzNS4xMQ0KMTk2NC0xMS0wMSwzNS4xMA0KMTk2NC0xMi0wMSwzNS4xMg0KMTk2NS0wMS0wMSwzNS4xMw0KMTk2NS0wMi0wMSwzNS4xNA0KMTk2NS0wMy0wMSwzNS4xNg0KMTk2NS0wNC0wMSwzNS4xNA0KMTk2NS0wNS0wMSwzNS4xMA0KMTk2NS0wNi0wMSwzNS4xMA0KMTk2NS0wNy0wMSwzNS4xMg0KMTk2NS0wOC0wMSwzNS4xNQ0KMTk2NS0wOS0wMSwzNS4xNA0KMTk2NS0xMC0wMSwzNS4xMQ0KMTk2NS0xMS0wMSwzNS4xMQ0KMTk2NS0xMi0wMSwzNS4xMw0KMTk2Ni0wMS0wMSwzNS4xNQ0KMTk2Ni0wMi0wMSwzNS4xNw0KMTk2Ni0wMy0wMSwzNS4xNQ0KMTk2Ni0wNC0wMSwzNS4xMw0KMTk2Ni0wNS0wMSwzNS4xMg0KMTk2Ni0wNi0wMSwzNS4xNQ0KMTk2Ni0wNy0wMSwzNS4xOA0KMTk2Ni0wOC0wMSwzNS4xOA0KMTk2Ni0wOS0wMSwzNS4xOQ0KMTk2Ni0xMC0wMSwzNS4xNg0KMTk2Ni0xMS0wMSwzNS4xNg0KMTk2Ni0xMi0wMSwzNS4xOA0KMTk2Ny0wMS0wMSwzNS4xOA0KMTk2Ny0wMi0wMSwzNS4xNw0KMTk2Ny0wMy0wMSwzNS4xNg0KMTk2Ny0wNC0wMSwzNS4xNw0KMTk2Ny0wNS0wMSwzNS4xOA0KMTk2Ny0wNi0wMSwzNS4xOQ0KMTk2Ny0wNy0wMSwzNS4xOQ0KMTk2Ny0wOC0wMSwzNS4xOQ0KMTk2Ny0wOS0wMSwzNS4xOQ0KMTk2Ny0xMC0wMSwzNS4xOQ0KMTk2Ny0xMS0wMSwzNS4yMA0KMTk2Ny0xMi0wMSwzNS4xOQ0KMTk2OC0wMS0wMSwzNS4xOA0KMTk2OC0wMi0wMSwzNS4yMA0KMTk2OC0wMy0wMSwzNS4yMA0KMTk2OC0wNC0wMSwzNy44NzgNCjE5NjgtMDUtMDEsNDAuNjkxDQoxOTY4LTA2LTAxLDQxLjExMQ0KMTk2OC0wNy0wMSwzOS41MzANCjE5NjgtMDgtMDEsMzkuMTcwDQoxOTY4LTA5LTAxLDQwLjIwMw0KMTk2OC0xMC0wMSwzOS4yMTANCjE5NjgtMTEtMDEsMzkuNzk1DQoxOTY4LTEyLTAxLDQxLjExMw0KMTk2OS0wMS0wMSw0Mi4yOTENCjE5NjktMDItMDEsNDIuNjE2DQoxOTY5LTAzLTAxLDQzLjE3MQ0KMTk2OS0wNC0wMSw0My4yOTUNCjE5NjktMDUtMDEsNDMuNDYxDQoxOTY5LTA2LTAxLDQxLjQ0Mg0KMTk2OS0wNy0wMSw0MS43NTUNCjE5NjktMDgtMDEsNDEuMTI4DQoxOTY5LTA5LTAxLDQwLjg2NQ0KMTk2OS0xMC0wMSw0MC40NzYNCjE5NjktMTEtMDEsMzcuNDUyDQoxOTY5LTEyLTAxLDM1LjE4OQ0KMTk3MC0wMS0wMSwzNC45NDYNCjE5NzAtMDItMDEsMzQuOTk0DQoxOTcwLTAzLTAxLDM1LjA4OQ0KMTk3MC0wNC0wMSwzNS42MjMNCjE5NzAtMDUtMDEsMzUuOTU4DQoxOTcwLTA2LTAxLDM1LjQzNw0KMTk3MC0wNy0wMSwzNS4zMjkNCjE5NzAtMDgtMDEsMzUuMzc3DQoxOTcwLTA5LTAxLDM2LjE5Ng0KMTk3MC0xMC0wMSwzNy41NTMNCjE5NzAtMTEtMDEsMzcuNDU1DQoxOTcwLTEyLTAxLDM3LjQzNA0KMTk3MS0wMS0wMSwzNy44NjgNCjE5NzEtMDItMDEsMzguNzE2DQoxOTcxLTAzLTAxLDM4Ljg3Mw0KMTk3MS0wNC0wMSwzOS4wMDENCjE5NzEtMDUtMDEsNDAuNDkzDQoxOTcxLTA2LTAxLDQwLjEwNQ0KMTk3MS0wNy0wMSw0MC45MjkNCjE5NzEtMDgtMDEsNDIuNzIyDQoxOTcxLTA5LTAxLDQxLjk3Ng0KMTk3MS0xMC0wMSw0Mi40NzMNCjE5NzEtMTEtMDEsNDIuODQyDQoxOTcxLTEyLTAxLDQzLjQ1NQ0KMTk3Mi0wMS0wMSw0NS42NDANCjE5NzItMDItMDEsNDguMjM3DQoxOTcyLTAzLTAxLDQ4LjI4OA0KMTk3Mi0wNC0wMSw0OS4wMjYNCjE5NzItMDUtMDEsNTQuNTAwDQoxOTcyLTA2LTAxLDYyLjE3MA0KMTk3Mi0wNy0wMSw2NS41NTgNCjE5NzItMDgtMDEsNjYuOTE3DQoxOTcyLTA5LTAxLDY1LjU4OQ0KMTk3Mi0xMC0wMSw2NC44MjQNCjE5NzItMTEtMDEsNjIuNzI2DQoxOTcyLTEyLTAxLDYzLjc3OQ0KMTk3My0wMS0wMSw2NS4xMjcNCjE5NzMtMDItMDEsNzMuOTcxDQoxOTczLTAzLTAxLDg0LjEwNQ0KMTk3My0wNC0wMSw5MC40NDENCjE5NzMtMDUtMDEsMTAxLjYyMw0KMTk3My0wNi0wMSwxMTkuODAwDQoxOTczLTA3LTAxLDEyMC4zNjQNCjE5NzMtMDgtMDEsMTA2LjIyNQ0KMTk3My0wOS0wMSwxMDMuMDM0DQoxOTczLTEwLTAxLDk5LjkyMw0KMTk3My0xMS0wMSw5NC42NDUNCjE5NzMtMTItMDEsMTA2LjIzNg0KMTk3NC0wMS0wMSwxMjkuMDI3DQoxOTc0LTAyLTAxLDE1MC4wMDANCjE5NzQtMDMtMDEsMTY4LjI5OA0KMTk3NC0wNC0wMSwxNzIuMjQzDQoxOTc0LTA1LTAxLDE2My41NjgNCjE5NzQtMDYtMDEsMTU0LjAxMw0KMTk3NC0wNy0wMSwxNDIuMjgzDQoxOTc0LTA4LTAxLDE1NC4zNjINCjE5NzQtMDktMDEsMTUxLjY2MA0KMTk3NC0xMC0wMSwxNTguNTMzDQoxOTc0LTExLTAxLDE4MS40ODMNCjE5NzQtMTItMDEsMTgzLjY4Mw0KMTk3NS0wMS0wMSwxNzYuODY0DQoxOTc1LTAyLTAxLDE3OS41NTMNCjE5NzUtMDMtMDEsMTc4LjIwMw0KMTk3NS0wNC0wMSwxNzAuMDY2DQoxOTc1LTA1LTAxLDE2Ny4yOTMNCjE5NzUtMDYtMDEsMTY0LjI1MA0KMTk3NS0wNy0wMSwxNjQuODY3DQoxOTc1LTA4LTAxLDE2My4xNjgNCjE5NzUtMDktMDEsMTQ0LjMxMQ0KMTk3NS0xMC0wMSwxNDIuNjkxDQoxOTc1LTExLTAxLDE0Mi41NjUNCjE5NzUtMTItMDEsMTM5LjI3OQ0KMTk3Ni0wMS0wMSwxMzEuNjk1DQoxOTc2LTAyLTAxLDEzMC45NDANCjE5NzYtMDMtMDEsMTMyLjY3Ng0KMTk3Ni0wNC0wMSwxMjcuOTEwDQoxOTc2LTA1LTAxLDEyNi45MTMNCjE5NzYtMDYtMDEsMTI1LjY4NA0KMTk3Ni0wNy0wMSwxMTcuODY2DQoxOTc2LTA4LTAxLDExMC4wNTUNCjE5NzYtMDktMDEsMTE0LjA5OA0KMTk3Ni0xMC0wMSwxMTYuMDk1DQoxOTc2LTExLTAxLDEzMC4zNDENCjE5NzYtMTItMDEsMTMzLjY3NA0KMTk3Ny0wMS0wMSwxMzIuMzA2DQoxOTc3LTAyLTAxLDEzNi4xNjMNCjE5NzctMDMtMDEsMTQ4LjMzOQ0KMTk3Ny0wNC0wMSwxNDkuMTg0DQoxOTc3LTA1LTAxLDE0Ni41MzYNCjE5NzctMDYtMDEsMTQwLjgyOA0KMTk3Ny0wNy0wMSwxNDMuMzgzDQoxOTc3LTA4LTAxLDE0NS4wMDkNCjE5NzctMDktMDEsMTQ5LjMzNA0KMTk3Ny0xMC0wMSwxNTguODQ4DQoxOTc3LTExLTAxLDE2Mi4xMzINCjE5NzctMTItMDEsMTYwLjQ4MA0KMTk3OC0wMS0wMSwxNzMuMjA3DQoxOTc4LTAyLTAxLDE3OC4zNjANCjE5NzgtMDMtMDEsMTgzLjc3MQ0KMTk3OC0wNC0wMSwxNzUuMzIzDQoxOTc4LTA1LTAxLDE3Ni4xOTANCjE5NzgtMDYtMDEsMTgzLjc2MQ0KMTk3OC0wNy0wMSwxODguNTY5DQoxOTc4LTA4LTAxLDIwNi40NTUNCjE5NzgtMDktMDEsMjEyLjIxMg0KMTk3OC0xMC0wMSwyMjcuNDMyDQoxOTc4LTExLTAxLDIwNy4wODINCjE5NzgtMTItMDEsMjA3Ljg5NQ0KMTk3OS0wMS0wMSwyMjYuOTg2DQoxOTc5LTAyLTAxLDI0NS41OTANCjE5NzktMDMtMDEsMjQyLjM0OA0KMTk3OS0wNC0wMSwyMzkuMTc0DQoxOTc5LTA1LTAxLDI1Ny43MzgNCjE5NzktMDYtMDEsMjc5LjM2MA0KMTk3OS0wNy0wMSwyOTUuMDA5DQoxOTc5LTA4LTAxLDMwMC43OTMNCjE5NzktMDktMDEsMzU0LjQ5OA0KMTk3OS0xMC0wMSwzOTAuOTU5DQoxOTc5LTExLTAxLDM5MS41MTgNCjE5NzktMTItMDEsNDYzLjY2Ng0KMTk4MC0wMS0wMSw2NzQuNTgwDQoxOTgwLTAyLTAxLDY2NS44OTMNCjE5ODAtMDMtMDEsNTU0LjI3Ng0KMTk4MC0wNC0wMSw1MTYuNzEwDQoxOTgwLTA1LTAxLDUxNC4yNjgNCjE5ODAtMDYtMDEsNjAwLjc4Ng0KMTk4MC0wNy0wMSw2NDUuNzUwDQoxOTgwLTA4LTAxLDYyNi4zNjANCjE5ODAtMDktMDEsNjczLjk0MQ0KMTk4MC0xMC0wMSw2NjIuMjcwDQoxOTgwLTExLTAxLDYyMy44NzUNCjE5ODAtMTItMDEsNTk2LjcxMg0KMTk4MS0wMS0wMSw1NTcuODEzDQoxOTgxLTAyLTAxLDUwMC44MDANCjE5ODEtMDMtMDEsNDk5LjY5Mw0KMTk4MS0wNC0wMSw0OTYuNjI1DQoxOTgxLTA1LTAxLDQ4MC4zMTYNCjE5ODEtMDYtMDEsNDYwLjUwMA0KMTk4MS0wNy0wMSw0MDkuMjg0DQoxOTgxLTA4LTAxLDQxMC4yNDANCjE5ODEtMDktMDEsNDQzLjc3Mw0KMTk4MS0xMC0wMSw0MzcuNjgwDQoxOTgxLTExLTAxLDQxMy40MDUNCjE5ODEtMTItMDEsNDEwLjExOQ0KMTk4Mi0wMS0wMSwzODQuMTYzDQoxOTgyLTAyLTAxLDM3NC40NTgNCjE5ODItMDMtMDEsMzMwLjQxMw0KMTk4Mi0wNC0wMSwzNTAuMzM1DQoxOTgyLTA1LTAxLDMzNC41MDUNCjE5ODItMDYtMDEsMzE0Ljk2MQ0KMTk4Mi0wNy0wMSwzMzcuODk1DQoxOTgyLTA4LTAxLDM2My40MTMNCjE5ODItMDktMDEsNDM4LjE1MA0KMTk4Mi0xMC0wMSw0MjIuNzg2DQoxOTgyLTExLTAxLDQxNS4xMTQNCjE5ODItMTItMDEsNDQ0Ljc3Ng0KMTk4My0wMS0wMSw0ODEuODM4DQoxOTgzLTAyLTAxLDQ5My40ODgNCjE5ODMtMDMtMDEsNDIwLjcwNw0KMTk4My0wNC0wMSw0MzMuMTcxDQoxOTgzLTA1LTAxLDQzNy4zOTMNCjE5ODMtMDYtMDEsNDEzLjE0OA0KMTk4My0wNy0wMSw0MjIuNjQ1DQoxOTgzLTA4LTAxLDQxNi4yMDUNCjE5ODMtMDktMDEsNDEyLjI0NQ0KMTk4My0xMC0wMSwzOTQuMjQ1DQoxOTgzLTExLTAxLDM4MS4wMTYNCjE5ODMtMTItMDEsMzg4LjA2MA0KMTk4NC0wMS0wMSwzNzAuNzM1DQoxOTg0LTAyLTAxLDM4Ni4wMzgNCjE5ODQtMDMtMDEsMzk0Ljc0Mw0KMTk4NC0wNC0wMSwzODEuMzcxDQoxOTg0LTA1LTAxLDM3Ni45NTcNCjE5ODQtMDYtMDEsMzc4LjMxNA0KMTk4NC0wNy0wMSwzNDcuNTk4DQoxOTg0LTA4LTAxLDM0Ny42NzcNCjE5ODQtMDktMDEsMzQwLjk0NQ0KMTk4NC0xMC0wMSwzNDAuMjE3DQoxOTg0LTExLTAxLDM0MS4yODYNCjE5ODQtMTItMDEsMzE5LjYyMg0KMTk4NS0wMS0wMSwzMDIuODUyDQoxOTg1LTAyLTAxLDI5OS44MzMNCjE5ODUtMDMtMDEsMzAzLjIwNQ0KMTk4NS0wNC0wMSwzMjQuODgzDQoxOTg1LTA1LTAxLDMxNi4zOTUNCjE5ODUtMDYtMDEsMzE2LjI5OA0KMTk4NS0wNy0wMSwzMTcuMjAyDQoxOTg1LTA4LTAxLDMzMC4xMzENCjE5ODUtMDktMDEsMzIzLjc2NA0KMTk4NS0xMC0wMSwzMjYuMDkzDQoxOTg1LTExLTAxLDMyNS41NDgNCjE5ODUtMTItMDEsMzIxLjk4NQ0KMTk4Ni0wMS0wMSwzNDUuNTYxDQoxOTg2LTAyLTAxLDMzOS4wNTMNCjE5ODYtMDMtMDEsMzQ2LjA5NQ0KMTk4Ni0wNC0wMSwzNDAuNzE2DQoxOTg2LTA1LTAxLDM0Mi4zMjUNCjE5ODYtMDYtMDEsMzQyLjc5OA0KMTk4Ni0wNy0wMSwzNDguNTU0DQoxOTg2LTA4LTAxLDM3Ni4yOTANCjE5ODYtMDktMDEsNDE4LjE1Mg0KMTk4Ni0xMC0wMSw0MjMuODYzDQoxOTg2LTExLTAxLDM5Ni45ODMNCjE5ODYtMTItMDEsMzkxLjU5NQ0KMTk4Ny0wMS0wMSw0MDguNTI0DQoxOTg3LTAyLTAxLDQwMS4wNDUNCjE5ODctMDMtMDEsNDA4Ljg0OA0KMTk4Ny0wNC0wMSw0MzkuNjY1DQoxOTg3LTA1LTAxLDQ2MS42NTANCjE5ODctMDYtMDEsNDQ5LjI4Mg0KMTk4Ny0wNy0wMSw0NTAuMzMwDQoxOTg3LTA4LTAxLDQ2MC45ODgNCjE5ODctMDktMDEsNDYwLjEyMA0KMTk4Ny0xMC0wMSw0NjUuNzY0DQoxOTg3LTExLTAxLDQ2OC4xNDANCjE5ODctMTItMDEsNDg3LjA3OQ0KMTk4OC0wMS0wMSw0NzcuNzU4DQoxOTg4LTAyLTAxLDQ0Mi4xMjQNCjE5ODgtMDMtMDEsNDQzLjQ5MQ0KMTk4OC0wNC0wMSw0NTEuNTU4DQoxOTg4LTA1LTAxLDQ1MS4zMjANCjE5ODgtMDYtMDEsNDUxLjY1Nw0KMTk4OC0wNy0wMSw0MzcuNDUyDQoxOTg4LTA4LTAxLDQzMS4wNjQNCjE5ODgtMDktMDEsNDEzLjQzOQ0KMTk4OC0xMC0wMSw0MDYuMzkwDQoxOTg4LTExLTAxLDQxOS45NjYNCjE5ODgtMTItMDEsNDE5LjI0OA0KMTk4OS0wMS0wMSw0MDQuNDQ1DQoxOTg5LTAyLTAxLDM4Ny45NzMNCjE5ODktMDMtMDEsMzkwLjI3NA0KMTk4OS0wNC0wMSwzODQuNzIwDQoxOTg5LTA1LTAxLDM3MS4zNTANCjE5ODktMDYtMDEsMzY3LjcyNw0KMTk4OS0wNy0wMSwzNzUuMjEwDQoxOTg5LTA4LTAxLDM2NS41NDgNCjE5ODktMDktMDEsMzYxLjc5OA0KMTk4OS0xMC0wMSwzNjYuODAwDQoxOTg5LTExLTAxLDM5NC4zNjENCjE5ODktMTItMDEsNDA5LjY1NQ0KMTk5MC0wMS0wMSw0MTAuMTE4DQoxOTkwLTAyLTAxLDQxNi41NDMNCjE5OTAtMDMtMDEsMzkzLjY2MQ0KMTk5MC0wNC0wMSwzNzQuOTI5DQoxOTkwLTA1LTAxLDM2OC44NTUNCjE5OTAtMDYtMDEsMzUyLjY1Nw0KMTk5MC0wNy0wMSwzNjEuODIwDQoxOTkwLTA4LTAxLDM5NC44NjENCjE5OTAtMDktMDEsMzg5LjU2MA0KMTk5MC0xMC0wMSwzODEuMzMzDQoxOTkwLTExLTAxLDM4MS44NjYNCjE5OTAtMTItMDEsMzc4LjE2MQ0KMTk5MS0wMS0wMSwzODQuNTkxDQoxOTkxLTAyLTAxLDM2My43NDgNCjE5OTEtMDMtMDEsMzYzLjM5MA0KMTk5MS0wNC0wMSwzNTguMDU1DQoxOTkxLTA1LTAxLDM1Ny4xMTcNCjE5OTEtMDYtMDEsMzY2LjM2MA0KMTk5MS0wNy0wMSwzNjguMDEzDQoxOTkxLTA4LTAxLDM1Ni43MjENCjE5OTEtMDktMDEsMzQ4LjQ2MA0KMTk5MS0xMC0wMSwzNTguODI2DQoxOTkxLTExLTAxLDM1OS45NjANCjE5OTEtMTItMDEsMzYxLjg3NQ0KMTk5Mi0wMS0wMSwzNTQuNDM2DQoxOTkyLTAyLTAxLDM1My44NTMNCjE5OTItMDMtMDEsMzQ0LjY0MQ0KMTk5Mi0wNC0wMSwzMzguNzI4DQoxOTkyLTA1LTAxLDMzNy4wMzkNCjE5OTItMDYtMDEsMzQwLjc4NA0KMTk5Mi0wNy0wMSwzNTIuNDUyDQoxOTkyLTA4LTAxLDM0My42MDMNCjE5OTItMDktMDEsMzQ1LjMwMA0KMTk5Mi0xMC0wMSwzNDQuMjc3DQoxOTkyLTExLTAxLDMzNC45MjQNCjE5OTItMTItMDEsMzM0LjY1Nw0KMTk5My0wMS0wMSwzMjguOTkzDQoxOTkzLTAyLTAxLDMyOS4zMTANCjE5OTMtMDMtMDEsMzI5Ljk3NA0KMTk5My0wNC0wMSwzNDEuOTQ4DQoxOTkzLTA1LTAxLDM2Ny4wNDUNCjE5OTMtMDYtMDEsMzcxLjkxNA0KMTk5My0wNy0wMSwzOTIuMDM0DQoxOTkzLTA4LTAxLDM3OS43OTUNCjE5OTMtMDktMDEsMzU1LjU2MQ0KMTk5My0xMC0wMSwzNjQuMDA1DQoxOTkzLTExLTAxLDM3My45MzkNCjE5OTMtMTItMDEsMzgzLjI0Mw0KMTk5NC0wMS0wMSwzODcuMTEwDQoxOTk0LTAyLTAxLDM4MS42NTgNCjE5OTQtMDMtMDEsMzg0LjAwMA0KMTk5NC0wNC0wMSwzNzcuOTA4DQoxOTk0LTA1LTAxLDM4MS4zNDMNCjE5OTQtMDYtMDEsMzg1LjcxNA0KMTk5NC0wNy0wMSwzODUuNDUwDQoxOTk0LTA4LTAxLDM4MC4yMDcNCjE5OTQtMDktMDEsMzkxLjM0OA0KMTk5NC0xMC0wMSwzOTAuMTY0DQoxOTk0LTExLTAxLDM4NC4zNzcNCjE5OTQtMTItMDEsMzc5LjQ4MA0KMTk5NS0wMS0wMSwzNzguNzM4DQoxOTk1LTAyLTAxLDM3Ni43NDUNCjE5OTUtMDMtMDEsMzgxLjgyMA0KMTk5NS0wNC0wMSwzOTEuMzM5DQoxOTk1LTA1LTAxLDM4NS4yMzENCjE5OTUtMDYtMDEsMzg3LjYxOA0KMTk5NS0wNy0wMSwzODYuMTM4DQoxOTk1LTA4LTAxLDM4My41MDINCjE5OTUtMDktMDEsMzgyLjkzMQ0KMTk5NS0xMC0wMSwzODMuMjAyDQoxOTk1LTExLTAxLDM4NS4yMDkNCjE5OTUtMTItMDEsMzg3LjQ0NQ0KMTk5Ni0wMS0wMSwzOTguNjk1DQoxOTk2LTAyLTAxLDQwNC45MTkNCjE5OTYtMDMtMDEsMzk2LjUxMg0KMTk5Ni0wNC0wMSwzOTIuODcwDQoxOTk2LTA1LTAxLDM5MS45OTANCjE5OTYtMDYtMDEsMzg1LjI0NQ0KMTk5Ni0wNy0wMSwzODMuNDU3DQoxOTk2LTA4LTAxLDM4Ny41MTANCjE5OTYtMDktMDEsMzgzLjI5MA0KMTk5Ni0xMC0wMSwzODAuOTA5DQoxOTk2LTExLTAxLDM3Ny44NjkNCjE5OTYtMTItMDEsMzY5LjMzOA0KMTk5Ny0wMS0wMSwzNTUuMDI1DQoxOTk3LTAyLTAxLDM0Ni40MDANCjE5OTctMDMtMDEsMzUyLjMxMQ0KMTk5Ny0wNC0wMSwzNDQuNzA3DQoxOTk3LTA1LTAxLDM0NC4xMDANCjE5OTctMDYtMDEsMzQwLjgwNQ0KMTk5Ny0wNy0wMSwzMjMuNzgwDQoxOTk3LTA4LTAxLDMyMy45OTgNCjE5OTctMDktMDEsMzIyLjYxNg0KMTk5Ny0xMC0wMSwzMjQuODYzDQoxOTk3LTExLTAxLDMwNi4zNDUNCjE5OTctMTItMDEsMjg4Ljc3Ng0KMTk5OC0wMS0wMSwyODkuMjY0DQoxOTk4LTAyLTAxLDI5Ny43NDMNCjE5OTgtMDMtMDEsMjk1Ljg3MA0KMTk5OC0wNC0wMSwzMDguNTU4DQoxOTk4LTA1LTAxLDI5OC45NzENCjE5OTgtMDYtMDEsMjkyLjIyMw0KMTk5OC0wNy0wMSwyOTIuODc0DQoxOTk4LTA4LTAxLDI4NC4yMjgNCjE5OTgtMDktMDEsMjg4LjY2MQ0KMTk5OC0xMC0wMSwyOTYuNTk1DQoxOTk4LTExLTAxLDI5NC4yNDMNCjE5OTgtMTItMDEsMjkxLjM1Nw0KMTk5OS0wMS0wMSwyODcuMzMzDQoxOTk5LTAyLTAxLDI4Ny40OTUNCjE5OTktMDMtMDEsMjg2LjI0Mw0KMTk5OS0wNC0wMSwyODIuNjIwDQoxOTk5LTA1LTAxLDI3Ni45MzINCjE5OTktMDYtMDEsMjYxLjQwMg0KMTk5OS0wNy0wMSwyNTYuMTk4DQoxOTk5LTA4LTAxLDI1Ni45MzYNCjE5OTktMDktMDEsMjY0LjQ3MA0KMTk5OS0xMC0wMSwzMTEuNTYyDQoxOTk5LTExLTAxLDI5My42NTANCjE5OTktMTItMDEsMjgzLjc0Mw0KMjAwMC0wMS0wMSwyODQuNTkwDQoyMDAwLTAyLTAxLDMwMC44NTUNCjIwMDAtMDMtMDEsMjg2LjcwNA0KMjAwMC0wNC0wMSwyNzkuOTYxDQoyMDAwLTA1LTAxLDI3NS4yOTMNCjIwMDAtMDYtMDEsMjg1LjM2OA0KMjAwMC0wNy0wMSwyODIuMTUyDQoyMDAwLTA4LTAxLDI3NC41MjMNCjIwMDAtMDktMDEsMjczLjY3Ng0KMjAwMC0xMC0wMSwyNzAuNDA1DQoyMDAwLTExLTAxLDI2NS45ODkNCjIwMDAtMTItMDEsMjcxLjg5Mg0KMjAwMS0wMS0wMSwyNjUuOTM0DQoyMDAxLTAyLTAxLDI2Mi4wMTgNCjIwMDEtMDMtMDEsMjYzLjI3Mw0KMjAwMS0wNC0wMSwyNjAuNzUwDQoyMDAxLTA1LTAxLDI3Mi4wNTcNCjIwMDEtMDYtMDEsMjcwLjczOA0KMjAwMS0wNy0wMSwyNjcuNzA3DQoyMDAxLTA4LTAxLDI3Mi42NTcNCjIwMDEtMDktMDEsMjgyLjQ3OA0KMjAwMS0xMC0wMSwyODMuMzIyDQoyMDAxLTExLTAxLDI3Ni4yNDgNCjIwMDEtMTItMDEsMjc1Ljk5Mg0KMjAwMi0wMS0wMSwyODEuNzY0DQoyMDAyLTAyLTAxLDI5NS42ODMNCjIwMDItMDMtMDEsMjk0LjM1Mw0KMjAwMi0wNC0wMSwzMDIuODYyDQoyMDAyLTA1LTAxLDMxNC40ODANCjIwMDItMDYtMDEsMzIxLjUzNg0KMjAwMi0wNy0wMSwzMTMuNTY3DQoyMDAyLTA4LTAxLDMxMC4wNDUNCjIwMDItMDktMDEsMzE4LjgwMA0KMjAwMi0xMC0wMSwzMTYuNzQ4DQoyMDAyLTExLTAxLDMxOS4yNTUNCjIwMDItMTItMDEsMzMzLjMwMA0KMjAwMy0wMS0wMSwzNTYuODY0DQoyMDAzLTAyLTAxLDM1OS41NzUNCjIwMDMtMDMtMDEsMzQxLjU2NA0KMjAwMy0wNC0wMSwzMjguMjA4DQoyMDAzLTA1LTAxLDM1NS40MDUNCjIwMDMtMDYtMDEsMzU2LjkxMg0KMjAwMy0wNy0wMSwzNTAuNzY1DQoyMDAzLTA4LTAxLDM1OC45OTMNCjIwMDMtMDktMDEsMzc4Ljg1OQ0KMjAwMy0xMC0wMSwzNzkuMDkzDQoyMDAzLTExLTAxLDM5MC4yMDANCjIwMDMtMTItMDEsNDA3LjY3NA0KMjAwNC0wMS0wMSw0MTQuNDk1DQoyMDA0LTAyLTAxLDQwNC43MzANCjIwMDQtMDMtMDEsNDA1Ljk3Ng0KMjAwNC0wNC0wMSw0MDQuODUwDQoyMDA0LTA1LTAxLDM4My45NTMNCjIwMDQtMDYtMDEsMzkxLjc4MA0KMjAwNC0wNy0wMSwzOTguNDQxDQoyMDA0LTA4LTAxLDQwMC4xMzMNCjIwMDQtMDktMDEsNDA1LjQwMg0KMjAwNC0xMC0wMSw0MjAuMjEwDQoyMDA0LTExLTAxLDQzOS4wNTkNCjIwMDQtMTItMDEsNDQyLjk3NA0KMjAwNS0wMS0wMSw0MjQuMDgwDQoyMDA1LTAyLTAxLDQyMy40MzANCjIwMDUtMDMtMDEsNDM0LjM1NQ0KMjAwNS0wNC0wMSw0MjkuMTQwDQoyMDA1LTA1LTAxLDQyMi45MDMNCjIwMDUtMDYtMDEsNDMwLjMwMg0KMjAwNS0wNy0wMSw0MjQuNzQ1DQoyMDA1LTA4LTAxLDQzNy43NzMNCjIwMDUtMDktMDEsNDU1LjkzNg0KMjAwNS0xMC0wMSw0NzAuMTA3DQoyMDA1LTExLTAxLDQ3Ni42NjgNCjIwMDUtMTItMDEsNTA5LjQyMw0KMjAwNi0wMS0wMSw1NDkuNDMzDQoyMDA2LTAyLTAxLDU1NS41MTgNCjIwMDYtMDMtMDEsNTU3LjIxNQ0KMjAwNi0wNC0wMSw2MTEuODUzDQoyMDA2LTA1LTAxLDY3Ni43NjkNCjIwMDYtMDYtMDEsNTk3Ljg5OA0KMjAwNi0wNy0wMSw2MzMuMDkzDQoyMDA2LTA4LTAxLDYzMS41NTcNCjIwMDYtMDktMDEsNjAwLjE1MA0KMjAwNi0xMC0wMSw1ODYuNjQ4DQoyMDA2LTExLTAxLDYyNi44MjUNCjIwMDYtMTItMDEsNjI5LjUxMw0KMjAwNy0wMS0wMSw2MzAuMzUyDQoyMDA3LTAyLTAxLDY2NS4xMDMNCjIwMDctMDMtMDEsNjU1Ljg5MQ0KMjAwNy0wNC0wMSw2ODAuMDA4DQoyMDA3LTA1LTAxLDY2OC4zMTANCjIwMDctMDYtMDEsNjU1LjcxNA0KMjAwNy0wNy0wMSw2NjUuMjY2DQoyMDA3LTA4LTAxLDY2NC41MzANCjIwMDctMDktMDEsNzEwLjY0NQ0KMjAwNy0xMC0wMSw3NTQuNDgwDQoyMDA3LTExLTAxLDgwOC4zMTENCjIwMDctMTItMDEsODAzLjYxOA0KMjAwOC0wMS0wMSw4ODcuNzg0DQoyMDA4LTAyLTAxLDkyNC4yODMNCjIwMDgtMDMtMDEsOTcxLjA1NQ0KMjAwOC0wNC0wMSw5MTEuNjAwDQoyMDA4LTA1LTAxLDg4OS4xMjUNCjIwMDgtMDYtMDEsODg5LjUzNg0KMjAwOC0wNy0wMSw5NDEuMTY3DQoyMDA4LTA4LTAxLDg0MC4zODgNCjIwMDgtMDktMDEsODI0LjkyMA0KMjAwOC0xMC0wMSw4MTIuODE1DQoyMDA4LTExLTAxLDc1Ny44NTANCjIwMDgtMTItMDEsODE5Ljk0MA0KMjAwOS0wMS0wMSw4NTcuNzI2DQoyMDA5LTAyLTAxLDkzOS43NjMNCjIwMDktMDMtMDEsOTI1Ljk4OQ0KMjAwOS0wNC0wMSw4OTIuNjYzDQoyMDA5LTA1LTAxLDkyNi44NTUNCjIwMDktMDYtMDEsOTQ3LjgwNw0KMjAwOS0wNy0wMSw5MzQuMjcyDQoyMDA5LTA4LTAxLDk0OS41MDANCjIwMDktMDktMDEsOTk2LjQ0Mw0KMjAwOS0xMC0wMSwxMDQzLjUxMQ0KMjAwOS0xMS0wMSwxMTI2LjExOQ0KMjAwOS0xMi0wMSwxMTM1LjAxMg0KMjAxMC0wMS0wMSwxMTE5LjU3NQ0KMjAxMC0wMi0wMSwxMDk1LjgwMA0KMjAxMC0wMy0wMSwxMTE1LjU1NA0KMjAxMC0wNC0wMSwxMTQ4LjQ3NQ0KMjAxMC0wNS0wMSwxMjA0LjMyMQ0KMjAxMC0wNi0wMSwxMjMyLjM4Mg0KMjAxMC0wNy0wMSwxMTk2LjAwMA0KMjAxMC0wOC0wMSwxMjEzLjQ2NA0KMjAxMC0wOS0wMSwxMjcxLjQ2MQ0KMjAxMC0xMC0wMSwxMzQzLjE5MA0KMjAxMC0xMS0wMSwxMzcxLjc4NA0KMjAxMC0xMi0wMSwxMzkzLjUxMg0KMjAxMS0wMS0wMSwxMzYwLjQ3NQ0KMjAxMS0wMi0wMSwxMzcxLjMxMw0KMjAxMS0wMy0wMSwxNDIyLjg0OA0KMjAxMS0wNC0wMSwxNDc0LjQzMQ0KMjAxMS0wNS0wMSwxNTEyLjE4OA0KMjAxMS0wNi0wMSwxNTI4LjM4MA0KMjAxMS0wNy0wMSwxNTY4LjUyNg0KMjAxMS0wOC0wMSwxNzU5LjUwMA0KMjAxMS0wOS0wMSwxNzgwLjY0OA0KMjAxMS0xMC0wMSwxNjY3Ljg5Mw0KMjAxMS0xMS0wMSwxNzM1Ljk3Nw0KMjAxMS0xMi0wMSwxNjUyLjcyNQ0KMjAxMi0wMS0wMSwxNjU2LjA5NQ0KMjAxMi0wMi0wMSwxNzQzLjA5NQ0KMjAxMi0wMy0wMSwxNjc1LjA1Nw0KMjAxMi0wNC0wMSwxNjQ4LjUzOQ0K',
          ),
        ),
      ),
    )
  );
}

/**
 * Builds an array of Afghanistan Election Districts Dataset
 */
function dkan_default_content_afghan_election_districts_dataset(){
  return array(
    'title' => 'Afghanistan Election Districts',
    'sort_value' => '4',
    'status' => '1',
    'uid' => 1,
    'promote' => '0',
    'sticky' => '0',
    'type' => 'dataset',
    'language' => 'und',
    'created' => '1351612344',
    'comment' => '1',
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
    'og_group_ref' => array(
      'und' => array(
        0 => array(
          'title' => 'Geospatial Data Explorer Examples'
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
    'field_contact_email' => array(
      'und' => array(
        0 => array(
          'value' => variable_get('site_mail', NULL),
        ),
      ),
    ),
  );
}

/**
 * Builds an array of Afghanistan Election Districts Dataset Resources
 */
function dkan_default_content_afghan_election_districts_resources(){
  return array(
    array(
      'exported_path' => 'dataset/resource/7fb111b3-ab62-4bf3-864c-56a72347df33',
      'title' => 'District Names',
      'uid' => 1,
      'sort_value' => '5',
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
            'value' => 'You can see this data plotted on a map, by clicking on \'Map\' below. Individual data records can be seen by clicking on each point.',
            'summary' => '',
            'format' => 'plain_text',
            'safe_value' => '<p>You can see this data plotted on a map, by clicking on &#039;Map&#039; below. Individual data records can be seen by clicking on each point.</p>
  ',
            'safe_summary' => '',
          ),
        ),
      ),
      'field_format' => array(
        'und' => array(
          0 => array(
            'tid' => '8',
          ),
        ),
      ),
      'field_upload' => array(
        'und' => array(
          0 => array(
            'service_id' => NULL,
            'grid' => '1',
            'graph' => '0',
            'map' => '1',
            'timeline' => '0',
            'transform' => '0',
            'filename' => 'district_centerpoints.csv',
            'uri' => 'public://district_centerpoints.csv',
            'filemime' => 'text/csv',
            'filesize' => '32378',
            'status' => '1',
            'rdf_mapping' => array(),
            'file' => 'ImxvbiIsImxhdCIsIlVuaXRfVHlwZSIsIkRpc3RfTmFtZSIsIlByb3ZfTmFtZSIsIkRhcmlfRGlzdCIsIkRhcmlfUHJvdiIsIkRpc3RfSUQiLCJQcm92X0lEIgo2MS4zMywzMi40LCJEaXN0cmljdCIsIlFhbGEtZS1LYWgiLCJGYXJhaCIsItmC2YTYudmHINqp2KfZhyIsItmB2LHYp9mHIiwzMTA2LDMxCjYyLjA2LDMyLjQ5LCJEaXN0cmljdCIsIlB1c2h0IFJvZCIsIkZhcmFoIiwi2b7YtNiqINix2YjYryIsItmB2LHYp9mHIiwzMTA1LDMxCjYxLjM3LDMyLjExLCJEaXN0cmljdCIsIlNoaWIgS29oIiwiRmFyYWgiLCLZjti024zYqCDaqdmI2YciLCLZgdix2KfZhyIsMzEwNywzMQo2Mi4yMywzMi4yNCwiUHJvdmluY2lhbF9DZW50ZXIiLCJGYXJhaCIsIkZhcmFoIiwi2YHYsdin2YciLCLZgdix2KfZhyIsMzEwMSwzMQo2MS44MywzMS4zMiwiRGlzdHJpY3QiLCJLYW5nIiwiTmltcm96Iiwi2qnZhtqvIiwi2YbbjNmF2LHZiNiyIiwzNDAyLDM0CjYyLjA0LDMwLjkxLCJQcm92aW5jaWFsX0NlbnRlciIsIlphcmFuaiIsIk5pbXJveiIsItiy2LHZhtisIiwi2YbbjNmF2LHZiNiyIiwzNDAxLDM0CjYyLjg4LDMyLjIxLCJEaXN0cmljdCIsIkJha3dhIiwiRmFyYWgiLCLYqNqp2YjYp9mHIiwi2YHYsdin2YciLDMxMDIsMzEKNjUuMTcsMzAuMTIsIkRpc3RyaWN0IiwiUmVnIiwiS2FuZGFoYXIiLCLYsduM2q8iLCLaqdmG2K/Zh9in2LEiLDMzMDksMzMKNjYuMTcsMzAuOTksIkRpc3RyaWN0IiwiU3BpbiBCb2xkYWsiLCJLYW5kYWhhciIsItiz2b7bjNmGINio2YjZhNiv2qkiLCLaqdmG2K/Zh9in2LEiLDMzMTEsMzMKNjUuMTYsMzEuMDcsIkRpc3RyaWN0IiwiUGFuaiB3YXlpIiwiS2FuZGFoYXIiLCLZvtmG2KzZiNin2KbbjCIsItqp2YbYr9mH2KfYsSIsMzMwNCwzMwo2NC45OSwzMS41OCwiRGlzdHJpY3QiLCJNYXl3YW5kIiwiS2FuZGFoYXIiLCLZhduM2YjZhtivIiwi2qnZhtiv2YfYp9ixIiwzMzA4LDMzCjY1LjcxLDMxLjUxLCJQcm92aW5jaWFsX0NlbnRlciIsIkthbmRhaGFyIiwiS2FuZGFoYXIiLCLaqdmG2K/Zh9in2LEiLCLaqdmG2K/Zh9in2LEiLDMzMDEsMzMKNjUuNCwzMS42NCwiRGlzdHJpY3QiLCJaaGVyYXkiLCJLYW5kYWhhciIsItqY2pPbjSIsItqp2YbYr9mH2KfYsSIsMzMwMywzMwo2Ni41NSwzMS41MiwiRGlzdHJpY3QiLCJBcmdoaXN0YW4iLCJLYW5kYWhhciIsItin2LHYutiz2KrYp9mGIiwi2qnZhtiv2YfYp9ixIiwzMzEyLDMzCjY3LjI1LDMxLjQ1LCJEaXN0cmljdCIsIk1hcnVmIiwiS2FuZGFoYXIiLCLZhdi52LHZiNmBIiwi2qnZhtiv2YfYp9ixIiwzMzE2LDMzCjY1LjIsMzIuMDQsIkRpc3RyaWN0IiwiR2hvcmFrIiwiS2FuZGFoYXIiLCLYutmI2LHaqSIsItqp2YbYr9mH2KfYsSIsMzMxNSwzMwo2NS41LDMyLjAxLCJEaXN0cmljdCIsIktoYWtyZXoiLCJLYW5kYWhhciIsItiu2Kfaqdix24zYsiIsItqp2YbYr9mH2KfYsSIsMzMwNywzMwo2NS42NCwzMS43NiwiRGlzdHJpY3QiLCJBcmdoYW5kYWIiLCJLYW5kYWhhciIsItin2LHYutmG2K/Yp9ioIiwi2qnZhtiv2YfYp9ixIiwzMzAyLDMzCjY1Ljc4LDMxLjIxLCJEaXN0cmljdCIsIkRhbWFuIiwiS2FuZGFoYXIiLCLYr9in2YXYp9mGIiwi2qnZhtiv2YfYp9ixIiwzMzA1LDMzCjY1LjcxLDMyLjM5LCJEaXN0cmljdCIsIk5lc2giLCJLYW5kYWhhciIsItmG24zYtCIsItqp2YbYr9mH2KfYsSIsMzMxNCwzMwo2Ni4wMSwzMi4wOCwiRGlzdHJpY3QiLCJTaGFoIFdhbGkgS290IiwiS2FuZGFoYXIiLCLYtNin2Ycg2YjZhNuMINqp2YjYqiIsItqp2YbYr9mH2KfYsSIsMzMwNiwzMwo2Ni4zNCwzMi4zOCwiRGlzdHJpY3QiLCJNaXlhbnNoaW4iLCJLYW5kYWhhciIsItmF24zYp9mGINi024zZhiIsItqp2YbYr9mH2KfYsSIsMzMxMywzMwo2NS40NywzMi41OCwiRGlzdHJpY3QiLCJEZWhyYXd1ZCIsIlVydXpnYW4iLCLYr9mH2LHYp9mI2K8iLCLYp9ix2LLar9in2YYiLDIzMDQsMjMKNjUuNDgsMzIuOTIsIkRpc3RyaWN0IiwiU2hhaGlkLWUtSGFzc2FzIiwiVXJ1emdhbiIsIti02YfbjNivINit2LPYp9izIiwi2KfYsdiy2q/Yp9mGIiwyMzAzLDIzCjY1LjkyLDMyLjY4LCJQcm92aW5jaWFsX0NlbnRlciIsIlRpcmluIEtvdCIsIlVydXpnYW4iLCLYqtuM2LHbjNmG2qnZiNiqIiwi2KfYsdiy2q/Yp9mGIiwyMzAxLDIzCjY2LjI2LDMyLjkzLCJEaXN0cmljdCIsIkNob3JhIiwiVXJ1emdhbiIsItqG2YjYsdmHIiwi2KfYsdiy2q/Yp9mGIiwyMzAyLDIzCjY2LjY5LDMzLCJEaXN0cmljdCIsIktoYXMgVXJ1emdhbiIsIlVydXpnYW4iLCLYrtin2LUg2KfYsdiy2q/Yp9mGIiwi2KfYsdiy2q/Yp9mGIiwyMzA1LDIzCjY2LjUyLDMxLjkxLCJEaXN0cmljdCIsIlRhcm5hayBXYSBKYWxkYWsiLCJaYWJ1bCIsItiq2LHZhtqpINmIINis2YTYr9qpIiwi2LLYp9io2YQiLDI0MDQsMjQKNjYuNTEsMzIuMTgsIkRpc3RyaWN0IiwiTWl6YW4iLCJaYWJ1bCIsItmF24zYstin2YYiLCLYstin2KjZhCIsMjQwMywyNAo2Ny4zOSwzMS43MiwiRGlzdHJpY3QiLCJBdGdoYXIiLCJaYWJ1bCIsItin2KrYutixIiwi2LLYp9io2YQiLDI0MDksMjQKNjYuNjksMzIuNjEsIkRpc3RyaWN0IiwiRGF5IGNob3BhbiIsIlphYnVsIiwi2K/Yp9uMINqG2YjZvtin2YYiLCLYstin2KjZhCIsMjQwOCwyNAo2Ni45OSwzMi41MSwiRGlzdHJpY3QiLCJBcmdoYW5kYWIiLCJaYWJ1bCIsItin2LHYutmG2K/Yp9ioIiwi2LLYp9io2YQiLDI0MDIsMjQKNjcsMzIuMjEsIlByb3ZpbmNpYWxfQ2VudGVyIiwiUWFsYXQiLCJaYWJ1bCIsItmC2YTYp9iqIiwi2LLYp9io2YQiLDI0MDEsMjQKNjkuNzgsMzMuMzIsIkRpc3RyaWN0IiwiTWFuZG8gWmF5aSIsIktob3N0Iiwi2YXZhtiv2YjYstin2KbbjCIsItiu2YjYs9iqIiwyNjA1LDI2CjY3LjE4LDMyLjg2LCJEaXN0cmljdCIsIktha2FyIiwiWmFidWwiLCLaqdin2qnakyIsItiy2KfYqNmEIiwyNDA3LDI0CjY3LjM5LDMyLjUsIkRpc3RyaWN0IiwiU2hhaCBKb3kiLCJaYWJ1bCIsIti02KfZhyDYrNmI24wiLCLYstin2KjZhCIsMjQwNiwyNAo2Ny4xNiwzMS45MiwiRGlzdHJpY3QiLCJTaGlua2F5IiwiWmFidWwiLCLYtNuM2YbaqduMIiwi2LLYp9io2YQiLDI0MDUsMjQKNjcuNTcsMzIuMjIsIkRpc3RyaWN0IiwiTmF3IEJhaGFyIiwiWmFidWwiLCLZhtmI2KjZh9in2LEiLCLYstin2KjZhCIsMjQxMSwyNAo2Ny4xMSwzMy41MiwiRGlzdHJpY3QiLCJBanJlc3RhbiIsIkdoYXpuaSIsItin2KzYsdiz2KrYp9mGIiwi2LrYstmG24wiLDExMTYsMTEKNjcuMjQsMzMuMywiRGlzdHJpY3QiLCJNYWxlc3RhbiIsIkdoYXpuaSIsItmF2KfZhNiz2KrYp9mGIiwi2LrYstmG24wiLDExMTcsMTEKNjcuNjUsMzIuNzEsIkRpc3RyaWN0IiwiR2VsYW4iLCJHaGF6bmkiLCLar9uM2YTYp9mGIiwi2LrYstmG24wiLDExMTgsMTEKNjcuNzgsMzIuOTQsIkRpc3RyaWN0IiwiTXVxdXIiLCJHaGF6bmkiLCLZhdmC2LEiLCLYutiy2YbbjCIsMTExNCwxMQo2Ny40NiwzMy4xNCwiRGlzdHJpY3QiLCJKYWdodXJpIiwiR2hhem5pIiwi2KzYp9i62YjYsduMIiwi2LrYstmG24wiLDExMTMsMTEKNjguMDYsMzIuODgsIkRpc3RyaWN0IiwiQWIgQmFuZCIsIkdoYXpuaSIsItii2Kgg2KjZhtivIiwi2LrYstmG24wiLDExMTUsMTEKNjguMzUsMzMuMDYsIkRpc3RyaWN0IiwiR2lybyIsIkdoYXpuaSIsItqv24zYsdmIIiwi2LrYstmG24wiLDExMTEsMTEKNjcuOTksMzMuMjIsIkRpc3RyaWN0IiwiUWFyYWJhZ2giLCJHaGF6bmkiLCLZgtix2Ycg2KjYp9i6Iiwi2LrYstmG24wiLDExMTAsMTEKNjguNDUsMzMuMzEsIkRpc3RyaWN0IiwiQW5kYXIiLCJHaGF6bmkiLCLYp9mG2K/akyIsIti62LLZhtuMIiwxMTA1LDExCjY4LjIxLDMzLjQxLCJEaXN0cmljdCIsIldhZ2hheiIsIkdoYXpuaSIsItmI2KfYuti4Iiwi2LrYstmG24wiLDExMDQsMTEKNjguMywzMy41NiwiRGlzdHJpY3QiLCJXYWxpIE11aGFtbWFkLWUtIFNoYWhpZCIsIkdoYXpuaSIsItmI2YTbjCDZhdit2YXYryDYtNmH24zYryIsIti62LLZhtuMIiwxMTAzLDExCjY4LjA3LDMzLjUzLCJEaXN0cmljdCIsIkphZ2hhdHUiLCJHaGF6bmkiLCLYrNi62KrZiCIsIti62LLZhtuMIiwxMTA5LDExCjY4LjE1LDMzLjcsIkRpc3RyaWN0IiwiUmFzaGlkYW4iLCJHaGF6bmkiLCLYsdi024zYr9in2YYiLCLYutiy2YbbjCIsMTEwOCwxMQo2OC42NSwzMy40NywiRGlzdHJpY3QiLCJEZWggWWFrIiwiR2hhem5pIiwi2K/ZhyDbjNqpIiwi2LrYstmG24wiLDExMDYsMTEKNjguNDQsMzMuNTMsIlByb3ZpbmNpYWxfQ2VudGVyIiwiR2hhem5pIiwiR2hhem5pIiwi2LrYstmG24wiLCLYutiy2YbbjCIsMTEwMSwxMQo2OC42NywzMy42OCwiRGlzdHJpY3QiLCJaYW5hIEtoYW4iLCJHaGF6bmkiLCLYstmG2Ycg2K7Yp9mGIiwi2LrYstmG24wiLDExMDcsMTEKNjguNDIsMzMuNjgsIkRpc3RyaWN0IiwiS2h3YWphIFVtYXJpIiwiR2hhem5pIiwi2K7ZiNin2KzZhyDYudmF2LHbjCIsIti62LLZhtuMIiwxMTAyLDExCjY3Ljc3LDMxLjgyLCJEaXN0cmljdCIsIlNob211bHpheSIsIlphYnVsIiwi2LTZhdmE2LLYp9im24wiLCLYstin2KjZhCIsMjQxMCwyNAo2Ny43NCwzMy44NSwiRGlzdHJpY3QiLCJOYXd1ciIsIkdoYXpuaSIsItmG2KfZiNixIiwi2LrYstmG24wiLDExMTIsMTEKNjcuOSwzMi4zMywiRGlzdHJpY3QiLCJOYXdhIiwiR2hhem5pIiwi2YbYp9mI2YciLCLYutiy2YbbjCIsMTExOSwxMQo2OC4zMSwzMS45MSwiRGlzdHJpY3QiLCJUdXJ3byAoVGFyd2UiLCJQYWt0aWthIiwi2KrYsdmI2YciLCLZvtqp2KrbjNqp2KciLDI1MTksMjUKNjguODMsMzEuOTMsIkRpc3RyaWN0IiwiV29yIE1hbWF5IiwiUGFrdGlrYSIsItmI2pMg2YXZhduMIiwi2b7aqdiq24zaqdinIiwyNTE0LDI1CjY4LjMzLDMyLjI1LCJEaXN0cmljdCIsIldhemEgS2hhaCIsIlBha3Rpa2EiLCLZiNin2LLZhyDYrtmI2KfZhyIsItm+2qnYqtuM2qnYpyIsMjUxMywyNQo2OC4xMywzMi41NiwiRGlzdHJpY3QiLCJEaWxhIiwiUGFrdGlrYSIsItiv24zZhNmHIiwi2b7aqdiq24zaqdinIiwyNTE4LDI1CjY4LjQ4LDMyLjg1LCJEaXN0cmljdCIsIlphcmdodW4gU2hhaHIiLCJQYWt0aWthIiwi2LLYsdi62YjZhiDYtNmH2LEiLCLZvtqp2KrbjNqp2KciLDI1MDUsMjUKNjguNDgsMzIuNjcsIkRpc3RyaWN0IiwiSmFuaSBLaGVsIiwiUGFrdGlrYSIsItis2KfZhtuMINiu24zZhCIsItm+2qnYqtuM2qnYpyIsMjUxMiwyNQo2OC42NiwzMi45MiwiRGlzdHJpY3QiLCJZYWh5YSBLaGVsIiwiUGFrdGlrYSIsItuM2K3bjCDYrtuM2YQiLCLZvtqp2KrbjNqp2KciLDI1MDYsMjUKNjguODEsMzIuODksIkRpc3RyaWN0IiwiT21uYSIsIlBha3Rpa2EiLCLYp9mI2YXZhtmHIiwi2b7aqdiq24zaqdinIiwyNTA3LDI1CjY5LjI4LDMyLjcxLCJEaXN0cmljdCIsIkJlcm1lbCAoQnVybXVsIiwiUGFrdGlrYSIsItio2LHZhdmEIiwi2b7aqdiq24zaqdinIiwyNTE1LDI1CjY5LjQsMzIuOTgsIkRpc3RyaWN0IiwiR2lhbiIsIlBha3Rpa2EiLCLar9uM2KfZhiIsItm+2qnYqtuM2qnYpyIsMjUxNiwyNQo2OS4xOSwzMi45OCwiRGlzdHJpY3QiLCJVcmd1biIsIlBha3Rpa2EiLCLYp9ix2q/ZiNmGIiwi2b7aqdiq24zaqdinIiwyNTEwLDI1CjY5LjMzLDMzLjE2LCJEaXN0cmljdCIsIlppcnVrIiwiUGFrdGlrYSIsItiy24zak9mI2qkiLCLZvtqp2KrbjNqp2KciLDI1MTcsMjUKNjkuMjQsMzMuMjEsIkRpc3RyaWN0IiwiTmlrYSIsIlBha3Rpa2EiLCLZhtuM2qnZhyIsItm+2qnYqtuM2qnYpyIsMjUxMSwyNQo2OS4wNCwzMy4wNiwiRGlzdHJpY3QiLCJTYXIgSGF3emVoKFJhd3plaCIsIlBha3Rpa2EiLCLYs9ix2LHZiNi22Yco2LPYsdit2YjYttmHIiwi2b7aqdiq24zaqdinIiwyNTA0LDI1CjY4LjY1LDMzLjA1LCJEaXN0cmljdCIsIllvc3VmIEtoZWwiLCJQYWt0aWthIiwi24zZiNiz2YEg2K7bjNmEIiwi2b7aqdiq24zaqdinIiwyNTAzLDI1CjY4LjczLDMzLjIsIlByb3ZpbmNpYWxfQ2VudGVyIiwiU2hhcmFuIiwiUGFrdGlrYSIsIti02LHZhiIsItm+2qnYqtuM2qnYpyIsMjUwMSwyNQo2OC44OSwzMy4zLCJEaXN0cmljdCIsIk1hdGEgS2hhbiIsIlBha3Rpa2EiLCLZhdiq2KfYrtin2YYiLCLZvtqp2KrbjNqp2KciLDI1MDIsMjUKNjkuNDksMzMuMTYsIkRpc3RyaWN0IiwiU3BlcmEiLCJLaG9zdCIsItiz2b7bjNix2YciLCLYrtmI2LPYqiIsMjYxMSwyNgo2OS43MywzMy4xOSwiRGlzdHJpY3QiLCJUYW5pIiwiS2hvc3QiLCLYqtmG24wiLCLYrtmI2LPYqiIsMjYwNiwyNgo2OS41MywzMy4zMSwiRGlzdHJpY3QiLCJTaGFtYWwgKFNoYW11bCIsIktob3N0Iiwi2LTZhdmEIiwi2K7ZiNiz2KoiLDI2MTAsMjYKNjkuOTQsMzMuMiwiRGlzdHJpY3QiLCJHdXJidXoiLCJLaG9zdCIsItqv2LHYqNiyIiwi2K7ZiNiz2KoiLDI2MDcsMjYKNzAsMzMuMzQsIlByb3ZpbmNpYWxfQ2VudGVyIiwiS2hvc3QoTWF0dW4iLCJLaG9zdCIsItiu2YjYs9iqICgg2YXYqtmI2YYgIiwi2K7ZiNiz2KoiLDI2MDEsMjYKNjkuNjgsMzMuMzgsIkRpc3RyaWN0IiwiTmFkaXIgU2hhaCBLb3QiLCJLaG9zdCIsItmG2KfYr9ix2LTYp9mHINqp2YjYqiIsItiu2YjYs9iqIiwyNjA0LDI2CjcwLjA4LDMzLjUzLCJEaXN0cmljdCIsIkJhayIsIktob3N0Iiwi2KjYp9qpIiwi2K7ZiNiz2KoiLDI2MTIsMjYKNzAuMDgsMzMuNjQsIkRpc3RyaWN0IiwiSmFqaSBNYXlkYW4iLCJLaG9zdCIsItis2KfYrNuMINmF24zYr9in2YYiLCLYrtmI2LPYqiIsMjYxMywyNgo2OS45MSwzMy41NCwiRGlzdHJpY3QiLCJTYWJyaSIsIktob3N0Iiwi2LXYqNix24wiLCLYrtmI2LPYqiIsMjYwMiwyNgo2OS42NywzMy41NywiRGlzdHJpY3QiLCJNdXNhIEtoZWwiLCJLaG9zdCIsItmF2YjYs9uMINiu24zZhCIsItiu2YjYs9iqIiwyNjAzLDI2CjY5LjY2LDMzLjQ5LCJEaXN0cmljdCIsIlFhbGFuZGFyIiwiS2hvc3QiLCLZgtmE2YbYr9ixIiwi2K7ZiNiz2KoiLDI2MDksMjYKNjkuOTEsMzMuNzksIkRpc3RyaWN0IiwiRGFuZCB3YSBQYXRhbiIsIlBha3R5YSIsItiv2YbYryDZvtiq2KfZhiIsItm+2qnYqtuM2KciLDEyMTEsMTIKNjkuOCwzMy43OCwiRGlzdHJpY3QiLCJDaGFta2FuaSIsIlBha3R5YSIsItqF2YXaqdmG24wiLCLZvtqp2KrbjNinIiwxMjEwLDEyCjY5Ljc5LDMzLjY2LCJEaXN0cmljdCIsIkphbmkgS2hlbCIsIlBha3R5YSIsItis2KfZhtuMINiu24zZhCIsItm+2qnYqtuM2KciLDEyMDksMTIKNjkuNjcsMzMuOTgsIkRpc3RyaWN0IiwiQWxpIEtoZWwgKEphamkiLCJQYWt0eWEiLCLYudmE24wg2K7bjNmEICjYrNin2KzbjCIsItm+2qnYqtuM2KciLDEyMDgsMTIKNjkuNjMsMzMuODIsIkRpc3RyaWN0IiwiTGlqYSBBaG1hZCBLaGVsIiwiUGFrdHlhIiwi2YTYrNmHINin2K3ZhdivINiu24zZhCIsItm+2qnYqtuM2KciLDEyMDcsMTIKNjkuMzQsMzMuNzcsIkRpc3RyaWN0IiwiQWhtYWRhYmEiLCJQYWt0eWEiLCLYp9it2YXYryDYotio2KciLCLZvtqp2KrbjNinIiwxMjAzLDEyCjY5LjQ4LDMzLjcxLCJEaXN0cmljdCIsIlNheWVkIEthcmFtIiwiUGFrdHlhIiwi2LPbjNiv2qnYsdmFIiwi2b7aqdiq24zYpyIsMTIwMiwxMgo2OS40MSwzMy4zNywiRGlzdHJpY3QiLCJaYWRyYW4iLCJQYWt0eWEiLCLYrdmR2K/Ysdin2YYiLCLZvtqp2KrbjNinIiwxMjA2LDEyCjY5LjM3LDMzLjQ1LCJEaXN0cmljdCIsIlNoYXdhayIsIlBha3R5YSIsIti02YjYp9qpIiwi2b7aqdiq24zYpyIsMTIwNSwxMgo2OS4wNCwzMy40MiwiRGlzdHJpY3QiLCJadXJtYXQiLCJQYWt0eWEiLCLYstmP2LHZhdiqIiwi2b7aqdiq24zYpyIsMTIwNCwxMgo2OC44OSwzMy42OSwiRGlzdHJpY3QiLCJLaGFyd2FyIiwiTG9nYXIiLCLYrtix2YjYp9ixIiwi2YTZiNqv2LEiLDUwNiw1CjY5LjI0LDMzLjYsIlByb3ZpbmNpYWxfQ2VudGVyIiwiR2FyZGV6IiwiUGFrdHlhIiwi2q/Ysdiv24zYsiIsItm+2qnYqtuM2KciLDEyMDEsMTIKNjkuNjMsMzQuMTEsIkRpc3RyaWN0IiwiQXpyYSIsIkxvZ2FyIiwi2KfYstix2YciLCLZhNmI2q/YsSIsNTA3LDUKNjkuMjcsMzQsIkRpc3RyaWN0IiwiS2hvc2hpIiwiTG9nYXIiLCLYrtmI2LTbjCIsItmE2Yjar9ixIiw1MDIsNQo2OC45MiwzMy44MiwiRGlzdHJpY3QiLCJDaGFya2giLCJMb2dhciIsItqG2LHYriIsItmE2Yjar9ixIiw1MDUsNQo2OC44OSwzMy45MywiRGlzdHJpY3QiLCJCYXJha2kgQmFyYWsiLCJMb2dhciIsItio2LHaqduMINio2LHaqSIsItmE2Yjar9ixIiw1MDQsNQo2OS4wNSwzMy45NywiUHJvdmluY2lhbF9DZW50ZXIiLCJQdWwtZS0gQWxhbSIsIkxvZ2FyIiwi2b7ZhCDYudmE2YUiLCLZhNmI2q/YsSIsNTAxLDUKNjkuMTgsMzQuMjEsIkRpc3RyaWN0IiwiTW9oYW1tYWQgQWdoYSIsIkxvZ2FyIiwi2YXYrdmF2K/Yoti62YciLCLZhNmI2q/YsSIsNTAzLDUKNjguNzYsMzQuMzIsIkRpc3RyaWN0IiwiTmVya2giLCJXYXJkYWsiLCLZhtix2K4iLCLZiNix2K/aqSIsNDAzLDQKNjguODIsMzQuNDQsIlByb3ZpbmNpYWxfQ2VudGVyIiwiTWF5ZGFuIFNoYWhyIiwiV2FyZGFrIiwi2YXbjNiv2KfZhiDYtNmH2LEiLCLZiNix2K/aqSIsNDAxLDQKNjguNTgsMzQuNTMsIkRpc3RyaWN0IiwiSmFscmV6IiwiV2FyZGFrIiwi2KzZhNix24zYsiIsItmI2LHYr9qpIiw0MDIsNAo2OC42NiwzMy45MiwiRGlzdHJpY3QiLCJTYXlkYWJhZCIsIldhcmRhayIsItiz24zYryDYotio2KfYryIsItmI2LHYr9qpIiw0MDcsNAo2OC4zNiwzMy44NCwiRGlzdHJpY3QiLCJKYWdoYXR1IiwiV2FyZGFrIiwi2KzYutiq2YgiLCLZiNix2K/aqSIsNDA5LDQKNjguNDksMzQuMTEsIkRpc3RyaWN0IiwiQ2hhayIsIldhcmRhayIsItqG2qkiLCLZiNix2K/aqSIsNDA2LDQKNjguMzEsMzQuMjIsIkRpc3RyaWN0IiwiRGF5IE1pcmRhZCIsIldhcmRhayIsItiv2KfbjNmF24zYsdiv2KfYryIsItmI2LHYr9qpIiw0MDUsNAo2OC4xOSwzNC41MywiRGlzdHJpY3QiLCJIZXNhIEF3YWwtZS0gQmVoc3VkIiwiV2FyZGFrIiwi2K3YtdmHINin2YjZhCDYqNmH2LPZiNivIiwi2YjYsdiv2qkiLDQwNCw0CjY3LjY5LDM0LjQsIkRpc3RyaWN0IiwiTWFya2F6LWUtQmVoc3VkIiwiV2FyZGFrIiwi2YXYr9qp2LIg2KjZh9iz2YjYryIsItmI2LHYr9qpIiw0MDgsNAo2OS43NywzNC4yOCwiRGlzdHJpY3QiLCJIZXNhcmFrIiwiTmFuZ2FyaGFyIiwi2K3Ytdin2LHaqSIsItmG2Ybar9ix2YfYp9ixIiw2MTAsNgo2OS45OCwzNC4xOSwiRGlzdHJpY3QiLCJTaGVyemFkIiwiTmFuZ2FyaGFyIiwi2LTbjNix2LLYp9ivIiwi2YbZhtqv2LHZh9in2LEiLDYxMSw2CjcwLjEyLDM0LjIzLCJEaXN0cmljdCIsIktob3dnaWFuaSIsIk5hbmdhcmhhciIsItiu2Yjar9uM2KfZhtuMIiwi2YbZhtqv2LHZh9in2LEiLDYwNCw2CjcwLjQ0LDM0LjA5LCJEaXN0cmljdCIsIkRlaCBCYWxhIiwiTmFuZ2FyaGFyIiwi2K/ZhyDYqNin2YTYpyIsItmG2Ybar9ix2YfYp9ixIiw2MTMsNgo3MC4zNywzNC4yOSwiRGlzdHJpY3QiLCJDaGFwYXJoYXIiLCJOYW5nYXJoYXIiLCLahtm+2LEg2YfYp9ixIiwi2YbZhtqv2LHZh9in2LEiLDYwNSw2CjcwLjI4LDM0LjM5LCJEaXN0cmljdCIsIlN1cmtoIFJvZCIsIk5hbmdhcmhhciIsItiz2LHYriDYsdmI2K8iLCLZhtmG2q/YsdmH2KfYsSIsNjAzLDYKNzAuNDYsMzQuNDIsIlByb3ZpbmNpYWxfQ2VudGVyIiwiSmFsYWxhYmFkIiwiTmFuZ2FyaGFyIiwi2KzZhNin2YQg2KLYqNin2K8iLCLZhtmG2q/YsdmH2KfYsSIsNjAxLDYKNzAuNjYsMzQuMDYsIkRpc3RyaWN0IiwiQWNoaW4iLCJOYW5nYXJoYXIiLCLYp9qG24zZhiIsItmG2Ybar9ix2YfYp9ixIiw2MTcsNgo3MC42LDM0LjE1LCJEaXN0cmljdCIsIktvdCIsIk5hbmdhcmhhciIsItqp2YjZvCIsItmG2Ybar9ix2YfYp9ixIiw2MTQsNgo3MC44MywzNC4wNiwiRGlzdHJpY3QiLCJOYXppYW4iLCJOYW5nYXJoYXIiLCLZhtin2LLbjNin2YYiLCLZhtmG2q/YsdmH2KfYsSIsNjIxLDYKNzAuOCwzNC4yMSwiRGlzdHJpY3QiLCJTaGlud2FyIiwiTmFuZ2FyaGFyIiwi2LTbjNmG2YjYp9ixIiwi2YbZhtqv2LHZh9in2LEiLDYxOCw2CjcwLjU3LDM0LjI3LCJEaXN0cmljdCIsIlJvZGF0IiwiTmFuZ2FyaGFyIiwi2LHZiNiv2KfYqiIsItmG2Ybar9ix2YfYp9ixIiw2MDYsNgo3MC45NiwzNC4xLCJEaXN0cmljdCIsIkR1ciBCYWJhIiwiTmFuZ2FyaGFyIiwi2K/Ysdio2KfYqNinIiwi2YbZhtqv2LHZh9in2LEiLDYyMiw2CjcxLDM0LjIxLCJEaXN0cmljdCIsIk11aG1hbmQgRGFyYSIsIk5hbmdhcmhhciIsItmF2YfZhdmG2K8g2K/YsdmHIiwi2YbZhtqv2LHZh9in2LEiLDYxOSw2CjY2LjI3LDMzLjQsIkRpc3RyaWN0IiwiR2l6YWIiLCJEYXlrdW5kaSIsItqv24zYstin2KgiLCLYr9in24zaqdmG2K/bjCIsMjIwNSwyMgo2Ni4wOCwzMy43NiwiUHJvdmluY2lhbF9DZW50ZXIiLCJOaWxpIiwiRGF5a3VuZGkiLCLZhtuM2YTbjCIsItiv2KfbjNqp2YbYr9uMIiwyMjAxLDIyCjY2Ljk0LDMzLjgzLCJEaXN0cmljdCIsIk1pcmFtb3IiLCJEYXlrdW5kaSIsItmF24zYsdin2YXZiNixIiwi2K/Yp9uM2qnZhtiv24wiLDIyMDksMjIKNjYuNDcsMzMuNzEsIkRpc3RyaWN0IiwiU2hhaHJpc3RhbiIsIkRheWt1bmRpIiwi2LTZh9ix2LPYqtin2YYiLCLYr9in24zaqdmG2K/bjCIsMjIwNiwyMgo2Ni4zLDM0LjA3LCJEaXN0cmljdCIsIkFzaHRhcmxheSIsIkRheWt1bmRpIiwi2KfYtNiq2LHZhNuMIiwi2K/Yp9uM2qnZhtiv24wiLDIyMDIsMjIKNjYuOTcsMzQuMTUsIkRpc3RyaWN0IiwiV2FyYXMiLCJCYW15YW4iLCLZiNix2KsiLCLYqNin2YXbjNin2YYiLDEwMDcsMTAKNjYuOTMsMzQuNDIsIkRpc3RyaWN0IiwiUGFuamFiIiwiQmFteWFuIiwi2b7Zhtis2KfYqCIsItio2KfZhduM2KfZhiIsMTAwNCwxMAo2Ny42OCwzNC43OSwiUHJvdmluY2lhbF9DZW50ZXIiLCJCYW15YW4iLCJCYW15YW4iLCLYqNin2YXbjNin2YYiLCLYqNin2YXbjNin2YYiLDEwMDEsMTAKNjcuNjEsMzUuMTIsIkRpc3RyaWN0IiwiU2F5Z2hhbiIsIkJhbXlhbiIsItiz24zYutin2YYiLCLYqNin2YXbjNin2YYiLDEwMDIsMTAKNjguNjMsMzQuNzcsIkRpc3RyaWN0IiwiU3Vya2hlIFBhcnNhIiwiUGFyd2FuIiwi2LPYsdiuINm+2KfYsdiz2KciLCLZvtix2YjYp9mGIiwzMTAsMwo2OC45NCwzNC41NSwiRGlzdHJpY3QiLCJQYWdobWFuIiwiS2FidWwiLCLZvti62YXYp9mGIiwi2qnYp9io2YQiLDEwNCwxCjY5LjA3LDM0LjM5LCJEaXN0cmljdCIsIkNoYWhhciBBc3lhYiIsIkthYnVsIiwi2obZh9in2LEg2KLYs9uM2KfYqCIsItqp2KfYqNmEIiwxMDUsMQo2OS4yMiwzNC4zNywiRGlzdHJpY3QiLCJNdXNheWkiLCJLYWJ1bCIsItmF2YjYs9mH24wiLCLaqdin2KjZhCIsMTA2LDEKNjkuMTUsMzQuNTIsIkNhcGl0YWwiLCJLYWJ1bCIsIkthYnVsIiwi2qnYp9io2YQiLCLaqdin2KjZhCIsMTAxLDEKNjkuMzQsMzQuNDgsIkRpc3RyaWN0IiwiQmFncmFtaSIsIkthYnVsIiwi2Kjar9ix2KfZhduMIiwi2qnYp9io2YQiLDEwNywxCjY5LjQzLDM0LjM3LCJEaXN0cmljdCIsIktoYWstZS0gSmFiYmFyIiwiS2FidWwiLCLYrtin2qkg2KzYqNin2LEiLCLaqdin2KjZhCIsMTEyLDEKNjkuMzEsMzQuNjcsIkRpc3RyaWN0IiwiRGVoIFNhYnoiLCJLYWJ1bCIsItiv2Ycg2LPYqNiyIiwi2qnYp9io2YQiLDEwMiwxCjY5LjE4LDM0Ljc2LCJEaXN0cmljdCIsIkthbGFrYW4iLCJLYWJ1bCIsItqp2YTaqdin2YYiLCLaqdin2KjZhCIsMTA5LDEKNjkuMTIsMzQuNzMsIkRpc3RyaWN0IiwiTWlyIEJhY2hhIEtvdCIsIkthYnVsIiwi2YXbjNixINio2obZhyDaqdmI2KoiLCLaqdin2KjZhCIsMTEwLDEKNjkuMDQsMzQuNjYsIkRpc3RyaWN0IiwiU2hha2FyZGFyYSIsIkthYnVsIiwi2LTaqdix2K/YsdmHIiwi2qnYp9io2YQiLDEwMywxCjY5LDM0Ljc1LCJEaXN0cmljdCIsIkd1bGRhcmEiLCJLYWJ1bCIsItqv2YTYr9ix2YciLCLaqdin2KjZhCIsMTExLDEKNjkuMDIsMzQuNzksIkRpc3RyaWN0IiwiRmFyemEiLCJLYWJ1bCIsItmB2LHYstmHIiwi2qnYp9io2YQiLDExNSwxCjY5LjAyLDM0Ljg1LCJEaXN0cmljdCIsIkVzdGFsZWYiLCJLYWJ1bCIsItin2LPYqtin2YTZgSIsItqp2KfYqNmEIiwxMTQsMQo2OS4xOCwzNC44NSwiRGlzdHJpY3QiLCJRYXJhYmFnaCIsIkthYnVsIiwi2YLYsdmHINio2KfYuiIsItqp2KfYqNmEIiwxMDgsMQo2OS4xNCwzNC45OSwiUHJvdmluY2lhbF9DZW50ZXIiLCJDaGFoYXJpa2FyIiwiUGFyd2FuIiwi2obYp9ix24zaqdin2LEiLCLZvtix2YjYp9mGIiwzMDEsMwo2OS40NywzNC43NiwiRGlzdHJpY3QiLCJLb2gtZS0gU2FmaSIsIlBhcndhbiIsItqp2YjZhyDYtdin2YHbjCIsItm+2LHZiNin2YYiLDMwOCwzCjY5LjMzLDM0LjkyLCJEaXN0cmljdCIsIkJhZ3JhbSIsIlBhcndhbiIsItio2q/Ysdin2YUiLCLZvtix2YjYp9mGIiwzMDQsMwo2OS4yNywzNS4wNSwiRGlzdHJpY3QiLCJTYXlkIEtoZWwiLCJQYXJ3YW4iLCLYs9uM2K/YrtuM2YQiLCLZvtix2YjYp9mGIiwzMDUsMwo2OS4xOSwzNS4xMiwiRGlzdHJpY3QiLCJKYWJhbHVzc2FyYWoiLCJQYXJ3YW4iLCLYrNio2YQg2KfZhNiz2LHYp9isIiwi2b7YsdmI2KfZhiIsMzAyLDMKNjkuNywzNC41MywiRGlzdHJpY3QiLCJTdXJvYmkiLCJLYWJ1bCIsItiz2LHZiNio24wiLCLaqdin2KjZhCIsMTEzLDEKNjkuMzQsMzUuMTQsIkRpc3RyaWN0IiwiSGVzYSBBd2FsLWUtIEtvaGVzdGFuIiwiS2FwaXNhIiwi2K3YtdmHINin2YjZhCDaqdmI2YfYs9iq2KfZhiIsItqp2KfZvtuM2LPYpyIsMjA3LDIKNzEuMDIsMzQuMzMsIkRpc3RyaWN0IiwiTGFsIFB1ciIsIk5hbmdhcmhhciIsItmE2LnZhCDZvtmI2LEiLCLZhtmG2q/YsdmH2KfYsSIsNjIwLDYKNzAuODcsMzQuNDUsIkRpc3RyaWN0IiwiR29zaHRhIiwiTmFuZ2FyaGFyIiwi2q/ZiNi02KrZhyIsItmG2Ybar9ix2YfYp9ixIiw2MTYsNgo3MC41MSwzNC40NCwiRGlzdHJpY3QiLCJCZWhzdWQiLCJOYW5nYXJoYXIiLCLYqNmH2LPZiNivIiwi2YbZhtqv2LHZh9in2LEiLDYwMiw2CjcwLjY2LDM0LjQ0LCJEaXN0cmljdCIsIkthbWEiLCJOYW5nYXJoYXIiLCLaqdin2YXZhyIsItmG2Ybar9ix2YfYp9ixIiw2MDcsNgo3MC42MSwzNC41NiwiRGlzdHJpY3QiLCJLdXogS3VuYXIiLCJOYW5nYXJoYXIiLCLaqdmI2LIg2qnZhtixIiwi2YbZhtqv2LHZh9in2LEiLDYwOCw2CjcwLjU5LDM0LjcxLCJEaXN0cmljdCIsIkRhcmEtZS1OdXIiLCJOYW5nYXJoYXIiLCLYr9ix2Ycg2YbZiNixIiwi2YbZhtqv2LHZh9in2LEiLDYwOSw2CjY5LjMzLDM1LjA3LCJEaXN0cmljdCIsIkhpc2EgRHV3dW0tZS0gS29oZXN0YW4iLCJLYXBpc2EiLCLYrdi12Ycg2K/ZiNmFINqp2YjZh9iz2KrYp9mGIiwi2qnYp9m+24zYs9inIiwyMDQsMgo2OS40MiwzNSwiUHJvdmluY2lhbF9DZW50ZXIiLCJNYWhtdWQtZS0gUmFxaSIsIkthcGlzYSIsItmF2K3ZhdmI2K8g2LHYp9mC24wiLCLaqdin2b7bjNiz2KciLDIwMSwyCjY5LjQ3LDM1LjExLCJEaXN0cmljdCIsIktvaCBCYW5kIiwiS2FwaXNhIiwi2qnZiNmHINio2YbYryIsItqp2KfZvtuM2LPYpyIsMjAzLDIKNjkuNjUsMzUuMDUsIkRpc3RyaWN0IiwiTmVqcmFiIiwiS2FwaXNhIiwi2YbYrNix2KfYqCIsItqp2KfZvtuM2LPYpyIsMjAyLDIKNjkuNjgsMzQuNzksIkRpc3RyaWN0IiwiVGFnYWIiLCJLYXBpc2EiLCLYqtqv2KfYqCIsItqp2KfZvtuM2LPYpyIsMjA1LDIKNjkuODEsMzQuOTMsIkRpc3RyaWN0IiwiQWxhc2F5IiwiS2FwaXNhIiwi2KfZhNmHINiz2KfbjCIsItqp2KfZvtuM2LPYpyIsMjA2LDIKNzAsMzUuMDQsIkRpc3RyaWN0IiwiRGF1bGF0IFNoYWgiLCJMYWdobWFuIiwi2K/ZiNmE2Kog2LTYp9mHIiwi2YTYutmF2KfZhiIsNzA1LDcKNzAuMDgsMzQuODMsIkRpc3RyaWN0IiwiQWxpc2hhbmciLCJMYWdobWFuIiwi2YLYsdi62Ycg2KbbjCIsItmE2LrZhdin2YYiLDcwMiw3CjcwLjE3LDM0LjUzLCJEaXN0cmljdCIsIlFhcmdoYXlpIiwiTGFnaG1hbiIsIti52YTbjNmG2q/Yp9ixIiwi2YTYutmF2KfZhiIsNzAzLDcKNzAuMDksMzQuNjUsIlByb3ZpbmNpYWxfQ2VudGVyIiwiTWVodGFybGFtIiwiTGFnaG1hbiIsItmF2YfYqtixINmE2KfZhSIsItmE2LrZhdin2YYiLDcwMSw3CjcwLjQxLDM0LjgyLCJEaXN0cmljdCIsIkFsaW5nYXIiLCJMYWdobWFuIiwi2LnZhNuM2LTZhtqvIiwi2YTYutmF2KfZhiIsNzA0LDcKNjkuNDEsMzUuMjIsIkRpc3RyaWN0IiwiT25hYmEoQW5hd2EiLCJQYW5qc2hlciIsIti52YbYp9io2YciLCLZvtmG2KzYtNuM2LEiLDgwNiw4CjY5LjQ4LDM1LjI3LCJEaXN0cmljdCIsIlJ1a2hhIiwiUGFuanNoZXIiLCLYsdiu2YciLCLZvtmG2KzYtNuM2LEiLDgwMyw4CjY5Ljc3LDM1LjI0LCJEaXN0cmljdCIsIkRhcmEiLCJQYW5qc2hlciIsItiv2LHZhyIsItm+2YbYrNi024zYsSIsODA0LDgKNzAuMzgsMzUuMjIsIkRpc3RyaWN0IiwiRHUgQWIiLCJOdXJpc3RhbiIsItiv2Ygg2KLYqCIsItmG2YjYsdiz2KrYp9mGIiwxNDAzLDE0CjcwLjczLDM0LjY5LCJEaXN0cmljdCIsIk51cmdhbCIsIkt1bmFyIiwi2YbZiNix2q/ZhCIsItqp2YbYsSDZh9inIiwxMzE0LDEzCjcwLjk0LDM0LjYxLCJEaXN0cmljdCIsIktoYXMgS3VuYXIiLCJLdW5hciIsItiu2KfYtSDaqdmG2LEiLCLaqdmG2LEg2YfYpyIsMTMwOSwxMwo3MS4xMiwzNC43NSwiRGlzdHJpY3QiLCJTYXJrYW5pIiwiS3VuYXIiLCLYs9ix2qnYp9mG24wiLCLaqdmG2LEg2YfYpyIsMTMwNCwxMwo3MC44NywzNC43NCwiRGlzdHJpY3QiLCJDaGF3a2F5IiwiS3VuYXIiLCLahtmI2qnbjCIsItqp2YbYsSDZh9inIiwxMzA4LDEzCjcxLDM0LjgsIkRpc3RyaWN0IiwiTmFyYW5nIiwiS3VuYXIiLCLZhtix2YbaryIsItqp2YbYsSDZh9inIiwxMzAzLDEzCjcxLjIzLDM0Ljg2LCJEaXN0cmljdCIsIk1hcmF3YXJhIiwiS3VuYXIiLCLZhdix2Ycg2YjYsdmHIiwi2qnZhtixINmH2KciLDEzMDUsMTMKNzAuNywzNC45NiwiRGlzdHJpY3QiLCJDaGFwYSBEYXJhIiwiS3VuYXIiLCLahtm+2Ycg2K/YsdmHIiwi2qnZhtixINmH2KciLDEzMTMsMTMKNzEuMTMsMzQuODcsIlByb3ZpbmNpYWxfQ2VudGVyIiwiQXNhZCBBYmFkIiwiS3VuYXIiLCLYp9iz2K8g2KLYqNin2K8iLCLaqdmG2LEg2YfYpyIsMTMwMSwxMwo3MC45MiwzNC45NSwiRGlzdHJpY3QiLCJEYXJhLWUtUGVjaCIsIkt1bmFyIiwi2K/YsdmHINm+24zahiIsItqp2YbYsSDZh9inIiwxMzA3LDEzCjcxLjA4LDM1LCJEaXN0cmljdCIsIldhdGEgUHVyIiwiS3VuYXIiLCLZiNiq2Ycg2b7ZiNixIiwi2qnZhtixINmH2KciLDEzMDIsMTMKNzEuMjMsMzUuMDQsIkRpc3RyaWN0IiwiU2hpZ2FsIHdhIHNoZWwgdGFuIiwiS3VuYXIiLCLYtNuM2q/ZhCDZiNi02YTYqtmGIiwi2qnZhtixINmH2KciLDEzMDYsMTMKNzEuNDQsMzUsIkRpc3RyaWN0IiwiRGFuZ2FtIiwiS3VuYXIiLCLYr9in2Ybar9in2YUiLCLaqdmG2LEg2YfYpyIsMTMxMCwxMwo3MS41NCwzNS4yNywiRGlzdHJpY3QiLCJOYXJpIiwiS3VuYXIiLCLZhtin2pPbjCIsItqp2YbYsSDZh9inIiwxMzE1LDEzCjcxLjM4LDM1LjA3LCJEaXN0cmljdCIsIkJhciBLdW5hciIsIkt1bmFyIiwi2KjYsdqp2YbYsSIsItqp2YbYsSDZh9inIiwxMzExLDEzCjcxLjMzLDM1LjIzLCJEaXN0cmljdCIsIkdoYXppIEFiYWQiLCJLdW5hciIsIti62KfYstuMINii2KjYp9ivIiwi2qnZhtixINmH2KciLDEzMTIsMTMKNzAuOCwzNS4xOCwiRGlzdHJpY3QiLCJXYW1hIiwiTnVyaXN0YW4iLCLZiNin2YXYpyIsItmG2YjYsdiz2KrYp9mGIiwxNDA1LDE0CjcwLjQ3LDM1LjA3LCJEaXN0cmljdCIsIk51cmdhcmFtIiwiTnVyaXN0YW4iLCLZhtmI2LHar9ix2KfZhSIsItmG2YjYsdiz2KrYp9mGIiwxNDA0LDE0CjcxLjAzLDM1LjIzLCJEaXN0cmljdCIsIldheWdhbCIsIk51cmlzdGFuIiwi2YjYp9uM2q/ZhCIsItmG2YjYsdiz2KrYp9mGIiwxNDA2LDE0CjcxLjMxLDM1LjQ4LCJEaXN0cmljdCIsIkthbWRlc2giLCJOdXJpc3RhbiIsItqp2KfZhdiv24zYtCIsItmG2YjYsdiz2KrYp9mGIiwxNDA3LDE0CjcwLjI4LDM0LjA5LCJEaXN0cmljdCIsIlBhY2hpZXIgQWdhbSIsIk5hbmdhcmhhciIsItm+2obbjNix2Kfar9in2YUiLCLZhtmG2q/YsdmH2KfYsSIsNjEyLDYKNjIuOTEsMzEuNzQsIkRpc3RyaWN0IiwiS2hhc2ggUm9kIiwiTmltcm96Iiwi2K7Yp9i0INix2YjYryIsItmG24zZhdix2YjYsiIsMzQwNSwzNAo2Mi40NCwzMS4zMiwiRGlzdHJpY3QiLCJDaGFraGFuc3VyIiwiTmltcm96Iiwi2obYrtin2YbYs9mI2LEiLCLZhtuM2YXYsdmI2LIiLDM0MDQsMzQKNjIuMDgsMzAuMTYsIkRpc3RyaWN0IiwiQ2hhaGFyIEJ1cmphayIsIk5pbXJveiIsItqG2KfYsdio2LHYrNqpIiwi2YbbjNmF2LHZiNiyIiwzNDAzLDM0CjYzLjE3LDI5Ljg4LCJEaXN0cmljdCIsIkRlaCBzaHUiLCJIaWxtYW5kIiwi2K/bjNi02YgiLCLZh9mE2YXZhtivIiwzMjEzLDMyCjYzLjQ4LDMwLjgzLCJEaXN0cmljdCIsIlJlZyhraGFuIE5lc2hpbiIsIkhpbG1hbmQiLCLYsduM2q8gKCDYrtin2YYg2LTbjNmGICIsItmH2YTZhdmG2K8iLDMyMTIsMzIKNjQuMTcsMzAuMzEsIkRpc3RyaWN0IiwiR2FybSBTZXIiLCJIaWxtYW5kIiwi2q/YsdmFINiz24zYsSIsItmH2YTZhdmG2K8iLDMyMDksMzIKNjMuNzksMzEuNTgsIkRpc3RyaWN0IiwiTmFkLWUtQWxpIiwiSGlsbWFuZCIsItmG2KfYr9i52YTbjCIsItmH2YTZhdmG2K8iLDMyMDMsMzIKNjQuMjEsMzEuMzMsIkRpc3RyaWN0IiwiTmF3YS1lLUJhcmFrIFphaXkiLCJIaWxtYW5kIiwi2YbYp9mI2Ycg2KjYp9ix2qnYstin2KbbjCIsItmH2YTZhdmG2K8iLDMyMDQsMzIKNjQuNTQsMzEuNDYsIlByb3ZpbmNpYWxfQ2VudGVyIiwiTGFzaGthciBHYWgiLCJIaWxtYW5kIiwi2YTYtNqp2LHar9in2YciLCLZh9mE2YXZhtivIiwzMjAxLDMyCjYzLjk1LDMyLjEsIkRpc3RyaWN0IiwiV2EgU2hlciIsIkhpbG1hbmQiLCLZiNin2LTbjNixIiwi2YfZhNmF2YbYryIsMzIwOCwzMgo2NC42MSwzMS44MywiRGlzdHJpY3QiLCJOYWhyLWUtU2FyYWoiLCJIaWxtYW5kIiwi2YbZh9ixINiz2LHYp9isIiwi2YfZhNmF2YbYryIsMzIwMiwzMgo2NC40NywzMi40NywiRGlzdHJpY3QiLCJOYXcgWmFkIiwiSGlsbWFuZCIsItmG2YjYstin2K8iLCLZh9mE2YXZhtivIiwzMjA3LDMyCjY0Ljc0LDMyLjQ3LCJEaXN0cmljdCIsIk11c2EgUWFsZWgiLCJIaWxtYW5kIiwi2YXZiNiz24wg2YLZhNi52YciLCLZh9mE2YXZhtivIiwzMjA2LDMyCjY1LDMyLjEyLCJEaXN0cmljdCIsIlNhbmdpbiIsIkhpbG1hbmQiLCLYs9mG2q/bjNmGIiwi2YfZhNmF2YbYryIsMzIwNSwzMgo2NS4wNCwzMi40OCwiRGlzdHJpY3QiLCJLYWpha2kiLCJIaWxtYW5kIiwi2qnYrNqp24wiLCLZh9mE2YXZhtivIiwzMjEwLDMyCjY5LjUxLDM1LjM3LCJQcm92aW5jaWFsX0NlbnRlciIsIkJhemFyYWsiLCJQYW5qc2hlciIsItio2KfYstin2LHaqSIsItm+2YbYrNi024zYsSIsODAxLDgKNjkuMywzNS4yOSwiRGlzdHJpY3QiLCJTaHV0dWwiLCJQYW5qc2hlciIsIti02KrZhCIsItm+2YbYrNi024zYsSIsODAyLDgKNjYuMDQsMzAuMTcsIkRpc3RyaWN0IiwiU2hvciBBYmFrIiwiS2FuZGFoYXIiLCLYtNmI2LEg2KLYqNqpIiwi2qnZhtiv2YfYp9ixIiwzMzEwLDMzCjY4Ljk1LDMyLjM5LCJEaXN0cmljdCIsIkdvbWFsIiwiUGFrdGlrYSIsItqv2YjZhdmEIiwi2b7aqdiq24zaqdinIiwyNTA4LDI1CjYxLjMzLDMxLjcyLCJEaXN0cmljdCIsIkxhc2gtZS1KdXdheW4iLCJGYXJhaCIsItmE2KfYtCDYrNmI24zZhiIsItmB2LHYp9mHIiwzMTA4LDMxCjcwLjE5LDMzLjM4LCJEaXN0cmljdCIsIlRlcmUgWmF5aSIsIktob3N0Iiwi2KrYsduMINiy2KfYptuMIiwi2K7ZiNiz2KoiLDI2MDgsMjYKNzEuMjUsMzUuNzcsIkRpc3RyaWN0IiwiQmFyZy1lLSBNYXRhbCIsIk51cmlzdGFuIiwi2KjYsdqpINmF2KrYp9mEIiwi2YbZiNix2LPYqtin2YYiLDE0MDgsMTQKNzAuOCwzNS40MSwiUHJvdmluY2lhbF9DZW50ZXIiLCJQb3J1bnMiLCJOdXJpc3RhbiIsItm+2LHZiNmG2LMiLCLZhtmI2LHYs9iq2KfZhiIsMTQwMSwxNAo3MC4yNSwzNS40LCJEaXN0cmljdCIsIk1hbmRvbCIsIk51cmlzdGFuIiwi2YXZhtiv2YjZhCIsItmG2YjYsdiz2KrYp9mGIiwxNDAyLDE0CjcwLjA0LDM1LjY1LCJEaXN0cmljdCIsIlBhcmlhbiIsIlBhbmpzaGVyIiwi2b7YsduM2KfZhiIsItm+2YbYrNi024zYsSIsODA3LDgKNjkuNzgsMzUuNDMsIkRpc3RyaWN0IiwiS2hlbmogKEhlcy1lLSBBd2FsIiwiUGFuanNoZXIiLCLYrdi12Ycg2KfZiNmEICjYrtmG2KwiLCLZvtmG2KzYtNuM2LEiLDgwNSw4CjY5LjE1LDM1LjI4LCJEaXN0cmljdCIsIlNhbGFuZyIsIlBhcndhbiIsItiz2KfZhNmG2q8iLCLZvtix2YjYp9mGIiwzMDYsMwo2OC45NywzNS4xMiwiRGlzdHJpY3QiLCJTaGlud2FyaSIsIlBhcndhbiIsIti024zZhtmI2KfYsduMIiwi2b7YsdmI2KfZhiIsMzAzLDMKNjguNzksMzUuMDEsIkRpc3RyaWN0IiwiU3lhaCBHZXJkIiwiUGFyd2FuIiwiKCDYs9uM2KfZhyDar9ix2K8gKNi62YjYsdio2YbYryIsItm+2LHZiNin2YYiLDMwNywzCjY4LjQyLDM0Ljk1LCJEaXN0cmljdCIsIlNoZWtoICBBbGkiLCJQYXJ3YW4iLCLYtNuM2K4g2LnZhNuMIiwi2b7YsdmI2KfZhiIsMzA5LDMKNjguMDQsMzQuOTIsIkRpc3RyaWN0IiwiU2hpYmFyIiwiQmFteWFuIiwi2LTbjNio2LEiLCLYqNin2YXbjNin2YYiLDEwMDUsMTAKNjcuNzgsMzUuMzYsIkRpc3RyaWN0IiwiS2FobWFyZCIsIkJhbXlhbiIsItqp2YfZhdix2K8iLCLYqNin2YXbjNin2YYiLDEwMDYsMTAKNjYuOTUsMzQuOTgsIkRpc3RyaWN0IiwiWWFrYXdsYW5nIiwiQmFteWFuIiwi24zaqdin2YjZhNmG2q8iLCLYqNin2YXbjNin2YYiLDEwMDMsMTAKNjYuMjcsMzQuNiwiRGlzdHJpY3QiLCJMYWwgV2EgU2FyamFuZ2FsIiwiR2hvciIsItmE2LnZhCDZiCDYs9ix2KzZhtqv2YQiLCLYutmI2LEiLDIxMDgsMjEKNjUuOTEsMzQuMTksIkRpc3RyaWN0IiwiU2FuZy1lLVRha2h0IiwiRGF5a3VuZGkiLCLYs9mG2q8g2KrYrtiqIiwi2K/Yp9uM2qnZhtiv24wiLDIyMDcsMjIKNjUuNzUsMzMuOSwiRGlzdHJpY3QiLCJLaGFkaXIiLCJEYXlrdW5kaSIsItiu2K/bjNixIiwi2K/Yp9uM2qnZhtiv24wiLDIyMDMsMjIKNjUuNzIsMzMuNTQsIkRpc3RyaWN0Iiwia2l0aSIsIkRheWt1bmRpIiwi2qnbjNiq24wiLCLYr9in24zaqdmG2K/bjCIsMjIwNCwyMgo2NS42MiwzMy4yMSwiRGlzdHJpY3QiLCJLYWpyYW4iLCJEYXlrdW5kaSIsItqp2KzYsdin2YYiLCLYr9in24zaqdmG2K/bjCIsMjIwOCwyMgo2NS4wNCwzMy4wMSwiRGlzdHJpY3QiLCJCYWdocmFuIiwiSGlsbWFuZCIsItio2KfYutix2KfZhiIsItmH2YTZhdmG2K8iLDMyMTEsMzIKNjIuNzUsMzIuNzcsIkRpc3RyaWN0IiwiQmFsYSBCdWx1ayIsIkZhcmFoIiwi2KjYp9mE2Kcg2KjZhNmI2qkiLCLZgdix2KfZhyIsMzEwMywzMQo2My41OSwzMi42NCwiRGlzdHJpY3QiLCJHdWxlc3RhbiIsIkZhcmFoIiwi2q/ZhNiz2KrYp9mGIiwi2YHYsdin2YciLDMxMDksMzEKNjIuMDcsMzIuNzYsIkRpc3RyaWN0IiwiS2hhay1lLVNhZmVkIiwiRmFyYWgiLCLYrtin2qkg2LPZgduM2K8iLCLZgdix2KfZhyIsMzEwNCwzMQo2MS4yNSwzMy4wMSwiRGlzdHJpY3QiLCJBbmFyIERhcmEiLCJGYXJhaCIsItin2YbYp9ixINiv2LHZhyIsItmB2LHYp9mHIiwzMTExLDMxCjY5LjA1LDMyLjc1LCJEaXN0cmljdCIsIlNhcm9iaSIsIlBha3Rpa2EiLCLYs9ix2YjYqNuMIiwi2b7aqdiq24zaqdinIiwyNTA5LDI1CjcwLjc2LDM0LjI5LCJEaXN0cmljdCIsIkJhdGkgS290IiwiTmFuZ2FyaGFyIiwi2KjZvNuMINqp2YjZvCIsItmG2Ybar9ix2YfYp9ixIiw2MTUsNgo2MS41NywzNS4xMywiRGlzdHJpY3QiLCJHdWxyYW4iLCJIaXJhdCIsItqv2YTYsdin2YYiLCLZh9ix2KfYqiIsMzAwOCwzMAo2MS4xNywzNC42MSwiRGlzdHJpY3QiLCJLb2hzYW4iLCJIaXJhdCIsItqp2YjZh9iz2KfZhiIsItmH2LHYp9iqIiwzMDE0LDMwCjYyLjI0LDM1LCJEaXN0cmljdCIsIkt1c2hrIiwiSGlyYXQiLCLaqdi02qkiLCLZh9ix2KfYqiIsMzAwMywzMAo2Mi42MywzNC44OSwiRGlzdHJpY3QiLCJLdXNoay1lLUtvaG5hIiwiSGlyYXQiLCLaqdi02qkg2qnZh9mG2YciLCLZh9ix2KfYqiIsMzAxMywzMAo2MS4xNSwzNC4xOCwiRGlzdHJpY3QiLCJHaG9yeWFuIiwiSGlyYXQiLCLYutmI2LHbjNin2YYiLCLZh9ix2KfYqiIsMzAwOSwzMAo2MS44MiwzNC4zNywiRGlzdHJpY3QiLCJaaW5kYSBKYW4iLCJIaXJhdCIsItiy2YbYr9mHINis2KfZhiIsItmH2LHYp9iqIiwzMDA0LDMwCjYyLDMzLjcxLCJEaXN0cmljdCIsIkFkcmFza2FuIiwiSGlyYXQiLCLYp9iv2LHYs9qp2YYiLCLZh9ix2KfYqiIsMzAxMCwzMAo2Mi43NSwzNC4xOCwiRGlzdHJpY3QiLCJQYXNodHVuICBaYXJnaHVuIiwiSGlyYXQiLCLZvti02KrZiNmGINiy2LHYutmI2YYiLCLZh9ix2KfYqiIsMzAwNiwzMAo2My4yOCwzMy44NCwiRGlzdHJpY3QiLCJGYXJzaSIsIkhpcmF0Iiwi2YHYsdiz24wiLCLZh9ix2KfYqiIsMzAxMSwzMAo2My4yNSwzNC4zNiwiRGlzdHJpY3QiLCJPYmUiLCJIaXJhdCIsItin2YjYqNmHIiwi2YfYsdin2KoiLDMwMTIsMzAKNjMuOTYsMzQuNDUsIkRpc3RyaWN0IiwiQ2hpc2h0LWUtU2hhcmlmIiwiSGlyYXQiLCLahti02Kog2LTYsduM2YEiLCLZh9ix2KfYqiIsMzAxNiwzMAo2Mi42NiwzNC41LCJEaXN0cmljdCIsIkthcnVraCIsIkhpcmF0Iiwi2qnYsdmP2K4iLCLZh9ix2KfYqiIsMzAwNywzMAo2Mi4xOCwzNC4zNywiUHJvdmluY2lhbF9DZW50ZXIiLCJIaXJhdCIsIkhpcmF0Iiwi2YfYsdin2KoiLCLZh9ix2KfYqiIsMzAwMSwzMAo2Mi4xOCwzNC40NywiRGlzdHJpY3QiLCJJbmppbCIsIkhpcmF0Iiwi2KfZhtis24zZhCIsItmH2LHYp9iqIiwzMDAyLDMwCjYyLjA0LDM0LjA0LCJEaXN0cmljdCIsIkd1emFyYSIsIkhpcmF0Iiwi2q/YsNix2YciLCLZh9ix2KfYqiIsMzAwNSwzMAo2My4xMSwzNS4xNywiRGlzdHJpY3QiLCJNdXF1ciIsIkJhZGdoaXMiLCLZhdmC2LEiLCLYqNin2K/YutuM2LMiLDI5MDIsMjkKNjIuOSwzNC45MywiRGlzdHJpY3QiLCJBYiBLYW1hcmkiLCJCYWRnaGlzIiwi2KLYqCDaqdmF2LHbjCIsItio2KfYr9i624zYsyIsMjkwMywyOQo2My41OCwzNC44MiwiRGlzdHJpY3QiLCJRYWRpcyIsIkJhZGdoaXMiLCLZgtin2K/bjNizIiwi2KjYp9iv2LrbjNizIiwyOTA0LDI5CjY0LjM0LDM1LCJEaXN0cmljdCIsIkphd2FuZCIsIkJhZGdoaXMiLCLYrNmI2YbYryIsItio2KfYr9i624zYsyIsMjkwNSwyOQo2My40NywzNS40NywiRGlzdHJpY3QiLCJCYWxhIE11cmdoYWIiLCJCYWRnaGlzIiwi2YXYsdi62KfYqCIsItio2KfYr9i624zYsyIsMjkwNiwyOQo2My43OCwzNS43MiwiRGlzdHJpY3QiLCJHaG9ybWFjaCIsIkJhZGdoaXMiLCLYutmI2LHZhdin2oYiLCLYqNin2K/YutuM2LMiLDI5MDcsMjkKNjQuMjgsMzUuOTYsIkRpc3RyaWN0IiwiQWxtYXIiLCJGYXJ5YWIiLCLYp9mE2YXYp9ixIiwi2YHYp9ix24zYp9ioIiwyODA1LDI4CjY0LjkyLDM1LjMzLCJEaXN0cmljdCIsIktvaGlzdGFuIiwiRmFyeWFiIiwi2qnZiNmH2LPYqtin2YYiLCLZgdin2LHbjNin2KgiLDI4MDcsMjgKNjUuMjgsMzUuNjMsIkRpc3RyaWN0IiwiR2Fyeml3YW4iLCJGYXJ5YWIiLCLar9ix2LLbjNmI2KfZhiIsItmB2KfYsduM2KfYqCIsMjgwOCwyOAo2NS40NSwzNS44OCwiRGlzdHJpY3QiLCJCaWwgQ2hlcmFnaCIsIkZhcnlhYiIsItio2YTahtix2KfYuiIsItmB2KfYsduM2KfYqCIsMjgwOSwyOAo2NC43OCwzNS44MSwiRGlzdHJpY3QiLCJQYXNodHVuIGtvdCIsIkZhcnlhYiIsItm+2LTYqtmI2YYg2qnZiNiqIiwi2YHYp9ix24zYp9ioIiwyODAzLDI4CjY0Ljc5LDM1Ljk0LCJQcm92aW5jaWFsX0NlbnRlciIsIk1heW1hbmEiLCJGYXJ5YWIiLCLZhduM2YXZhtmHIiwi2YHYp9ix24zYp9ioIiwyODAxLDI4CjY0Ljk3LDM2LjA2LCJEaXN0cmljdCIsIktod2FqYSBTYWJ6cG9zaCIsIkZhcnlhYiIsItiu2YjYp9is2Ycg2LPYqNiy2b7ZiNi0Iiwi2YHYp9ix24zYp9ioIiwyODAyLDI4CjY0LjgyLDM2LjI1LCJEaXN0cmljdCIsIlNoaXJpbiBUYWdhYiIsIkZhcnlhYiIsIti024zYsduM2YYg2Krar9in2KgiLCLZgdin2LHbjNin2KgiLDI4MDQsMjgKNjUuMDYsMzYuNTQsIkRpc3RyaWN0IiwiRGF3bGF0IEFiYWQiLCJGYXJ5YWIiLCLYr9mI2YTYqiDYotio2KfYryIsItmB2KfYsduM2KfYqCIsMjgxMCwyOAo2NC45NiwzNi43OSwiRGlzdHJpY3QiLCJRYXJhbSBRb2wiLCJGYXJ5YWIiLCLZgtix2YUg2YLZhCIsItmB2KfYsduM2KfYqCIsMjgxMiwyOAo2NC45NCwzNy4wNCwiRGlzdHJpY3QiLCJRb3JnaGFuIiwiRmFyeWFiIiwi2YLYsdi62KfZhiIsItmB2KfYsduM2KfYqCIsMjgxNCwyOAo2NS4yMywzNy4xMSwiRGlzdHJpY3QiLCJLaGFuLWUtQ2hhaGFyIEJhZ2giLCJGYXJ5YWIiLCLYrtin2YYg2obYp9ix2KjYp9i6Iiwi2YHYp9ix24zYp9ioIiwyODEzLDI4CjY1LjM3LDM2LCJEaXN0cmljdCIsIkRhcnogQWIiLCJKYXd6amFuIiwi2K/YsdiyINii2KgiLCLYrNmI2LLYrNin2YYiLDI3MTEsMjcKNjUuMzYsMzYuMjEsIkRpc3RyaWN0IiwiUXVzaCBUZXBhIiwiSmF3emphbiIsItmC2YjYtCDYqtuM2b7ZhyIsItis2YjYstis2KfZhiIsMjcwNCwyNwo2NS43NSwzNi41OSwiUHJvdmluY2lhbF9DZW50ZXIiLCJTaGliZXJnaGFuIiwiSmF3emphbiIsIti02KjYsdi62KfZhiIsItis2YjYstis2KfZhiIsMjcwMSwyNwo2NS4yOSwzNi44NSwiRGlzdHJpY3QiLCJBbmRraG95IiwiRmFyeWFiIiwi2KfZhtiv2K7ZiNuMIiwi2YHYp9ix24zYp9ioIiwyODExLDI4CjY1LjYxLDM2Ljk1LCJEaXN0cmljdCIsIktod2FqYSBEdSBLb2giLCJKYXd6amFuIiwi2K7ZiNin2KzZhyDYr9mI2qnZiNmHIiwi2KzZiNiy2KzYp9mGIiwyNzAzLDI3CjY2LjEzLDM2Ljc4LCJEaXN0cmljdCIsIktoYW5hcWEiLCJKYXd6amFuIiwi2K7Yp9mG2YLYpyIsItis2YjYstis2KfZhiIsMjcwNiwyNwo2Ni4zMywzNi43LCJEaXN0cmljdCIsIkZheXogQWJhZCIsIkphd3pqYW4iLCLZgduM2LYg2KLYqNin2K8iLCLYrNmI2LLYrNin2YYiLDI3MDUsMjcKNjYuMzMsMzcuMDUsIkRpc3RyaWN0IiwiTWFyZHlhbiIsIkphd3pqYW4iLCLZhdix2K/bjNin2YYiLCLYrNmI2LLYrNin2YYiLDI3MDgsMjcKNjYuMjEsMzYuOSwiRGlzdHJpY3QiLCJBcWNoYSIsIkphd3pqYW4iLCLYotmC2obZhyIsItis2YjYstis2KfZhiIsMjcwNywyNwo2NS45OSwzNy4wNiwiRGlzdHJpY3QiLCJNaW5nYWppayIsIkphd3pqYW4iLCLZhdmG2q/YrNqpIiwi2KzZiNiy2KzYp9mGIiwyNzAyLDI3CjY2LjA5LDM3LjI5LCJEaXN0cmljdCIsIlFhcnFpbiIsIkphd3pqYW4iLCLZgtix2YLbjNmGIiwi2KzZiNiy2KzYp9mGIiwyNzA5LDI3CjY1LjcyLDM3LjMxLCJEaXN0cmljdCIsIktoYW15YWIiLCJKYXd6amFuIiwi2K7ZhSDYotioKNiu2YXbjNin2KgiLCLYrNmI2LLYrNin2YYiLDI3MTAsMjcKNjMuNjcsMzMuNTgsIkRpc3RyaWN0IiwiU2FnaGFyIiwiR2hvciIsItiz2KfYutixIiwi2LrZiNixIiwyMTEwLDIxCjY0LjM4LDMzLjQ4LCJEaXN0cmljdCIsIlRheXdhcmFoIiwiR2hvciIsItiq24zZiNix2YciLCLYutmI2LEiLDIxMDYsMjEKNjMuNzEsMzQuMDEsIkRpc3RyaWN0IiwiVG9sYWsiLCJHaG9yIiwi2KrZiNmE2qkiLCLYutmI2LEiLDIxMDksMjEKNjQuMzQsMzQuMSwiRGlzdHJpY3QiLCJTaGFocmFrIiwiR2hvciIsIti02YfYsdqpIiwi2LrZiNixIiwyMTA1LDIxCjY1LjAxLDM0LjEyLCJEaXN0cmljdCIsIkR1IExpbmEiLCJHaG9yIiwi2K/ZiCDZhNuM2YbZhyIsIti62YjYsSIsMjEwMywyMQo2NS4xMiwzNS4xMiwiRGlzdHJpY3QiLCJDaGFoYXIgU2FkcmEiLCJHaG9yIiwi2obYp9ixINi12K8g2LHZhyIsIti62YjYsSIsMjEwMiwyMQo2NS4zMywzNC43MiwiUHJvdmluY2lhbF9DZW50ZXIiLCJDaGFnaGNoYXJhbiIsIkdob3IiLCLahti62obYsdin2YYiLCLYutmI2LEiLDIxMDEsMjEKNjUuNzUsMzQuNTgsIkRpc3RyaWN0IiwiRGF3bGF0IFlhciIsIkdob3IiLCLYr9mI2YTYqiDbjNin2LEiLCLYutmI2LEiLDIxMDQsMjEKNjYuODUsMzcuMjYsIkRpc3RyaWN0IiwiU2hvciBUZXBhIiwiQmFsa2giLCLYtNmI2LEg2KrbjNm+2YciLCLYqNmE2K4iLDE4MDMsMTgKNjcuMzYsMzcuMTksIlRvd24iLCJTaGFyYWstZS1IYXlyYXRhbiIsIkJhbGtoIiwi2LTZh9ix2qkg2K3bjNix2KrYp9mGIiwi2KjZhNiuIiwxODExLDE4CjY3LjU1LDM3LjEzLCJEaXN0cmljdCIsIkthbGRhciIsIkJhbGtoIiwi2qnZhNiv2KfYsSIsItio2YTYriIsMTgxMCwxOAo2Ni44LDM3LjA2LCJEaXN0cmljdCIsIkRhd2xhdCBBYmFkIiwiQmFsa2giLCLYr9mI2YTYqiDYotio2KfYryIsItio2YTYriIsMTgwNCwxOAo2Ny43NCwzNi44MSwiRGlzdHJpY3QiLCJLaHVsbSIsIkJhbGtoIiwi2K7ZhNmFIiwi2KjZhNiuIiwxODA5LDE4CjY3LjM0LDM2LjYzLCJEaXN0cmljdCIsIk1hcm11bCIsIkJhbGtoIiwi2YXYp9ix2YXZhCIsItio2YTYriIsMTgwOCwxOAo2Ny4yNCwzNi4zNywiRGlzdHJpY3QiLCJDaGFoYXIgS2VudCIsIkJhbGtoIiwi2obYp9ixINqp2YbYqiIsItio2YTYriIsMTgwNywxOAo2Ny4wMSwzNi42NCwiRGlzdHJpY3QiLCJEZWggRGFkaSIsIkJhbGtoIiwi2K/Zh9iv2KfYr9uMIiwi2KjZhNiuIiwxODA2LDE4CjY2Ljg5LDM2LjgyLCJEaXN0cmljdCIsIkJhbGtoIiwiQmFsa2giLCLYqNmE2K4iLCLYqNmE2K4iLDE4MDUsMTgKNjYuNjQsMzYuODMsIkRpc3RyaWN0IiwiQ2hhaGFyIEJvbGFrIiwiQmFsa2giLCLahtin2LEg2KjZiNmE2qkiLCLYqNmE2K4iLDE4MTIsMTgKNjYuNiwzNi41MiwiRGlzdHJpY3QiLCJDaGVtdGFsIiwiQmFsa2giLCLahtmF2KrYp9mEIiwi2KjZhNiuIiwxODEzLDE4CjY2Ljg2LDM2LjMsIkRpc3RyaWN0IiwiU2hvbGdhcmVoIiwiQmFsa2giLCLYtNmI2YTar9ix2YciLCLYqNmE2K4iLDE4MTQsMTgKNjYuOTIsMzYuMDEsIkRpc3RyaWN0IiwiS2lzaGluZGVoIiwiQmFsa2giLCLaqdi02YbYr9mHIiwi2KjZhNiuIiwxODE1LDE4CjY2LjczLDM1Ljg1LCJEaXN0cmljdCIsIlphcmkiLCJCYWxraCIsItiy2KfYsduMIiwi2KjZhNiuIiwxODE2LDE4CjY3LjU4LDM2LjQ2LCJEaXN0cmljdCIsIkZlcm96IE5ha2hjaGlyIiwiU2FtYW5nYW4iLCLZgduM2LHZiNiyINmG2K7ahtuM2LEiLCLYs9mF2Ybar9in2YYiLDE5MDMsMTkKNjcuMjgsMzUuNjgsIkRpc3RyaWN0IiwiRGFyYWggU3VmLWUtQmFsYSIsIlNhbWFuZ2FuIiwi2K/YsduAINi12YjZgSDYqNin2YTYpyIsItiz2YXZhtqv2KfZhiIsMTkwNSwxOQo2Ny44MiwzNS42NCwiRGlzdHJpY3QiLCJSdXktZS1EdSBBYiIsIlNhbWFuZ2FuIiwi2LHZiNuMINiv2Ygg2KLYqCIsItiz2YXZhtqv2KfZhiIsMTkwNywxOQo2OC4xMiwzNS45OSwiRGlzdHJpY3QiLCJLaHVyYW0gd2EgU2FyYmFnaCIsIlNhbWFuZ2FuIiwi2K7YsdmFINmI2LPYp9ix2KjYp9i6Iiwi2LPZhdmG2q/Yp9mGIiwxOTA2LDE5CjY2LjA2LDM2LjI4LCJQcm92aW5jaWFsX0NlbnRlciIsIlNhci1lLVB1bCIsIlNhci1lLVB1bCIsItiz2LHZvtmEIiwi2LPYsdm+2YQiLDIwMDEsMjAKNjUuNzEsMzYuMTUsIkRpc3RyaWN0IiwiU2F5YWQiLCJTYXItZS1QdWwiLCLYtduM2KfYryIsItiz2LHZvtmEIiwyMDAyLDIwCjY2LjIzLDM2LjAyLCJEaXN0cmljdCIsIlNvem1hIFFhbGEiLCJTYXItZS1QdWwiLCLYs9mI2LLZhdmHINmC2YTYudmHIiwi2LPYsdm+2YQiLDIwMDQsMjAKNjYuNDUsMzYuMDYsIkRpc3RyaWN0IiwiR29zZmFuZGkiLCJTYXItZS1QdWwiLCLar9mI2LPZgdmG2K/bjCIsItiz2LHZvtmEIiwyMDA1LDIwCjY2LjM0LDM1Ljg0LCJEaXN0cmljdCIsIlNhbmcoU2FuQ2hhcmFrIiwiU2FyLWUtUHVsIiwi2LPYp9mGINqG2KfYsdqpICjYs9mG2q8g2obYp9ix2qkiLCLYs9ix2b7ZhCIsMjAwNywyMAo2Ni42LDM1LjQ2LCJEaXN0cmljdCIsIkJhbGtoIEFiIiwiU2FyLWUtUHVsIiwi2KjZhNiu2KfYqCIsItiz2LHZvtmEIiwyMDA2LDIwCjY1Ljk0LDM1LjQ0LCJEaXN0cmljdCIsIktvaGlzdGFuYXQiLCJTYXItZS1QdWwiLCLaqdmI2YfYs9iq2KfZhtin2KoiLCLYs9ix2b7ZhCIsMjAwMywyMAo2OC4zNiwzNi45LCJEaXN0cmljdCIsIlFhbGEtZS1aYWwiLCJLdW5kdXoiLCLZgtmE2LnZhyDYsNin2YQiLCLaqdmG2K/YsiIsMTcwMywxNwo2OC41NywzNi42NSwiRGlzdHJpY3QiLCJDaGFoYXIgRGFyYWgiLCJLdW5kdXoiLCLahtmH2KfYsSDYr9ix2YciLCLaqdmG2K/YsiIsMTcwNCwxNwo2OC44NSwzNy4xMSwiRGlzdHJpY3QiLCJJbWFtIFNhaGViIiwiS3VuZHV6Iiwi2KfZhdin2YUg2LXYp9it2KgiLCLaqdmG2K/YsiIsMTcwMiwxNwo2OS4xNiwzNi45NiwiRGlzdHJpY3QiLCJEYXNodC1lLUFyY2hpIiwiS3VuZHV6Iiwi2K/YtNiqINin2LHahtuMIiwi2qnZhtiv2LIiLDE3MDcsMTcKNjguODYsMzYuOCwiUHJvdmluY2lhbF9DZW50ZXIiLCJLdW5kdXoiLCJLdW5kdXoiLCLaqdmG2K/YsiIsItqp2YbYr9iyIiwxNzAxLDE3CjY4Ljg4LDM2LjUzLCJEaXN0cmljdCIsIkFsaSBBYmFkIiwiS3VuZHV6Iiwi2LnZhNuMINii2KjYp9ivIiwi2qnZhtiv2LIiLDE3MDUsMTcKNjkuMSwzNi42LCJEaXN0cmljdCIsIktoYW4gQWJhZCIsIkt1bmR1eiIsItiu2KfZhiDYotio2KfYryIsItqp2YbYr9iyIiwxNzA2LDE3CjY4LjYyLDM2LjMzLCJEaXN0cmljdCIsIkJhZ2hsYW4tZS1qYWRpZCIsIkJhZ2hsYW4iLCLYqNi62YTYp9mGINis2K/bjNivIiwi2KjYutmE2KfZhiIsOTAyLDkKNjkuMTYsMzYuMjQsIkRpc3RyaWN0IiwiQnVya2EiLCJCYWdobGFuIiwi2KjZiNix2qnZhyAo2KjYsdqp2YciLCLYqNi62YTYp9mGIiw5MTAsOQo2OC40NiwzNS44MiwiRGlzdHJpY3QiLCJEYWhhbmEtZS1HaG9yeSIsIkJhZ2hsYW4iLCLYr9mH2YbZhyDYutmI2LHbjCIsItio2LrZhNin2YYiLDkwMyw5CjY4LjY3LDM2LjAxLCJQcm92aW5jaWFsX0NlbnRlciIsIlB1bC1lLSBraHVtcmkiLCJCYWdobGFuIiwi2b7ZhNiu2YXYsduMIiwi2KjYutmE2KfZhiIsOTAxLDkKNjguNjcsMzUuNjIsIkRpc3RyaWN0IiwiRHVzaGkiLCJCYWdobGFuIiwi2K/ZiNi024wiLCLYqNi62YTYp9mGIiw5MDQsOQo2OS4wMywzNiwiRGlzdHJpY3QiLCJOYWhyaW4iLCJCYWdobGFuIiwi2YbZh9ix24zZhiIsItio2LrZhNin2YYiLDkwNSw5CjY5LjI5LDM1Ljk3LCJEaXN0cmljdCIsIktod2FqYSBIZWpyYW4iLCJCYWdobGFuIiwi2K7ZiNin2KzZhyDZh9is2LHYp9mGIiwi2KjYutmE2KfZhiIsOTA5LDkKNjkuMTgsMzUuNjIsIkRpc3RyaWN0IiwiQW5kYXJhYiIsIkJhZ2hsYW4iLCLYp9mG2K/Ysdin2KgiLCLYqNi62YTYp9mGIiw5MDgsOQo2OC45NywzNS40NywiRGlzdHJpY3QiLCJLaGVuamFuIiwiQmFnaGxhbiIsItiu2YbYrNin2YYiLCLYqNi62YTYp9mGIiw5MDcsOQo2OC4zOCwzNS4yOCwiRGlzdHJpY3QiLCJUYWxhIHdhIGJhcmZhayIsIkJhZ2hsYW4iLCLYqtin2YTZhyDZiNio2LHZgdqpIiwi2KjYutmE2KfZhiIsOTA2LDkKNjkuNTcsMzYuMjMsIkRpc3RyaWN0IiwiR3V6YXJnYWgtZS0gTnVyIiwiQmFnaGxhbiIsItqv2LDYsdqv2KfZhyDZhtmI2LEiLCLYqNi62YTYp9mGIiw5MTQsOQo2OS42OCwzNi4xNCwiRGlzdHJpY3QiLCJGaXJpbmcgV2EgR2hhcnUiLCJCYWdobGFuIiwi2YHYsdmG2q8g2LrYp9ix2YgiLCLYqNi62YTYp9mGIiw5MTUsOQo2OS4zNiwzNS43NCwiRGlzdHJpY3QiLCJEZWggU2FsYWgiLCJCYWdobGFuIiwi2K/ZhyDYtdmE2KfYrSIsItio2LrZhNin2YYiLDkxMiw5CjY5LjM5LDM2LjM3LCJEaXN0cmljdCIsIklzaGthbWlzaCIsIlRha2hhciIsItin2LTaqdmF2LQiLCLYqtiu2KfYsSIsMTYxNSwxNgo2OS41NiwzNi41LCJEaXN0cmljdCIsIkNoYWwiLCJUYWtoYXIiLCLahtin2YQiLCLYqtiu2KfYsSIsMTYwNSwxNgo2OS43MSwzNi40NywiRGlzdHJpY3QiLCJOYW1hayBBYiIsIlRha2hhciIsItmG2YXaqSDYotioIiwi2KrYrtin2LEiLDE2MDYsMTYKNjkuOTYsMzYuNDcsIkRpc3RyaWN0IiwiRmFya2hhciIsIlRha2hhciIsItmB2LHYrtin2LEiLCLYqtiu2KfYsSIsMTYwNywxNgo2OS4zNCwzNi42MSwiRGlzdHJpY3QiLCJCYW5naSIsIlRha2hhciIsItio2Ybar9uMIiwi2KrYrtin2LEiLDE2MDQsMTYKNjkuNCwzNi44MywiRGlzdHJpY3QiLCJCYWhhcmFrIiwiVGFraGFyIiwi2KjZh9in2LHaqSIsItiq2K7Yp9ixIiwxNjAzLDE2CjY5LjU3LDM2LjkzLCJEaXN0cmljdCIsIkhhemFyIFN1bXVjaCIsIlRha2hhciIsItmH2LLYp9ixINiz2YXZiNqGIiwi2KrYrtin2LEiLDE2MDIsMTYKNjkuNjEsMzYuNzQsIlByb3ZpbmNpYWxfQ2VudGVyIiwiVGFsb3FhbiIsIlRha2hhciIsItiq2KfZhNmC2KfZhiIsItiq2K7Yp9ixIiwxNjAxLDE2CjY5LjkxLDM2Ljc5LCJEaXN0cmljdCIsIkthbGFmZ2FuIiwiVGFraGFyIiwi2qnZhNmB2q/Yp9mGIiwi2KrYrtin2LEiLDE2MDgsMTYKNjkuODIsMzcuMTEsIkRpc3RyaWN0IiwiUm9zdGFxIiwiVGFraGFyIiwi2LHYs9iq2KfZgiIsItiq2K7Yp9ixIiwxNjA5LDE2CjY5LjUsMzcuMiwiRGlzdHJpY3QiLCJEYXNodC1lLVFhbGEiLCJUYWtoYXIiLCLYr9i02Kog2YLZhNi52YciLCLYqtiu2KfYsSIsMTYxMywxNgo2OS41OCwzNy4zNCwiRGlzdHJpY3QiLCJLaHdhamEgQmFoYXd1ZGRpbiIsIlRha2hhciIsItiu2YjYp9is2Ycg2KjZh9in2YjYp9mE2K/bjNmGIiwi2KrYrtin2LEiLDE2MTIsMTYKNjkuNSwzNy40NSwiRGlzdHJpY3QiLCJEYXJxYWQiLCJUYWtoYXIiLCLYr9ix2YLYryIsItiq2K7Yp9ixIiwxNjE3LDE2CjY5LjY3LDM3LjQ2LCJEaXN0cmljdCIsIllhbmdpIFFhbGEiLCJUYWtoYXIiLCLbjNmG2q/bjCDZgtmE2LnZhyIsItiq2K7Yp9ixIiwxNjExLDE2CjY5Ljg4LDM3LjQ1LCJEaXN0cmljdCIsIkNoYWggQWIiLCJUYWtoYXIiLCLahtin2Ycg2KLYqCIsItiq2K7Yp9ixIiwxNjEwLDE2CjcwLjE0LDM3LjM0LCJEaXN0cmljdCIsIlNoYWhyLWUtQnV6b3JnIiwiQmFkYWtoc2hhbiIsIti02YfYsSDYqNiy2LHaryIsItio2K/Yrti02KfZhiIsMTUwOCwxNQo3MC4zOCwzNy41MywiRGlzdHJpY3QiLCJZYXdhbiIsIkJhZGFraHNoYW4iLCLbjNin2YjYp9mGIiwi2KjYr9iu2LTYp9mGIiwxNTA3LDE1CjcwLjY3LDM3LjY2LCJEaXN0cmljdCIsIlJhZ2hpc3RhbiIsIkJhZGFraHNoYW4iLCLYsdin2LrYs9iq2KfZhiIsItio2K/Yrti02KfZhiIsMTUwNiwxNQo3MC4zOSwzNy44NCwiRGlzdHJpY3QiLCJLaHdhaGFuIiwiQmFkYWtoc2hhbiIsItiu2YjYp9mH2KfZhiIsItio2K/Yrti02KfZhiIsMTUxNywxNQo3MC42OSwzNy45NCwiRGlzdHJpY3QiLCJLb2YgQWIiLCJCYWRha2hzaGFuIiwi2qnZiNmBINii2KgiLCLYqNiv2K7YtNin2YYiLDE1MTYsMTUKNzAuMzcsMzcuMzEsIkRpc3RyaWN0IiwiWWFmdGFsLWUtU3VmbGEiLCJCYWRha2hzaGFuIiwi24zZgdiq2YQg2LPZgdmE24wiLCLYqNiv2K7YtNin2YYiLDE1MDIsMTUKNzAuNjYsMzcuNDIsIkRpc3RyaWN0IiwiS29oaXN0YW4iLCJCYWRha2hzaGFuIiwi2qnZiNmH2LPYqtin2YYiLCLYqNiv2K7YtNin2YYiLDE1MDUsMTUKNzAuNjUsMzguMjEsIkRpc3RyaWN0IiwiU2hha2kiLCJCYWRha2hzaGFuIiwi2LTaqduMIiwi2KjYr9iu2LTYp9mGIiwxNTI1LDE1CjcwLjkxLDM4LjIzLCJEaXN0cmljdCIsIkRhcndheiIsIkJhZGFraHNoYW4iLCLYr9ix2YjYp9iyIiwi2KjYr9iu2LTYp9mGIiwxNTI0LDE1CjcxLjE1LDM4LjEzLCJEaXN0cmljdCIsIkRhcndhei1lLUJhbGxhIiwiQmFkYWtoc2hhbiIsItiv2LHZiNin2LIg2KjYp9mE2KciLCLYqNiv2K7YtNin2YYiLDE1MTUsMTUKNzEuMjQsMzcuNiwiRGlzdHJpY3QiLCJTaGlnaG5hbiIsIkJhZGFraHNoYW4iLCLYtNi62YbYp9mGIiwi2KjYr9iu2LTYp9mGIiwxNTE0LDE1CjcwLjgyLDM3LjI3LCJEaXN0cmljdCIsIkFyZ2hhbmogS2h3YSIsIkJhZGFraHNoYW4iLCLYp9ix2LrZhtqG2K7ZiNin2YciLCLYqNiv2K7YtNin2YYiLDE1MDQsMTUKNzAuNjEsMzcuMjIsIlByb3ZpbmNpYWxfQ2VudGVyIiwiRmFpeiBBYmFkIiwiQmFkYWtoc2hhbiIsItmB24zYtiDYotio2KfYryIsItio2K/Yrti02KfZhiIsMTUwMSwxNQo3MS4xNiwzNy4xLCJEaXN0cmljdCIsIlNodWhhZGEiLCJCYWRha2hzaGFuIiwi2LTZh9iv2KciLCLYqNiv2K7YtNin2YYiLDE1MTMsMTUKNzAuNzMsMzYuOTQsIkRpc3RyaWN0IiwiS2hhc2giLCJCYWRha2hzaGFuIiwi2K7Yp9i0Iiwi2KjYr9iu2LTYp9mGIiwxNTExLDE1CjcwLjQzLDM3LjA2LCJEaXN0cmljdCIsIkFyZ28iLCJCYWRha2hzaGFuIiwi2KfYsdqv2YgiLCLYqNiv2K7YtNin2YYiLDE1MDMsMTUKNzAuNDcsMzYuODcsIkRpc3RyaWN0IiwiRGFyYXllbSIsIkJhZGFraHNoYW4iLCLYr9ix2KfbjNmFIiwi2KjYr9iu2LTYp9mGIiwxNTEwLDE1CjcwLjQsMzYuNzcsIkRpc3RyaWN0IiwiVGVzaGthbiIsIkJhZGFraHNoYW4iLCLYqti02qnYp9mGIiwi2KjYr9iu2LTYp9mGIiwxNTA5LDE1CjcwLjksMzcuMDMsIkRpc3RyaWN0IiwiQmFoYXJhayIsIkJhZGFraHNoYW4iLCLYqNmH2KfYsdqpIiwi2KjYr9iu2LTYp9mGIiwxNTEyLDE1CjcxLjE4LDM2Ljc2LCJEaXN0cmljdCIsIldhcmR1aiIsIkJhZGFraHNoYW4iLCLZiNix2K/ZiNisIiwi2KjYr9iu2LTYp9mGIiwxNTIyLDE1CjcwLjkxLDM2Ljc0LCJEaXN0cmljdCIsIkpvcm0iLCJCYWRha2hzaGFuIiwi2KzYsdmFIiwi2KjYr9iu2LTYp9mGIiwxNTIxLDE1CjcxLjM0LDM2LjQzLCJEaXN0cmljdCIsIlplYmFrIiwiQmFkYWtoc2hhbiIsItiy24zYqNin2qkiLCLYqNiv2K7YtNin2YYiLDE1MjcsMTUKNzEuNDYsMzYuODUsIkRpc3RyaWN0IiwiSXNoa2FzaGVtIiwiQmFkYWtoc2hhbiIsItin2LTaqdin2LTZhSIsItio2K/Yrti02KfZhiIsMTUyMywxNQo3My4zNSwzNy4wNiwiRGlzdHJpY3QiLCJXYWtoYW4iLCJCYWRha2hzaGFuIiwi2YjYp9iu2KfZhiIsItio2K/Yrti02KfZhiIsMTUyOCwxNQo3MC43NywzNS45MywiRGlzdHJpY3QiLCJLb3JhbiB3YSBNb25qYW4iLCJCYWRha2hzaGFuIiwi2qnYsdin2YYg2YjZhdmG2KzYp9mGIiwi2KjYr9iu2LTYp9mGIiwxNTI2LDE1CjcwLjc5LDM2LjQ0LCJEaXN0cmljdCIsIllhbWdhbiIsIkJhZGFraHNoYW4iLCLbjNmF2q/Yp9mGIiwi2KjYr9iu2LTYp9mGIiwxNTIwLDE1CjcwLjEzLDM2Ljc3LCJEaXN0cmljdCIsIktlc2hlbSIsIkJhZGFraHNoYW4iLCLaqdi02YUiLCLYqNiv2K7YtNin2YYiLDE1MTgsMTUKNzAuMzUsMzYuNDgsIkRpc3RyaWN0IiwiVGFnYWIiLCJCYWRha2hzaGFuIiwi2Krar9in2KgiLCLYqNiv2K7YtNin2YYiLDE1MTksMTUKNjkuNCwzNy4wMiwiRGlzdHJpY3QiLCJLaHdhamEgR2hhciIsIlRha2hhciIsItiu2YjYp9is2Ycg2LrYp9ixIiwi2KrYrtin2LEiLDE2MTQsMTYKNjkuNjcsMzUuOSwiRGlzdHJpY3QiLCJLaG93c3Qgd2EgRmVyZW5nIiwiQmFnaGxhbiIsItiu2YjYs9iqINmI2YHYsdmG2q8iLCLYqNi62YTYp9mGIiw5MTMsOQo2OS41NiwzNS41OCwiRGlzdHJpY3QiLCJQdWwtZS1IZXNhciIsIkJhZ2hsYW4iLCLZvtmEINit2LXYp9ixIiwi2KjYutmE2KfZhiIsOTExLDkKNjcuODcsMzYuMzgsIkRpc3RyaWN0IiwiSGF6cmF0LWUtIFN1bHRhbiIsIlNhbWFuZ2FuIiwi2K3Yttix2Kog2LPZhNi32KfZhiIsItiz2YXZhtqv2KfZhiIsMTkwMiwxOQo2Ny4zNSwzNi4wMywiRGlzdHJpY3QiLCJEYXJhaCBTdWYtZS0gUGF5aW4iLCJTYW1hbmdhbiIsItiv2LHbgCDYtdmI2YEg2b7Yp9im24zZhiIsItiz2YXZhtqv2KfZhiIsMTkwNCwxOQo2Ny45OSwzNi4yMSwiUHJvdmluY2lhbF9DZW50ZXIiLCJBeWJhayIsIlNhbWFuZ2FuIiwi2KfbjNio2qkiLCLYs9mF2Ybar9in2YYiLDE5MDEsMTkKNzAuMTQsMzYuMSwiRGlzdHJpY3QiLCJXYXJzYWoiLCJUYWtoYXIiLCLZiNix2LPYrCIsItiq2K7Yp9ixIiwxNjE2LDE2CjYzLjg2LDMzLjE0LCJEaXN0cmljdCIsIlB1ciBDaGFtYW4iLCJGYXJhaCIsItm+2Y/YsdqG2YXZhiIsItmB2LHYp9mHIiwzMTEwLDMxCjYyLjM4LDMzLjI1LCJEaXN0cmljdCIsIlNoaW5kYW5kIiwiSGlyYXQiLCLYtNuM2YbYr9mG2K8iLCLZh9ix2KfYqiIsMzAxNSwzMAo2My4xMiwzNC44MSwiUHJvdmluY2lhbF9DZW50ZXIiLCJRYWxhLWUtTmF3IiwiQmFkZ2hpcyIsItmC2YTYudmHINmG2YgiLCLYqNin2K/YutuM2LMiLDI5MDEsMjkKNjUuMDksMzMuNTksIkRpc3RyaWN0IiwiUGFzYWJhbmQiLCJHaG9yIiwi2b7Ys9in2KjZhtivIiwi2LrZiNixIiwyMTA3LDIxCjY0LjI4LDM1LjY0LCJEaXN0cmljdCIsIlFheXNhciIsIkZhcnlhYiIsItmC24zYtdin2LEiLCLZgdin2LHbjNin2KgiLDI4MDYsMjgKNjcuMTIsMzYuNzEsIlByb3ZpbmNpYWxfQ2VudGVyIiwiTWF6YXItZS1TaGFyaWYiLCJCYWxraCIsItmF2LLYp9ixINi02LHbjNmBIiwi2KjZhNiuIiwxODAxLDE4CjY3LjE4LDM2Ljg0LCJEaXN0cmljdCIsIk5haHItZS0gU2hhaGkiLCJCYWxraCIsItmG2YfYsSDYtNin2YfbjCIsItio2YTYriIsMTgwMiwxOAo=',
          ),
        ),
      ),
    )
  );
}
