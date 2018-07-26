<?php

global $base_url;

$redirect = 'Location: ' . $base_url . '/profiles/dkan/test/files/dkan/Polling_Places_Madison_test.csv';

/* Redirect browser */
header($redirect);

exit;
?>
