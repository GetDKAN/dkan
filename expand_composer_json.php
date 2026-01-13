#!/usr/bin/env php
<?php

/**
 * @file
 * Populate a composer.json with the right dependencies.
 */

set_error_handler("halt_on_warning");

$project_name = $argv[1] ?? getenv('CI_PROJECT_NAME');
if (empty($project_name)) {
  throw new RuntimeException('Unable to determine project name.');
}
$ignore_project_core_version = (bool) getenv('IGNORE_PROJECT_DRUPAL_CORE_VERSION');
$minimum_stability_override = getenv('MINIMUM_STABILITY_OVERRIDE');

$path = 'composer.json';
$json_project = json_decode(file_get_contents($path), TRUE);
if (!empty($minimum_stability_override)) {
  $json_project['minimum-stability'] = $minimum_stability_override;
}
$json_default = default_json($project_name);

if (
  !empty($json_project['require-dev']['drupal/core']) ||
  !empty($json_project['require']['drupal/core']) ||
  !empty($json_project['require-dev']['drupal/core-recommended']) ||
  !empty($json_project['require']['drupal/core-recommended'])
) {
  // The module has set it and we want to ignore it.
  if ($ignore_project_core_version) {
    unset(
      $json_project['require-dev']['drupal/core'],
      $json_project['require']['drupal/core'],
      $json_project['require-dev']['drupal/core-recommended'],
      $json_project['require']['drupal/core-recommended']
    );
  }
  // The module has set it and we do not want to ignore it.
  else {
    // Unset core-recommended.
    unset($json_default['require-dev']['drupal/core-recommended']);

    // And set core-dev and core-composer-scaffold to the same value as core.
    $core_value = $json_project['require-dev']['drupal/core'] ?? $json_project['require']['drupal/core'] ?? $json_project['require-dev']['drupal/core-recommended'] ?? $json_project['require']['drupal/core-recommended'];
    print "Project composer.json specifies core {$core_value}\n";
    $json_project['require-dev']['drupal/core-composer-scaffold'] = $core_value;
    $json_project['require-dev']['drupal/core-dev'] = $core_value;
  }
}

// Conditionally add prophecy.
$core_version = str_replace(['^', '~'], ['', ''], getenv('DRUPAL_CORE'));
print "core_version=$core_version\n";
if (
  !isset($json_project['require-dev']['phpspec/prophecy-phpunit']) &&
  (version_compare($core_version, '9', '>=') && version_compare($core_version, '10', '<'))
) {
  $json_default['require-dev']['phpspec/prophecy-phpunit'] = '^2';
}

// Do not add "packages.drupal.org" twice if it is added by the module.
// This can happen if a module wants to use a fork and exclude some
// canonical projects via the "exclude" section in favor of their forks.
if (!empty($json_project['repositories']) && is_array($json_project['repositories'])) {
  $packages_drupal_org_found = FALSE;
  foreach ($json_project['repositories'] as $repository) {
    if (isset($repository['url']) && $repository['url'] == 'https://packages.drupal.org/8') {
      $packages_drupal_org_found = TRUE;
    }
  }
  if ($packages_drupal_org_found) {
    unset($json_default['repositories']['drupal']);
  }
}

// Merge the default and the project composer.json.
$json_rich = merge_deep($json_default, $json_project);

// The order of the 'repositories' entry values is important, so prioritize the
// module's values first, if defined.
$json_rich['repositories'] = merge_deep($json_project['repositories'] ?? [], $json_default['repositories'] ?? []);

// Likewise for 'installer-paths' prioritize the project's values first.
$json_rich['extra']['installer-paths'] = merge_deep($json_project['extra']['installer-paths'] ?? [], $json_default['extra']['installer-paths']);

// Add allowed modules for the lenient plugin if defined. Use the old deprecated
// variable if there nothing in the new variable, to maintain BC.
$lenient_allow_list = getenv('_LENIENT_ALLOW_LIST') ?? getenv('LENIENT_ALLOW_LIST');
if ($lenient_allow_list) {
  print "Using lenient allow list: $lenient_allow_list\n";
  if (!isset($json_rich['extra']['drupal-lenient']['allowed-list'])) {
    $json_rich['extra']['drupal-lenient']['allowed-list'] = [];
  }
  foreach (explode(',', $lenient_allow_list) as $module_name) {
    $json_rich['extra']['drupal-lenient']['allowed-list'][] = 'drupal/' . trim($module_name);
  }
}

// Add a composer patch file if defined. Use the old deprecated variable if
// there nothing in the new variable, to maintain BC.
$composer_patches_file = getenv('_COMPOSER_PATCHES_FILE') ?? getenv('COMPOSER_PATCHES_FILE');
if ($composer_patches_file) {
  print "Using composer patches file: $composer_patches_file\n";
  if (is_file($composer_patches_file) && file_exists($composer_patches_file)) {
    $json_rich['extra']['patches-file'] = $composer_patches_file;
  }
  elseif (is_file('./.gitlab-ci/' . $composer_patches_file) && file_exists('./.gitlab-ci/' . $composer_patches_file)) {
    $json_rich['extra']['patches-file'] = './.gitlab-ci/' . $composer_patches_file;
  }
  elseif (parse_url($composer_patches_file, PHP_URL_SCHEME)) {
    $json_rich['extra']['patches-file'] = $composer_patches_file;
  }
  else {
    print "Patch file is not valid!" . PHP_EOL;
    exit(1);
  }
}

