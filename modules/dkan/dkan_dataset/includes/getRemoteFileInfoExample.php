<?php

/**
 * @file
 * Example file for getting file type info from getRemoteFileInfo.
 */

use dkanDataset\getRemoteFileInfo;

include_once 'getRemoteFileInfo.php';

$url = 'https://www.nd.gov/gis/apps/NDHUB.TraumaCenters.csv';

$file_info = new GetRemoteFileInfo($url, 'test');
var_dump($file_info->getType());
var_dump($file_info->getName());
