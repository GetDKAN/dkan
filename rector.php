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
use DrupalRector\Rector\Deprecation\FunctionToStaticRector;
use DrupalRector\Set\Drupal10SetList;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {

  // Rector-ize this repo.
  $rectorConfig->paths([
    __DIR__,
  ]);

  $rectorConfig->sets([
    Drupal10SetList::DRUPAL_10,
    SetList::PHP_80,
    SetList::DEAD_CODE,
  ]);

  $rectorConfig->skip([
    // Skip data_dictionary_widget to avoid merge conflicts.
    // @todo Add this back.
    '*/modules/data_dictionary_widget',
    // Skip this file because we want its switch/case to remain:
    // @todo Figure out what to do about DataFactory::getInstance().
    '*/modules/metastore/src/Storage/DataFactory.php',
    // Skip this file to keep the debug method.
    // @todo Do we need the debug method?
    '*/modules/common/tests/src/Unit/Storage/SelectFactoryTest.php',
    // Don't change the signature of these service classes.
    // @todo Unskip these later.
    '*/modules/datastore/src/Service/Info/ImportInfo.php',
    '*/modules/frontend/src/Routing/RouteProvider.php',
    '*/modules/frontend/src/Page.php',
    // Don't throw errors on JSON parse problems. Yet.
    // @todo Throw errors and deal with them appropriately.
    JsonThrowOnErrorRector::class,
    // We like our tags. Unfortunately some other rules obliterate them anyway.
    RemoveUselessParamTagRector::class,
    RemoveUselessVarTagRector::class,
    RemoveUselessReturnTagRector::class,
    AddDoesNotPerformAssertionToNonAssertingTestRector::class,
    ClosureToArrowFunctionRector::class,
    // Don't automate ::class because we need some string literals that look
    // like class names.
    // @see \Drupal\common\Util\JobStoreUtil
    // @see \Drupal\common\EventDispatcherTrait
    StringClassNameToClassConstantRector::class,
    RemoveExtraParametersRector::class,
    RemoveParentCallWithoutParentRector::class,
    ClassPropertyAssignToConstructorPromotionRector::class,
    FunctionToStaticRector::class,
    NullToStrictStringFuncCallArgRector::class,
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

  // @todo Add removeUnusedImports().
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);
};
