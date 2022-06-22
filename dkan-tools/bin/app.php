#!/usr/bin/env php
<?php

use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Symfony\Component\Console\Output\ConsoleOutput;

foreach ([
  // Legacy-style autoload.
  '/../vendor/autoload.php',
  // Under vendor.
  '/../../vendor/autoload.php',
         ] as $path) {
  $pathy = __DIR__ . $path;
  if (file_exists($pathy)) {
    require_once $pathy;
  }
}

putenv("COMPOSER_MEMORY_LIMIT=-1");

$dktl_directory = DkanTools\Util\Util::getDktlDirectory();
$dktl_project_directory = DkanTools\Util\Util::getProjectDirectory();

$output = new ConsoleOutput();

$discovery = new CommandFileDiscovery();
$discovery->setSearchPattern('*Commands.php');
$defaultCommandClasses = $discovery->discover("{$dktl_directory}/src", '\\DkanTools');

$customCommandClasses = [];
if (file_exists("{$dktl_project_directory}/src/command")) {
    $customCommandClasses = $discovery->discover("{$dktl_project_directory}/src/command", '\\DkanTools\\Custom');
}

$commandClasses = array_merge($defaultCommandClasses, $customCommandClasses);

$appName = "DKAN Tools";
$appVersion = '2.0.0-rc1';
$configurationFilename = 'dktl.yml';

$runner = new \Robo\Runner($commandClasses);
$runner->setConfigurationFilename($configurationFilename);

$argv = $_SERVER['argv'];

$output = new ConsoleOutput();
$statusCode = $runner->execute($argv, $appName, $appVersion, $output);

exit($statusCode);
