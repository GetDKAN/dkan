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

use DrupalFinder\DrupalFinderComposerRuntime;
use DrupalRector\Set\Drupal10SetList;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;

return static function (RectorConfig $rectorConfig): void {

  // Rector-ize this repo.
  $rectorConfig->paths([
    __DIR__,
  ]);

  $rectorConfig->sets([
    Drupal10SetList::DRUPAL_10,
    SetList::PHP_80,
    SetList::PHP_81,
    SetList::PHP_82,
    SetList::PHP_83,
    SetList::PHP_84,
    SetList::DEAD_CODE,
  ]);

  $rectorConfig->skip([
    // Don't change the signature of these service classes.
    // @todo Unskip these later.
    '*/modules/datastore/src/Service/Info/ImportInfo.php',
    '*/modules/frontend/src/Routing/RouteProvider.php',
    '*/modules/frontend/src/Page.php',
    // These seems a little excessive for now, revisit later.
    AddOverrideAttributeToOverriddenMethodsRector::class,
    ClassPropertyAssignToConstructorPromotionRector::class,
    ReadOnlyPropertyRector::class,
    ReturnNeverTypeRector::class,
    AddTypeToConstRector::class,
  ]);

  $drupalFinder = new DrupalFinderComposerRuntime(__DIR__);
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

  // @todo Add removeUnusedImports().
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);
};
