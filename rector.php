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
 *
 * @todo Add CompleteDynamicPropertiesRector when it works.
 */

declare(strict_types=1);

use DrupalRector\Drupal8\Rector\Deprecation\GetMockRector as DrupalGetMockRector;
use DrupalFinder\DrupalFinder;
use DrupalRector\Set\Drupal9SetList;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\PHPUnit\PHPUnit50\Rector\StaticCall\GetMockRector;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {

  // Rector-ize this repo.
  $rectorConfig->paths([
    __DIR__,
  ]);

  // Our base version of PHP.
  $rectorConfig->phpVersion(PhpVersion::PHP_74);

  $rectorConfig->sets([
    Drupal9SetList::DRUPAL_94,
    LevelSetList::UP_TO_PHP_74,
  ]);

  $rectorConfig->skip([
    '*/upgrade_status/tests/modules/*',
    // Keep getMockBuilder() for now.
    GetMockBuilderGetMockToCreateMockRector::class,
    DrupalGetMockRector::class,
    GetMockRector::class,
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
    PublicConstantVisibilityRector::class,
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
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);
};
