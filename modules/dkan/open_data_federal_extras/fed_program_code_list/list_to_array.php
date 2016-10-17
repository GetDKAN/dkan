<?php

/**
 * @file
 * Script for changing csv file of federal agencies to an array.
 */

const OPFE_CSV_CODES_FILE_NAME = 'Program Code Inventory-Table 1.csv';
const OPFE_CSV_AGENCY_FILE_NAME = 'Sheet2-Table 1.csv';

/**
 * Processes csv file.
 */
function opfe_list_to_array_process() {
  opfe_list_to_array_process_codes();
  opfe_list_to_array_process_agencies();
}

/**
 * Export code files.
 */
function opfe_list_to_array_process_agencies() {
  $records = opfe_list_to_array_read_file(OPFE_CSV_AGENCY_FILE_NAME);
  $headers = array_shift($records);
  $file = '<?php

  /**
   * @file
   * Array of federal agencies produced by Federal Inventory Program.
   * @see http://project-open-data.github.io/schema/#programCode
   */

  $federal_inventory_agency_list = ';
  $list = array();
  foreach ($records as $key => $record) {
    foreach ($headers as $header_key => $header) {
      // Bureau code is the key.
      if ($record[1]) {
        $result[$record[1]] = $record[0];
      }
    }
  }
  file_put_contents('federal_inventory_agencies.php', $file . var_export($result, TRUE) . ';');
}

/**
 * Export code files.
 */
function opfe_list_to_array_process_codes() {
  $records = opfe_list_to_array_read_file();
  opfe_list_to_array_validate_file($records[0]);
  $headers = array_shift($records);
  $full_file = '<?php

  /**
   * @file
   * Array of federal agency codes produced by Federal Inventory Program.
   * @see http://project-open-data.github.io/schema/#programCode
   */

  $federal_inventory_full_list = ';
  $small_file = '<?php

  /**
   * @file
   * Key/Vallue array of federal agencies produced by Federal Inventory Program.
   * @see http://project-open-data.github.io/schema/#programCode
   */

  $federal_inventory_small_list = ';
  $full_list = array();
  $small_list = array();
  foreach ($records as $key => $record) {
    foreach ($headers as $header_key => $header) {
      // Bureau code is the key.
      $full_result[$record[5]][$header] = $record[$header_key];
      $small_result[$record[5]] = $record[5] . ' - ' . $record[0] . ' - ' . $record[1];
    }
  }
  file_put_contents('federal_inventory_codes.php', $full_file . var_export($full_result, TRUE) . ';');
  file_put_contents('federal_inventory_list.php', $small_file . var_export($small_result, TRUE) . ';');
}

/**
 * Reads csv file.
 */
function opfe_list_to_array_read_file($csv = OPFE_CSV_CODES_FILE_NAME) {
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
  if (trim($headers[0]) != 'Agency Name' ||
    trim($headers[1]) != 'Program Name' ||
    trim($headers[2]) != 'Additional Information (optional)' ||
    trim($headers[3]) != 'agencyCode' ||
    trim($headers[4]) != 'ProgramCode' ||
    trim($headers[5]) != 'ProgramCodePODFormat') {
    throw new Exception("File headers do not match expected format");
  }
}
