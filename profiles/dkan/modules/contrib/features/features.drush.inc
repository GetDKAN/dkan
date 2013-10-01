<?php

/**
 * @file
 * Features module drush integration.
 */

/**
 * Implements hook_drush_command().
 *
 * @return
 *   An associative array describing your command(s).
 *
 * @see drush_parse_command()
 */
function features_drush_command() {
  $items = array();

  $items['features-list'] = array(
    'description' => "List all the available features for your site.",
    'options' => array(
      'status' => "Feature status, can be 'enabled', 'disabled'  or 'all'",
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fl', 'features'),
  );
  $items['features-export'] = array(
    'description' => "Export a feature from your site into a module.",
    'arguments' => array(
      'feature' => 'Feature name to export.',
      'components' => 'Patterns of components to include, see features-components for the format of patterns.'
    ),
    'options' => array(
      'destination' => "Destination path (from Drupal root) of the exported feature. Defaults to 'sites/all/modules/features'",
      'version-set' => "Specify a version number for the feature.",
      'version-increment' => "Increment the feature's version number.",
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fe'),
  );
  $items['features-add'] = array(
    'description' => "Add a component to a feature module. (DEPRECATED: use features-export)",
    'arguments' => array(
      'feature' => 'Feature name to add to.',
      'components' => 'List of components to add.',
    ),
    'options' => array(
      'version-set' => "Specify a version number for the feature.",
      'version-increment' => "Increment the feature's version number.",
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fa'),
  );
  $items['features-components'] = array(
    'description' => 'List features components.',
    'arguments' => array(
      'patterns' => 'The features components type to list. Omit this argument to list all components.',
    ),
    'options' => array(
      'exported' => array(
        'description' => 'Show only components that have been exported.',
      ),
      'not-exported' => array(
        'description' => 'Show only components that have not been exported.',
      ),
    ),
    'aliases' => array('fc'),
  );
  $items['features-update'] = array(
    'description' => "Update a feature module on your site.",
    'arguments' => array(
      'feature' => 'A space delimited list of features.',
    ),
    'options' => array(
      'version-set' => "Specify a version number for the feature.",
      'version-increment' => "Increment the feature's version number.",
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fu'),
  );
  $items['features-update-all'] = array(
    'description' => "Update all feature modules on your site.",
    'arguments' => array(
      'feature_exclude' => 'A space-delimited list of features to exclude from being updated.',
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fu-all', 'fua'),
  );
  $items['features-revert'] = array(
    'description' => "Revert a feature module on your site.",
    'arguments' => array(
      'feature' => 'A space delimited list of features or feature.component pairs.',
    ),
    'options' => array(
      'force' => "Force revert even if Features assumes components' state are default.",
    ),
    'examples' => array(
      'drush fr foo.node foo.taxonomy bar' => 'Revert node and taxonomy components of feature "foo", but only if they are overriden. Revert all overriden components of feature "bar".',
      'drush fr foo.node foo.taxonomy bar --force' => 'Revert node and taxonomy components of feature "foo". Revert all components of feature "bar".',
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fr'),
  );
  $items['features-revert-all'] = array(
    'description' => "Revert all enabled feature module on your site.",
    'arguments' => array(
      'feature_exclude' => 'A space-delimited list of features to exclude from being reverted.',
    ),
    'options' => array(
      'force' => "Force revert even if Features assumes components' state are default.",
    ),
    'drupal dependencies' => array('features'),
    'aliases' => array('fr-all', 'fra'),
  );
  $items['features-diff'] = array(
    'description' => "Show the difference between the default and overridden state of a feature.",
    'arguments' => array(
      'feature' => 'The feature in question.',
    ),
    'options' => array(
      'lines' => 'Generate diffs with <n> lines of context instead of the usual two.',
    ),
    'drupal dependencies' => array('features', 'diff'),
    'aliases' => array('fd'),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function features_drush_help($section) {
  switch ($section) {
    case 'drush:features':
      return dt("List all the available features for your site.");
    case 'drush:features-export':
      return dt("Export a feature from your site into a module. If called with no arguments, display a list of available components. If called with a single argument, attempt to create a feature including the given component with the same name. The option '--destination=foo' may be used to specify the path (from Drupal root) where the feature should be created. The default destination is 'sites/all/modules/features'. The option '--version-set=foo' may be used to specify a version number for the feature or the option '--version-increment' may also to increment the feature's version number.");
    case 'drush:features-components':
      return dt("List feature components matching patterns. The listing may be limited to exported/not-exported components.

A component pattern consists of a source, a colon and a component. Both source and component may be a full name (as in \"dependencies\"), a shorthand (for instance \"dep\") or a pattern (like \"%denci%\").

Shorthands are unique shortenings of a name. They will only match if exactly one option contains the shorthand. So in a standard installation, \"dep\" will work for dependencies, but \"user\" wont, as it matches both user_permission and user_role.

Patterns uses * or % for matching multiple sources/components. Unlike shorthands, patterns must match the whole name, so \"field:%article%\" should be used to select all fields containing \"article\" (which could both be those on the node type article, as well as those fields named article). * and % are equivalent, but the latter doesn't have to be escaped in UNIX shells.

Lastly, a pattern without a colon is interpreted as having \":%\" appended, for easy listing of all components of a source.
");
    case 'drush:features-update':
      return dt("Update a feature module on your site. The option '--version-set=foo' may be used to specify a version number for the feature or the option '--version-increment' may also to increment the feature's version number.");
    case 'drush:features-update-all':
      return dt("Update all feature modules on your site.");
    case 'drush:features-revert':
      return dt("Revert a feature module on your site.");
    case 'drush:features-revert-all':
      return dt("Revert all enabled feature module on your site.");
    case 'drush:features-diff':
      return dt("Show a diff of a feature module.");
    case 'drush:features-add':
      return dt("Add a component to a feature module. The option '--version-set=foo' may be used to specify a version number for the feature or the option '--version-increment' may also to increment the feature's version number.");
  }
}

/**
 * Get a list of all feature modules.
 */
function drush_features_list() {
  $status = drush_get_option('status') ? drush_get_option('status') : 'all';
  if (!in_array($status, array('enabled', 'disabled', 'all'))) {
    return drush_set_error('', dt('!status is not valid', array('!status' => $status)));
  }

  module_load_include('inc', 'features', 'features.export');
  $rows = array(array(dt('Name'), dt('Feature'), dt('Status'), dt('Version'), dt('State')));

  // Sort the Features list before compiling the output.
  $features = features_get_features(NULL, TRUE);
  ksort($features);

  foreach ($features as $k => $m) {
    switch (features_get_storage($m->name)) {
      case FEATURES_DEFAULT:
      case FEATURES_REBUILDABLE:
        $storage = '';
        break;
      case FEATURES_OVERRIDDEN:
        $storage = dt('Overridden');
        break;
      case FEATURES_NEEDS_REVIEW:
        $storage = dt('Needs review');
        break;
    }
    if (
      ($m->status == 0 && ($status == 'all' || $status == 'disabled')) ||
      ($m->status == 1 && ($status == 'all' || $status == 'enabled'))
    ) {
      $rows[] = array(
        $m->info['name'],
        $m->name,
        $m->status ? dt('Enabled') : dt('Disabled'),
        $m->info['version'],
        $storage
      );
    }
  }
  drush_print_table($rows, TRUE);
}

/**
 * List components, with pattern matching.
 */
function drush_features_components() {
  $args = func_get_args();
  $components = _drush_features_component_list();
  // If no args supplied, prompt with a list.
  if (empty($args)) {
    $types = array_keys($components);
    array_unshift($types, 'all');
    $choice = drush_choice($types, 'Enter a number to choose which component type to list.');
    if ($choice === FALSE) {
      return;
    }

    $args = ($choice == 0) ? array('*') : array($types[$choice]);
  }
  $options = array(
    'provided by' => TRUE,
  );
  if (drush_get_option(array('exported', 'e'), NULL)) {
    $options['not exported'] = FALSE;
  }
  elseif (drush_get_option(array('not-exported', 'o'), NULL)) {
    $options['exported'] = FALSE;
  }

  $filtered_components = _drush_features_component_filter($components, $args, $options);
  if ($filtered_components){
    _drush_features_component_print($filtered_components);
  }
}

/**
 * Returns a listing of all known components, indexed by source.
 */
function _drush_features_component_list() {
  $components = array();
  foreach (features_get_feature_components() as $source => $info) {
    if ($options = features_invoke($source, 'features_export_options')) {
      foreach ($options as $name => $title) {
        $components[$source][$name] = $title;
      }
    }
  }
  return $components;
}

/**
 * Filters components by patterns.
 */
function _drush_features_component_filter($all_components, $patterns = array(), $options = array()) {
  $options += array(
    'exported' => TRUE,
    'not exported' => TRUE,
    'provided by' => FALSE,
  );
  $pool = array();
  // Maps exported components to feature modules.
  $components_map = features_get_component_map();
  // First filter on exported state.
  foreach ($all_components as $source => $components) {
    foreach ($components as $name => $title) {
      $exported = sizeof($components_map[$source][$name]) > 0;
      if ($exported) {
        if ($options['exported']) {
          $pool[$source][$name] = $title;
        }
      }
      else {
        if ($options['not exported']) {
          $pool[$source][$name] = $title;
        }
      }
    }
  }

  $state_string = '';

  if (!$options['exported']) {
    $state_string = 'unexported';
  }
  elseif (!$options['not exported']) {
    $state_string = 'exported';
  }

  $selected = array();
  foreach ($patterns as $pattern) {
    // Rewrite * to %. Let users use both as wildcard.
    $pattern = strtr($pattern, array('*' => '%'));
    $sources = array();
    list($source_pattern, $component_pattern) = explode(':', $pattern, 2);
    // If source is empty, use a pattern.
    if ($source_pattern == '') {
      $source_pattern = '%';
    }
    if ($component_pattern == '') {
      $component_pattern = '%';
    }

    $preg_source_pattern = strtr(preg_quote($source_pattern, '/'), array('%' => '.*'));
    $preg_component_pattern = strtr(preg_quote($component_pattern, '/'), array('%' => '.*'));
    /*
     * If it isn't a pattern, but a simple string, we don't anchor the
     * pattern, this allows for abbreviating. Else, we do, as this seems more
     * natural for patterns.
     */
    if (strpos($source_pattern, '%') !== FALSE) {
      $preg_source_pattern = '^' . $preg_source_pattern . '$';
    }
    if (strpos($component_pattern, '%') !== FALSE) {
      $preg_component_pattern = '^' . $preg_component_pattern . '$';
    }
    $matches = array();

    // Find the sources.
    $all_sources = array_keys($pool);
    $matches = preg_grep('/' . $preg_source_pattern . '/', $all_sources);
    if (sizeof($matches) > 0) {
      // If we have multiple matches and the source string wasn't a
      // pattern, check if one of the matches is equal to the pattern, and
      // use that, or error out.
      if (sizeof($matches) > 1 and $preg_source_pattern[0] != '^') {
        if (in_array($source_pattern, $matches)) {
          $matches = array($source_pattern);
        }
        else {
          return drush_set_error('', dt('Ambiguous source "!source", matches !matches', array('!source' => $source_pattern, '!matches' => join(', ', $matches))));
        }
      }
      // Loose the indexes preg_grep preserved.
      $sources = array_values($matches);
    }
    else {
      return drush_set_error('', dt('No !state sources match "!source"', array('!state' => $state_string, '!source' => $source_pattern)));
    }


    // Now find the components.
    foreach ($sources as $source) {
      // Find the components.
      $all_components = array_keys($pool[$source]);
      // See if there's any matches.
      $matches = preg_grep('/' . $preg_component_pattern . '/', $all_components);
      if (sizeof($matches) > 0) {
        // If we have multiple matches and the components string wasn't a
        // pattern, check if one of the matches is equal to the pattern, and
        // use that, or error out.
        if (sizeof($matches) > 1 and $preg_component_pattern[0] != '^') {
          if (in_array($component_pattern, $matches)) {
            $matches = array($component_pattern);
          }
          else {
            return drush_set_error('', dt('Ambiguous component "!component", matches !matches', array('!component' => $component_pattern, '!matches' => join(', ', $matches))));
          }
        }
        if (!is_array($selected[$source])) {
          $selected[$source] = array();
        }
        $selected[$source] += array_intersect_key($pool[$source], array_flip($matches));
      }
      else {
        // No matches. If the source was a pattern, just carry on, else
        // error out. Allows for patterns like :*field*
        if ($preg_source_pattern[0] != '^') {
          return drush_set_error('', dt('No !state !source components match "!component"', array('!state' => $state_string, '!component' => $component_pattern, '!source' => $source)));
        }
      }
    }
  }

  // Lastly, provide feature module information on the selected components, if
  // requested.
  $provided_by = array();
  if ($options['provided by'] && $options['exported'] ) {
    foreach ($selected as $source => $components) {
      foreach ($components as $name => $title) {
        $exported = sizeof($components_map[$source][$name]) > 0;
        if ($exported) {
          $provided_by[$source . ':' . $name] = join(', ', $components_map[$source][$name]);
        }
      }
    }
  }

  return array(
    'components' => $selected,
    'sources' => $provided_by,
  );
}

/**
 * Prints a list of filtered components.
 */
function _drush_features_component_print($filtered_components) {
  $rows = array(array(dt('Available sources')));
  foreach ($filtered_components['components'] as $source => $components) {
    foreach ($components as $name => $value) {
      $row = array($source .':'. $name);
      if (isset($filtered_components['sources'][$source .':'. $name])) {
        $row[] = dt('Provided by') . ': ' . $filtered_components['sources'][$source .':'. $name];
      }
      $rows[] = $row;
    }
  }

  drush_print_table($rows, TRUE);
}

/**
 * Add a component to a features module, or create a new module with
 * the selected components.
 */
function drush_features_export() {
  if ($args = func_get_args()) {
    $module = array_shift($args);
    if (empty($args)) {
      return drush_set_error('', 'No components supplied.');
    }
    $components = _drush_features_component_list();
    $options = array(
      'exported' => FALSE,
    );

    $filtered_components = _drush_features_component_filter($components, $args, $options);
    $items = $filtered_components['components'];

    if (empty($items)) {
      return drush_set_error('', 'No components to add.');
    }

    $items = array_map('array_keys', $items);

    if (($feature = features_load_feature($module, TRUE)) && module_exists($module)) {
      module_load_include('inc', 'features', 'features.export');
      _features_populate($items, $feature->info, $feature->name);
      _drush_features_export($feature->info, $feature->name, dirname($feature->filename));
    }
    elseif ($feature) {
      _features_drush_set_error($module, 'FEATURES_FEATURE_NOT_ENABLED');
    }
    else {
      // Same logic as in _drush_features_export. Should be refactored.
      $destination = drush_get_option(array('destination'), 'sites/all/modules/features');
      $directory = isset($directory) ? $directory : $destination . '/' . $module;
      drush_print(dt('Will create a new module in !dir', array('!dir' => $directory)));
      if (!drush_confirm(dt('Do you really want to continue?'))) {
        drush_die('Aborting.');
      }
      $export = _drush_features_generate_export($items, $module);
      _features_populate($items, $export[info], $export[name]);
      _drush_features_export($export['info'], $module, $directory);
    }
  }
  else {
    return drush_set_error('', 'No feature name given.');
  }
}

/**
 * Add a component to a features module
 * the selected components.
 *
 * This is DEPRECATED, but keeping it around for a bit longer for user migration
 */
function drush_features_add() {
  drush_print(dt('features-add is DEPRECATED.'));
  drush_print(dt('Calling features-export instead.'));
  drush_features_export();
}


/**
 * Update an existing feature module.
 */
function drush_features_update() {
  if ($args = func_get_args()) {
    foreach ($args as $module) {
      if (($feature = features_load_feature($module, TRUE)) && module_exists($module)) {
        _drush_features_export($feature->info, $feature->name, dirname($feature->filename));
      }
      else if ($feature) {
        _features_drush_set_error($module, 'FEATURES_FEATURE_NOT_ENABLED');
      }
      else {
        _features_drush_set_error($module);
      }
    }
  }
  else {
    // By default just show contexts that are available.
    $rows = array(array(dt('Available features')));
    foreach (features_get_features(NULL, TRUE) as $name => $info) {
      $rows[] = array($name);
    }
    drush_print_table($rows, TRUE);
  }
}

/**
 * Update all enabled features. Optionally pass in a list of features to
 * exclude from being updated.
 */
function drush_features_update_all() {
  $features_to_update = array();
  $features_to_exclude = func_get_args();
  foreach (features_get_features() as $module) {
    if ($module->status && !in_array($module->name, $features_to_exclude)) {
      $features_to_update[] = $module->name;
    }
  }
  drush_print(dt('The following modules will be updated: !modules', array('!modules' => implode(', ', $features_to_update))));
  if (drush_confirm(dt('Do you really want to continue?'))) {
    foreach ($features_to_update as $module_name) {
      drush_invoke_process(drush_sitealias_get_record('@self'), 'features-update', array($module_name));
    }
  }
  else {
    drush_die('Aborting.');
  }
}

/**
 * Write a module to the site dir.
 *
 * @param $info
 *   The feature info associative array.
 * @param $module_name
 *  Optional. The name for the exported module.
 */
function _drush_features_export($info, $module_name = NULL, $directory = NULL) {
  $root = drush_get_option(array('r', 'root'), drush_locate_root());
  if ($root) {
    $destination = drush_get_option(array('destination'), 'sites/all/modules/features');
    $directory = isset($directory) ? $directory : $destination . '/' . $module_name;
    if (is_dir($directory)) {
      drush_print(dt('Module appears to already exist in !dir', array('!dir' => $directory)));
      if (!drush_confirm(dt('Do you really want to continue?'))) {
        drush_die('Aborting.');
      }
    }
    else {
      drush_op('mkdir', $directory);
    }
    if (is_dir($directory)) {
      drupal_flush_all_caches();
      $export = _drush_features_generate_export($info, $module_name);
      $files = features_export_render($export, $module_name, TRUE);
      foreach ($files as $extension => $file_contents) {
        if (!in_array($extension, array('module', 'info'))) {
          $extension .= '.inc';
        }
        drush_op('file_put_contents', "{$directory}/{$module_name}.$extension", $file_contents);
      }
      drush_log(dt("Created module: !module in !directory", array('!module' => $module_name, '!directory' => $directory)), 'ok');
    }
    else {
      drush_die(dt('Couldn\'t create directory !directory', array('!directory' => $directory)));
    }
  }
  else {
    drush_die(dt('Couldn\'t locate site root'));
  }
}

/**
 * Helper function for _drush_feature_export.
 *
 * @param $info
 *   The feature info associative array.
 * @param $module_name
 *  Optional. The name for the exported module.
 */
function _drush_features_generate_export(&$info, &$module_name) {
  module_load_include('inc', 'features', 'features.export');
  $export = features_populate($info, $module_name);
  if (!features_load_feature($module_name)) {
    $export['name'] = $module_name;
  }
  // Set the feature version if the --version-set or --version-increment option is passed.
  if ($version = drush_get_option(array('version-set'))) {
    preg_match('/^(?P<core>\d+\.x)-(?P<major>\d+)\.(?P<patch>\d+)-?(?P<extra>\w+)?$/', $version, $matches);
    if (!isset($matches['core'], $matches['major'])) {
      drush_die(dt('Please enter a valid version with core and major version number. Example: !example', array('!example' => '7.x-1.0')));
    }
    $export['version'] = $version;
  }
  else if ($version = drush_get_option(array('version-increment'))) {
    // Determine current version and increment it.
    $export_load = features_export_prepare($export, $module_name);
    $version = $export_load['version'];
    $version_explode = explode('.', $version);
    $version_minor = array_pop($version_explode);
    // Increment minor version number if numeric or not a dev release.
    if (is_numeric($version_minor) || strpos($version_minor, 'dev') !== (strlen($version_minor) - 3)) {
      // Check for prefixed versions (alpha, beta, rc).
      if (ctype_digit($version_minor)) {
        ++$version_minor;
      }
      else {
        // Split version number parts.
        $pattern = '/([0-9]-[a-z]+([0-9])+)/';
        $matches = array();
        preg_match($pattern, $version_minor, $matches);
        $number = array_pop($matches);
        ++$number;
        $pattern = '/[0-9]+$/';
        $version_minor = preg_replace($pattern, $number, $version_minor);
      }
    }
    array_push($version_explode, $version_minor);
    // Rebuild version string.
    $version = implode('.', $version_explode);
    $export['version'] = $version;
  }
  return $export;
}

/**
 * Revert a feature to it's code definition.
 * Optionally accept a list of components to revert.
 */
function drush_features_revert() {
  if ($args = func_get_args()) {
    module_load_include('inc', 'features', 'features.export');
    features_include();

    // Determine if revert should be forced.
    $force = drush_get_option('force');

    // Parse list of arguments.
    $modules = array();
    foreach ($args as $arg) {
      $arg = explode('.', $arg);
      $module = array_shift($arg);
      $component = array_shift($arg);

      if (isset($module)) {
        if (empty($component)) {
          // If we received just a feature name, this means that we need all of it's components.
          $modules[$module] = TRUE;
        }
        elseif ($modules[$module] !== TRUE) {
          if (!isset($modules[$module])) {
            $modules[$module] = array();
          }
          $modules[$module][] = $component;
        }
      }
    }

    // Process modules.
    foreach ($modules as $module => $components_needed) {
      if (($feature = features_load_feature($module, TRUE)) && module_exists($module)) {

        $components = array();
        // Forcefully revert all components of a feature.
        if ($force) {
          foreach (array_keys($feature->info['features']) as $component) {
            if (features_hook($component, 'features_revert')) {
              $components[] = $component;
            }
          }
        }
        // Only revert components that are detected to be Overridden/Needs review/rebuildable.
        else {
          $states = features_get_component_states(array($feature->name), FALSE);
          foreach ($states[$feature->name] as $component => $state) {
            if (in_array($state, array(FEATURES_OVERRIDDEN, FEATURES_NEEDS_REVIEW, FEATURES_REBUILDABLE)) && features_hook($component, 'features_revert')) {
              $components[] = $component;
            }
          }
        }

        if (!empty($components_needed) && is_array($components_needed)) {
          $components = array_intersect($components, $components_needed);
        }
        if (empty($components)) {
          drush_log(dt('Current state already matches defaults, aborting.'), 'ok');
        }
        else {
          foreach ($components as $component) {
            if (drush_confirm(dt('Do you really want to revert @component?', array('@component' => $component)))) {
              features_revert(array($module => array($component)));
              drush_log(dt('Reverted @component.', array('@component' => $component)), 'ok');
            }
            else {
              drush_log(dt('Skipping @component.', array('@component' => $component)), 'ok');
            }
          }
        }
      }
      else if ($feature) {
        _features_drush_set_error($module, 'FEATURES_FEATURE_NOT_ENABLED');
      }
      else {
        _features_drush_set_error($module);
      }
    }
  }
  else {
    drush_features_list();
    return;
  }
}

/**
 * Revert all enabled features to their definitions in code.
 *
 * @param ...
 *   (Optional) A list of features to exclude from being reverted.
 */
function drush_features_revert_all() {
  module_load_include('inc', 'features', 'features.export');
  $force = drush_get_option('force');
  $features_to_exclude = func_get_args();

  $features_to_revert = array();
  foreach (features_get_features(NULL, TRUE) as $module) {
    if ($module->status && !in_array($module->name, $features_to_exclude)) {
      // If forced, add module regardless of status.
      if ($force) {
        $features_to_revert[] = $module->name;
      }
      else {
        switch (features_get_storage($module->name)) {
          case FEATURES_OVERRIDDEN:
          case FEATURES_NEEDS_REVIEW:
          case FEATURES_REBUILDABLE:
            $features_to_revert[] = $module->name;
            break;
        }
      }
    }
  }

  if ($features_to_revert) {
    drush_print(dt('The following modules will be reverted: !modules', array('!modules' => implode(', ', $features_to_revert))));
    if (drush_confirm(dt('Do you really want to continue?'))) {
      foreach ($features_to_revert as $module) {
        drush_invoke_process(drush_sitealias_get_record('@self'), 'features-revert', array($module), array('force' => $force, '#integrate' => TRUE));
      }
    }
    else {
      return drush_user_abort('Aborting.');
    }
  }
  else {
    drush_log(dt('Current state already matches defaults, aborting.'), 'ok');
  }
}

/**
 * Show the diff of a feature module.
 */
function drush_features_diff() {
  if (!$args = func_get_args()) {
    drush_features_list();
    return;
  }
  $module = $args[0];
  $feature = features_load_feature($module);
  if (!module_exists($module)) {
    drush_log(dt('No such feature is enabled: ' . $module), 'error');
    return;
  }
  module_load_include('inc', 'features', 'features.export');
  $overrides = features_detect_overrides($feature);
  if (empty($overrides)) {
    drush_log(dt('Feature is in its default state. No diff needed.'), 'ok');
    return;
  }
  module_load_include('inc', 'diff', 'diff.engine');

  if (!class_exists('DiffFormatter')) {
    if (drush_confirm(dt('It seems that the Diff module is not available. Would you like to download and enable it?'))) {
      // Download it if it's not already here.
      $project_info = drush_get_projects();
      if (empty($project_info['diff']) && !drush_invoke_process(drush_sitealias_get_record('@self'), 'dl', array('diff'), array('#integrate' => TRUE))) {
        return drush_set_error(dt('Diff module could not be downloaded.'));
      }

      if (!drush_invoke_process(drush_sitealias_get_record('@self'), 'en', array('diff'), array('#integrate' => TRUE))) {
        return drush_set_error(dt('Diff module could not be enabled.'));
      }
    }
    else {
      return drush_set_error(dt('Diff module is not enabled.'));
    }
    // If we're still here, now we can include the diff.engine again.
    module_load_include('inc', 'diff', 'diff.engine');
  }

  $lines = (int) drush_get_option('lines');
  $lines = $lines > 0 ? $lines : 2;

  $formatter = new DiffFormatter();
  $formatter->leading_context_lines = $lines;
  $formatter->trailing_context_lines = $lines;
  $formatter->show_header = FALSE;

  if (drush_get_context('DRUSH_NOCOLOR')) {
    $red = $green = "%s";
  }
  else {
    $red = "\033[31;40m\033[1m%s\033[0m";
    $green = "\033[0;32;40m\033[1m%s\033[0m";
  }

  // Print key for colors
  drush_print(dt('Legend: '));
  drush_print(sprintf($red,   dt('Code:       drush features-revert will remove the overrides.')));
  drush_print(sprintf($green, dt('Overrides:  drush features-update will update the exported feature with the displayed overrides')));
  drush_print();

  foreach ($overrides as $component => $items) {
    $diff = new Diff(explode("\n", $items['default']), explode("\n", $items['normal']));
    drush_print();
    drush_print(dt("Component: !component", array('!component' => $component)));
    $rows = explode("\n", $formatter->format($diff));
    foreach ($rows as $row) {
      if (strpos($row, '>') === 0) {
        drush_print(sprintf($green, $row));
      }
      elseif (strpos($row, '<') === 0) {
        drush_print(sprintf($red, $row));
      }
      else {
        drush_print($row);
      }
    }
  }
}

/**
 * Helper function to call drush_set_error().
 *
 * @param $feature
 *   The string name of the feature.
 * @param $error
 *   A text string identifying the type of error.
 * @return
 *   FALSE.  See drush_set_error().
 */
function _features_drush_set_error($feature, $error = '') {
  $args = array('!feature' => $feature);

  switch ($error) {
    case 'FEATURES_FEATURE_NOT_ENABLED':
      $message = 'The feature !feature is not enabled.';
      break;
    case 'FEATURES_COMPONENT_NOT_FOUND':
      $message = 'The given component !feature could not be found.';
      break;
    default:
      $error = 'FEATURES_FEATURE_NOT_FOUND';
      $message = 'The feature !feature could not be found.';
  }

  return drush_set_error($error, dt($message, $args));
}
