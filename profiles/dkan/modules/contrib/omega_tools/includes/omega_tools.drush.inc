<?php

/**
 * Implements hook_drush_command().
 */
function omega_tools_drush_command() {
  $items = array();

  $items['omega-subtheme'] = array(
    'description' => 'Create a Omega subtheme.',
    'arguments' => array(
      'name' => 'The name of your subtheme.',
    ),
    'options' => array(
      'destination' => 'The destination of your subtheme. Defaults to "all" (sites/all/themes).',
      'machine_name' => 'The machine-readable name of your subtheme. This will be auto-generated from the human-readable name if ommited.',
      'base' => 'The base theme that you want to build on. Defaults to "Omega" or the base theme of the starterkit (if provided).',
      'starterkit' => 'The starterkit that your subtheme should use. It must have the same base theme as your subtheme. Defaults to "starterkit_omega_html5" if "--base" is not set either.',
      'enable' => 'Automatically enable the subtheme after creation.',
      'set-default' => 'Automatically enable the subtheme after creation and make it the default theme.',
    ),
    'examples' => array(
      'drush omega-subtheme "My Theme"' => 'Creates a Omega subtheme called "My Theme".',
      'drush omega-subtheme "My Theme" --destination=example.com' => 'Creates a Omega subtheme called "My Theme" in sites/example.com/themes.',
      'drush omega-subtheme "My Theme" --starterkit=starterkit_omega_html5' => 'Uses the HTML5 starterkit to create a Omega subtheme called "My Theme" in sites/all/themes (default).',
      'drush omega-subtheme "My Theme" --base=alpha' => 'Creates a Alpha subtheme called "My Theme" without using a starterkit.',
    ),
  );

  return $items;
}

/**
 * Implements of hook_drush_help().
 */
function omega_tools_drush_help($section) {
  switch ($section) {
    case 'drush:omega-subtheme':
      return dt('This command will create a Omega subtheme.');
  }
}

/**
 * Implements of drush_hook_COMMAND_validate().
 */
function drush_omega_tools_omega_subtheme_validate($name) {  
  system_rebuild_theme_data();
  
  $themes = list_themes();
  $destination = drush_get_option('destination', 'all');
  $machine_name = drush_get_option('machine_name', _omega_tools_transform_theme_name($name));
  $base = drush_get_option('base');
  $starterkit = drush_get_option('starterkit');
  
  drush_set_option('machine_name', $machine_name);
  
  if (!_omega_tools_theme_exists('alpha') || !_omega_tools_theme_exists('omega')) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('Where is the Omega base theme? I has not found it. :('));
  }
  
  if (!$machine_name || !preg_match('/^[a-z][a-z0-9_]*$/', $machine_name)) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('The machine name is invalid or could not be generated properly. It may only contain lowercase numbers, letters and underscores and must start with a letter. Please provide a proper machine name by using "--machine_name".'));
  }
  
  if (_omega_tools_theme_exists($machine_name)) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('A theme with that name already exists. The machine-readable name must be unique.'));
  }
  
  if (!in_array($destination, omega_tools_sites())) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('The destination is invalid.'));
  }
  
  if ($starterkit && (!_omega_tools_theme_exists($starterkit) || !_omega_tools_is_starterkit($starterkit))) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('There is no valid starterkit with the name !starterkit.', array('!starterkit' => $starterkit)));
  }
  
  if (!$base && $starterkit) {
    $base = $themes[$starterkit]->info['base theme'];    
    drush_set_option('base', $base);
  }
  
  if ($base && !array_key_exists($base, omega_tools_base_themes())) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('There is no base theme with the name !base.', array('!base' => $base)));
  }
  
  if ($starterkit && $themes[$starterkit]->info['base theme'] != $base) {
    return drush_set_error('OMEGA_TOOLS_THEME_ERROR', dt('The base theme of the selected starterkit does not match the selected base theme.'));
  }
  
  if (!$base && !$starterkit) {
    drush_set_option('base', 'omega');
    drush_set_option('starterkit', 'starterkit_omega_html5');
  }
  else if (!$base) {
    drush_set_option('base', 'omega');
  }
}

/**
 * Implements of drush_hook_COMMAND().
 */
function drush_omega_tools_omega_subtheme($name) {
  $subtheme = new stdClass();
  $subtheme->new = TRUE;
  $subtheme->name = $name;
  $subtheme->machine_name = drush_get_option('machine_name');
  $subtheme->starterkit = drush_get_option('starterkit');
  $subtheme->path = 'sites/' . drush_get_option('destination', 'all') . '/themes/' . $subtheme->machine_name;
  $subtheme->base = drush_get_option('base');
  $subtheme->default = drush_get_option('set-default') !== NULL;
  $subtheme->status = $subtheme->default || drush_get_option('enable') !== NULL;
  
  omega_tools_subtheme_create($subtheme);
  omega_tools_subtheme_process($subtheme);
}