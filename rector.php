<?php

/**
 * @file
 *
 * % ddev composer show palantirnet/drupal-rector --tree
 * palantirnet/drupal-rector dev-stable-in-composer Instant fixes for your Drupal code by using Rector.
 * ├──rector/rector @stable
 * │  ├──php ^7.2|^8.0
 * │  └──phpstan/phpstan ^1.10.35
 * │     └──php ^7.2|^8.0
 * └──webflo/drupal-finder @stable
 * └──ext-json *
 */

declare(strict_types=1);

use DrupalFinder\DrupalFinder;
use DrupalRector\Set\Drupal9SetList;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Set\ValueObject\SetList;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return static function (RectorConfig $rectorConfig): void {

  // Add paths as needed.
  $rectorConfig->paths([
    __DIR__ . '/docroot/modules/contrib/dkan',
  ]);

  $rectorConfig->rules([
    // Always declare properties.
    CompleteDynamicPropertiesRector::class,
  ]);

  $rectorConfig->sets([
    SetList::DEAD_CODE,
    Drupal9SetList::DRUPAL_9,
  ]);

  $rectorConfig->skip([
    '*/upgrade_status/tests/modules/*',
    // Keep getMockBuilder() for now.
    GetMockBuilderGetMockToCreateMockRector::class,
    // Don't throw errors on JSON parse problems. Yet.
    // @todo Throw errors and deal with them appropriately.
    JsonThrowOnErrorRector::class,
    // We like our tags. Unfortunately some other rules obliterate them anyway.
    RemoveUselessParamTagRector::class,
    RemoveUselessVarTagRector::class,
    RemoveUselessReturnTagRector::class,
    ClassPropertyAssignToConstructorPromotionRector::class,
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

  $rectorConfig->fileExtensions([
    'php',
    'module',
    'theme',
    'install',
    'profile',
    'inc',
    'engine',
  ]);
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);
};