// Remove empty top-level items.
$json_rich = array_filter($json_rich);
$output_file = getenv('COMPOSER') ?: $path;
print "Writing output to {$output_file}" . PHP_EOL;
file_put_contents($output_file, json_encode($json_rich, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));

/**
 * Helper function to treat warnings as errors.
 *
 * If the composer.json file is mal-formed this might only produce a warning,
 * but we want to exit with a code that can be detected in the calling job.
 */
function halt_on_warning($errno, $message, $file, $line) {
  if ($errno === E_WARNING) {
    exit(2);
  }
  // Return FALSE to execute the regular error handler.
  return FALSE;
}

/**
 * Get default composer.json contents.
 */
function default_json(string $project_name): array {
  $drupalConstraint = getenv('DRUPAL_CORE') ?: '^9';
  print "drupalConstraint=$drupalConstraint (from DRUPAL_CORE)\n";
  $webRoot = getenv('_WEB_ROOT') ?: 'web';
  return [
    'name' => 'drupal/' . $project_name,
    'type' => 'drupal-module',
    'description' => 'A description',
    'license' => 'GPL-2.0-or-later',
    'repositories' => [
      'drupal' => [
        'type' => 'composer',
        'url' => 'https://packages.drupal.org/8',
      ],
    ],
    'require' => [],
    'require-dev' => [
      'composer/installers' => '^1 || ^2',
      'drupal/core-composer-scaffold' => $drupalConstraint,
      'cweagans/composer-patches' => '~1.0',
      'drupal/core-recommended' => $drupalConstraint,
      'drupal/core-dev' => $drupalConstraint,
      'php-parallel-lint/php-parallel-lint' => '^1.2',
    ],
    'conflict' => [
      'phpunit/phpunit' => '>=11.5.29 <11.5.31',
    ],
    'minimum-stability' => 'dev',
    'prefer-stable' => TRUE,
    'config' => [
      'process-timeout' => 36000,
      "allow-plugins" => [
        "dealerdirect/phpcodesniffer-composer-installer" => TRUE,
        "composer/installers" => TRUE,
        "cweagans/composer-patches" => TRUE,
        "drupal/core-composer-scaffold" => TRUE,
        "drupalspoons/composer-plugin" => TRUE,
        "phpstan/extension-installer" => TRUE,
      ],
      'audit' => [
        'ignore' => [
          // Add security advisory exceptions to allow legacy test coverage.
          // @see https://www.drupal.org/i/3564269 and https://blog.packagist.com/composer-2-9
          // cspell:disable
          'PKSA-yhcn-xrg3-68b1' => 'Drupal9.5 twig v2.15.4 to v2.15.6',
          'PKSA-2wrf-1xmk-1pky' => 'Drupal9.5 twig v2.15.4 to v2.15.6',
          'PKSA-6319-ffpf-gx66' => 'Drupal9.5 twig v2.15.4 to v2.15.6',
          'PKSA-365x-2zjk-pt47' => 'Drupal 11.1 symfony/http-foundation >=2 <=7.3.7. CVE-2025-64500',
          'PKSA-1gck-s111-yq7g' => 'Drupal 11.1 older versions of Composer < 2.9.3',
          // cspell:enable
        ],
      ],
    ],
    'extra' => [
      'installer-paths' => [
        $webRoot . '/core' => [
          0 => 'type:drupal-core',
        ],
        $webRoot . '/libraries/{$name}' => [
          0 => 'type:drupal-library',
        ],
        $webRoot . '/modules/contrib/{$name}' => [
          0 => 'type:drupal-module',
        ],
        $webRoot . '/profiles/contrib/{$name}' => [
          0 => 'type:drupal-profile',
        ],
        $webRoot . '/themes/contrib/{$name}' => [
          0 => 'type:drupal-theme',
        ],
        'recipes/{$name}' => [
          0 => 'type:drupal-recipe',
        ],
        'drush/{$name}' => [
          0 => 'type:drupal-drush',
        ],
      ],
      'drupal-scaffold' => [
        'locations' => [
          'web-root' => $webRoot . '/',
        ],
      ],
      'drush' => [
        'services' => [
          'drush.services.yml' => '^9 || ^10 || ^11',
        ],
      ],
    ],
  ];
}

/**
 * Deeply merges arrays. Borrowed from Drupal core.
 */
function merge_deep(): array {
  return merge_deep_array(func_get_args());
}

/**
 * Deeply merges arrays. Borrowed from drupal.org/project/core.
 *
 * @param array $arrays
 *   An array of array that will be merged.
 * @param bool $preserve_integer_keys
 *   Whether to preserve integer keys.
 */
function merge_deep_array(array $arrays, bool $preserve_integer_keys = FALSE): array {
  $result = [];
  foreach ($arrays as $array) {
    foreach ($array as $key => $value) {
      if (is_int($key) && !$preserve_integer_keys) {
        $result[] = $value;
      }
      elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
        $result[$key] = merge_deep_array([$result[$key], $value], $preserve_integer_keys);
      }
      else {
        $result[$key] = $value;
      }
    }
  }
  return $result;
}
