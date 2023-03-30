<?php

/**
 * @file
 */

declare(strict_types=1);

/**
 * Call from the project directory like:
 * ./vendor/bin/rector process --config=docroot/modules/contrib/dkan/rector.php
 */

use DrupalFinder\DrupalFinder;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Transform\ValueObject\ClassMethodReference;
use Rector\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector;

return static function (RectorConfig $rectorConfig): void {
  $rectorConfig->sets([
    LevelSetList::UP_TO_PHP_74,
  ]);
  $rectorConfig->skip([
    JsonThrowOnErrorRector::class,
    ClosureToArrowFunctionRector::class,
  ]);
  $rectorConfig->ruleWithConfiguration(
    ReturnTypeWillChangeRector::class,
    [new ClassMethodReference('JsonSerializable', 'jsonSerialize')]
  );

  $parameters = $rectorConfig->parameters();

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
  $parameters->set('drupal_rector_notices_as_comments', TRUE);
};
