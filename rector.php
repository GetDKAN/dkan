<?php

/**
 * @file
 * Rector config for DKAN.
 *
 * To use this file:
 * - Require palantirnet/drupal-rector into your project root composer.json
 *   file: composer require --dev palantirnet/drupal-rector
 * - Add the following to the script section of your project composer.json:
 *
 * "scripts": {
 *   "rector": "./vendor/bin/rector -c \
 *              ./docroot/modules/contrib/dkan/rector.php",
 *   "rector-dry-run": "./vendor/bin/rector -c \
 *              ./docroot/modules/contrib/dkan/rector.php --dry-run"
 * }
 *
 * Now you can say: composer rector-dry-run, and eventually: composer rector.
 */

declare(strict_types=1);

use DrupalFinder\DrupalFinder;
use Rector\Config\RectorConfig;
use DrupalRector\Set\Drupal10SetList;
use DrupalRector\Rector\Deprecation\FunctionToStaticRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use DrupalRector\Rector\PHPUnit\ShouldCallParentMethodsRector;

return static function (RectorConfig $rectorConfig): void {

  // Rector-ize this repo.
  $rectorConfig->paths([
    __DIR__,
  ]);

  $rectorConfig->sets([
    Drupal10SetList::DRUPAL_10,
  ]);

  $rectorConfig->skip([
    FunctionToStaticRector::class,
    AddReturnTypeDeclarationRector::class,
    ShouldCallParentMethodsRector::class,
  ]);

  $drupalFinder = new DrupalFinder();
  $drupalFinder->locateRoot(__DIR__);
  $drupalRoot = $drupalFinder->getDrupalRoot();

  $rectorConfig->autoloadPaths([
    $drupalRoot . '/core',
    $drupalRoot . '/modules',
    $drupalRoot . '/profiles',
    $drupalRoot . '/themes',
  ]);
  $rectorConfig->skip(['*/upgrade_status/tests/modules/*']);
  $rectorConfig->fileExtensions([
    'php', 'module', 'theme', 'install', 'profile', 'inc', 'engine',
  ]);
  $rectorConfig->removeUnusedImports();
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);
};
