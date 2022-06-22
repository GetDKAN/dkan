<?php

foreach (scandir(__DIR__) as $file) {
    if (preg_match("/settings\..*\.php/", $file) == true
      && substr_count($file, "default") == 0) {
        include __DIR__ . "/{$file}";
    }
}

$config_directories['sync'] = '../config/sync';
$settings["config_sync_directory"] = '../config/sync';
$settings['hash_salt'] = '{HASH_SALT}';
