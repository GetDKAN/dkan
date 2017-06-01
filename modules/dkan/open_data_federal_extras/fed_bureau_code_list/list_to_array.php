<?php

/**
 * @file
 * Script for changing csv file of federal agencies to an array. 
 *
 * Latest version from https://github.com/batemapf/OMB-Agency-Bureau-and-Treasury-Codes/tree/new-json.
 */

const OPFE_CSV_BUREAU_CODES_FILE_NAME = 'omb-agency-bureau-treasury-codes.csv';

/**
 * Processes csv file.
 */
function opfe_list_to_array_process() {
  opfe_list_to_array_process_bureaus();
}

/**
 * Export bureau files.
 */
function opfe_list_to_array_process_bureaus() {
  $records = opfe_list_to_array_read_file(OPFE_CSV_BUREAU_CODES_FILE_NAME);
  $headers = array_shift($records);
  $file = '<?php
/**
 * @file
 * Declares Bureau codes.
 */

$bureau_codes = ';

  $list = array();
  foreach ($records as $key => $record) {
    foreach ($headers as $header_key => $header) {
      // Bureau code is the key.
      if ($record[2]) {
        $result["$record[2]:$record[3]"] = "$record[2]:$record[3] - $record[1]";
      }
    }
  }
  file_put_contents('omb-agency-bureau-treasury-codes.php', $file . var_export($result, TRUE) . ';');
}

/**
 * Reads csv file.
 */
function opfe_list_to_array_read_file($csv = OPFE_CSV_BUREAU_CODES_FILE_NAME) {
  $file_handle = fopen($csv, "r");
  $records = array();
  $i = 0;
  while (!feof($file_handle)) {
    $line = fgetcsv($file_handle, 1024);
    $records[] = $line;
  }
  fclose($file_handle);
  return $records;
}

/**
 * Validates headers for csv file.
 */
function opfe_list_to_array_validate_file($headers) {
  if (trim($headers[1]) != 'Department' ||
    trim($headers[2]) != 'Agency' ||
    trim($headers[3]) != 'OMB Agency Code' ||
    trim($headers[4]) != 'OMB Bureau Code' ||
    trim($headers[5]) != 'Treasury Agency Code') {
    throw new Exception("File headers do not match expected format");
  }
}
