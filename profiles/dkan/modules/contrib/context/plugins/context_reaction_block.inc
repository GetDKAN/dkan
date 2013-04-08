<?php

/**
 * Expose blocks as context reactions.
 */
class context_reaction_block extends context_reaction {
  /**
   * Options form.
   */
  function options_form($context) {
    // Rebuild the block info cache if necessary.
    $this->get_blocks(NULL, NULL, $this->rebuild_needed());
    $this->rebuild_needed(FALSE);

    $theme_key = variable_get('theme_default', 'garland');
    $weight_delta = $this->max_block_weight();

    $form = array(
      '#tree' => TRUE,
      '#theme' => 'context_block_form',
      'max_block_weight' => array(
        '#value' => $weight_delta,
        '#type' => 'value',
      ),
      'state' => array(
        '#type' => 'hidden',
        '#attributes' => array('class' => 'context-blockform-state'),
      ),
    );

    /**
     * Selector.
     */
    $modules = module_list();
    $form['selector'] = array(
      '#type' => 'item',
      '#tree' => TRUE,
      '#prefix' => '<div class="context-blockform-selector">',
      '#suffix' => '</div>',
    );
    foreach ($this->get_blocks() as $block) {
      $group = isset($block->context_group) ? $block->context_group : $block->module;
      if (!isset($form['selector'][$group])) {
        $form['selector'][$group] = array(
          '#type' => 'checkboxes',
          '#title' => isset($block->context_group) ? $block->context_group : $modules[$block->module],
          '#options' => array(),
        );
      }
      $form['selector'][$group]['#options'][$block->bid] = check_plain($block->info);
    }
    ksort($form['selector']);

    /**
     * Regions.
     */
    $form['blocks'] = array(
      '#tree' => TRUE,
      '#theme' => 'context_block_regions_form',
    );
    foreach (system_region_list($theme_key, REGIONS_VISIBLE) as $region => $label) {
      $form['blocks'][$region] = array(
        '#type' => 'item',
        '#title' => $label,
        '#tree' => TRUE,
      );
      foreach ($this->get_blocks($region, $context) as $block) {
        if (!empty($block->context)) {
          $form['blocks'][$region][$block->bid] = array(
            '#value' => check_plain($block->info),
            '#weight' => $block->weight,
            '#type' => 'markup',
            '#tree' => TRUE,
            'weight' => array('#type' => 'weight', '#delta' => $weight_delta, '#default_value' => $block->weight),
          );
        }
      }
    }
    return $form;
  }

  /**
   * Options form submit handler.
   */
  function options_form_submit($values) {
    $blocks = array();
    $block_info = $this->get_blocks();

    // Retrieve blocks from submitted JSON string.
    if (!empty($values['state'])) {
      $edited = $this->json_decode($values['state']);
    }
    else {
      $edited = array();
    }

    foreach ($edited as $region => $block_data) {
      foreach ($block_data as $position => $data) {
        if (isset($block_info[$data->bid])) {
          $blocks[$data->bid] = array(
            'module' => $block_info[$data->bid]->module,
            'delta' => $block_info[$data->bid]->delta,
            'region' => $region,
            'weight' => $data->weight,
          );
        }
      }
    }
    return array('blocks' => $blocks);
  }

  /**
   * Context editor form for blocks.
   */
  function editor_form($context) {
    $form = array();
    drupal_add_library('system', 'ui.droppable');
    drupal_add_library('system', 'ui.sortable');
    drupal_add_js(drupal_get_path('module', 'context_ui') . '/json2.js');
    drupal_add_js(drupal_get_path('module', 'context_ui') . '/theme/filter.js');
    drupal_add_js(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.js');
    drupal_add_css(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.css');

    // We might be called multiple times so use a static to ensure this is set just once.
    static $once;
    if (!isset($once)) {
      $settings = array(
        'path' => drupal_is_front_page() ? base_path() : url($_GET['q']),
        'params' => (object) array_diff_key($_GET, array('q' => '')),
        'scriptPlaceholder' => theme('context_block_script_placeholder', array('text' => '')),
      );
      drupal_add_js(array('contextBlockEditor' => $settings), 'setting');
      $once = TRUE;
    }

    $form['state'] = array(
      '#type' => 'hidden',
      '#attributes' => array('class' => array('context-block-editor-state')),
    );
    $form['browser'] = array(
      '#markup' => theme('context_block_browser', array(
        'blocks' => $this->get_blocks(NULL, NULL, $this->rebuild_needed()),
        'context' => $context
      )),
    );
    $this->rebuild_needed(FALSE);
    return $form;
  }

  /**
   * Submit handler context editor form.
   */
  function editor_form_submit(&$context, $values) {
    $edited = !empty($values['state']) ? (array) $this->json_decode($values['state']) : array();

    $options = array();

    // Take the existing context values and remove blocks that belong affected regions.
    $affected_regions = array_keys($edited);
    if (!empty($context->reactions['block']['blocks'])) {
      $options = $context->reactions['block'];
      foreach ($options['blocks'] as $key => $block) {
        if (in_array($block['region'], $affected_regions)) {
          unset($options['blocks'][$key]);
        }
      }
    }

    // Iterate through blocks and add in the ones that belong to the context.
    foreach ($edited as $region => $blocks) {
      foreach ($blocks as $weight => $block) {
        if ($block->context === $context->name) {
          $split = explode('-', $block->bid);
          $options['blocks'][$block->bid] = array(
            'module' => array_shift($split),
            'delta' => implode('-', $split),
            'region' => $region,
            'weight' => $weight,
          );
        }
      }
    }

    return $options;
  }

  /**
   * Settings form for variables.
   */
  function settings_form() {
    $form = array();
    $form['context_reaction_block_all_regions'] = array(
      '#title' => t('Show all regions'),
      '#type' => 'checkbox',
      '#default_value' => variable_get('context_reaction_block_all_regions', FALSE),
      '#description' => t('Show all regions including those that are empty. Enable if you are administering your site using the inline editor.')
    );
    return $form;
  }

  /**
   * Execute.
   */
  function execute(&$page) {
    global $theme;

    // The theme system might not yet be initialized. We need $theme.
    drupal_theme_initialize();

    // If the context_block querystring param is set, switch to AJAX rendering.
    // Note that we check the output buffer for any content to ensure that we
    // are not in the middle of a PHP template render.
    if (isset($_GET['context_block']) && !ob_get_contents()) {
      return $this->render_ajax($_GET['context_block']);
    }

    // Populate all block regions
    $all_regions = system_region_list($theme);

    // Load all region content assigned via blocks.
    foreach (array_keys($all_regions) as $region) {
      if ($this->is_enabled_region($region)) {
        if ($blocks = $this->block_get_blocks_by_region($region)) {

          // Are the blocks already sorted.
          $blocks_sorted = TRUE;

          // If blocks have already been placed in this region (most likely by
          // Block module), then merge in blocks from Context.
          if (isset($page[$region])) {
            $page[$region] = array_merge($page[$region], $blocks);

            // Restore the weights that Block module manufactured
            // @see _block_get_renderable_array()
            foreach ($page[$region] as &$block) {
              if (isset($block['#block']->weight)) {
                $block['#weight'] = $block['#block']->weight;
                $blocks_sorted = FALSE;
              }
            }
          }
          else {
            $page[$region] = $blocks;
          }

          $page[$region]['#sorted'] = $blocks_sorted;
        }
      }
    }
  }

  /**
   * Return a list of enabled regions for which blocks should be built.
   * Split out into a separate method for easy overrides in extending classes.
   */
  protected function is_enabled_region($region) {
    global $theme;
    $regions = array_keys(system_region_list($theme));
    return in_array($region, $regions, TRUE);
  }

  /**
   * Determine whether inline editing requirements are met and that the current
   * user may edit.
   */
  protected function is_editable_region($region, $reset = FALSE) {
    // Check requirements.
    // Generally speaking, it does not make sense to allow anonymous users to
    // edit a context inline. Though it may be possible to save (and restore)
    // an edited context to an anonymous user's cookie or session, it's an
    // edge case and probably not something we want to encourage anyway.
    static $requirements;
    if (!isset($requirements) || $reset) {
      global $user;
      if ($user->uid) {
        $requirements = TRUE;
        drupal_add_library('system', 'ui.droppable');
        drupal_add_library('system', 'ui.sortable');
        drupal_add_js(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.js');
        drupal_add_css(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.css');
      }
      else {
        $requirements = FALSE;
      }
    }
    // Check that this region is not locked by the theme.
    global $theme;
    $info = system_get_info('theme', $theme);
    if ($info && isset($info['regions_locked']) && in_array($region, $info['regions_locked'])) {
      return FALSE;
    }
    // Check that this region is not hidden
    $visible = system_region_list($theme, REGIONS_VISIBLE);
    return $requirements && $this->is_enabled_region($region) && isset($visible[$region]);
  }

  /**
   * Add markup for making a block editable.
   */
  protected function editable_block($block) {
    if (!empty($block->content)) {
      $block->content = array(
        'content' => $block->content,
        'context' => array('#markup' => "<a id='context-block-{$block->module}-{$block->delta}' class='context-block editable edit-{$block->context}'></a>"),
      );
      //Contextual links are in the wrong spot in the render array once we've nested them
      if (isset($block->content['content']['#contextual_links'])) {
        $block->content['#contextual_links'] = $block->content['content']['#contextual_links'];
        unset($block->content['content']['#contextual_links']);
      }
    }
    else {
      // the block alter in context.module should ensure that blocks are never
      // empty if the inline editor is present but in the case that they are,
      // warn that editing the context is likely to cause this block to be dropped
      drupal_set_message(t('The block with delta @delta from module @module is not compatible with the inline editor and will be dropped from the context containing it if you edit contexts here', array('@delta' => $block->delta, '@module' => $block->module)), 'warning');
    }
    return $block;
  }

  /**
   * Add markup for making a region editable.
   */
  protected function editable_region($region, $build) {
    if ($this->is_editable_region($region) &&
        (!empty($build) ||
         variable_get('context_reaction_block_all_regions', FALSE) ||
         context_isset('context_ui', 'context_ui_editor_present'))
    ) {
      global $theme;
      $regions = system_region_list($theme);
      $name = isset($regions[$region]) ? $regions[$region] : $region;
      // The negative weight + sorted will push our region marker to the top of the region
      $build['context'] = array(
        '#prefix' => "<div class='context-block-region' id='context-block-region-{$region}'>",
        '#markup' => "<span class='region-name'>{$name}</span>" .
                     "<a class='context-ui-add-link'>" . t('Add a block here.') . '</a>',
        '#suffix' => '</div>',
        '#weight' => -100,
      );
      $build['#sorted'] = FALSE;
    }

    return $build;
  }

  /**
  * Get a renderable array of a region containing all enabled blocks.
  */
  function block_get_blocks_by_region($region) {
    module_load_include('module', 'block', 'block');

    $build = array();
    if ($list = $this->block_list($region)) {
      $build = _block_get_renderable_array($list);
    }
    if ($this->is_editable_region($region)) {
      $build = $this->editable_region($region, $build);
    }
    return $build;
  }

  /**
   * An alternative version of block_list() that provides any context enabled blocks.
   */
  function block_list($region) {
    module_load_include('module', 'block', 'block');

    $context_blocks = &drupal_static('context_reaction_block_list');;
    $contexts = context_active_contexts();
    if (!isset($context_blocks)) {
      $info = $this->get_blocks();
      $context_blocks = array();
      foreach ($contexts as $context) {
        $options = $this->fetch_from_context($context);
        if (!empty($options['blocks'])) {
          foreach ($options['blocks'] as $context_block) {
            $bid = "{$context_block['module']}-{$context_block['delta']}";
            if (isset($info[$bid])) {
              $block = (object) array_merge((array) $info[$bid], $context_block);
              $block->context = $context->name;
              $block->title = isset($info[$block->bid]->title) ? $info[$block->bid]->title : NULL;
              $block->cache = isset($info[$block->bid]->cache) ? $info[$block->bid]->cache : DRUPAL_NO_CACHE;
              $context_blocks[$block->region][$block->bid] = $block;
            }
          }
        }
      }
      
      $this->is_editable_check($context_blocks);
      foreach ($context_blocks as $r => $blocks) {
        $context_blocks[$r] = _block_render_blocks($blocks);

        // Make blocks editable if allowed.
        if ($this->is_editable_region($r)) {
          foreach ($context_blocks[$r] as $key => $block) {
            $context_blocks[$r][$key] = $this->editable_block($block);
          }
        }

        // Sort blocks.
        uasort($context_blocks[$r], array('context_reaction_block', 'block_sort'));
      }
    }
    return isset($context_blocks[$region]) ? $context_blocks[$region] : array();
  }

  /**
   * Determine if there is an active context editor block, and set a flag.  We will set a flag so 
   * that we can make sure that blocks with empty content have some default content.  This is
   * needed so the save of the context inline editor does not remove the blocks with no content.
   */
  function is_editable_check($context_blocks) {
    foreach ($context_blocks as $r => $blocks) {
      if (isset($blocks['context_ui-editor'])) {
        $block = $blocks['context_ui-editor'];
        // see if the editor is actually enabled, lifted from _block_render_blocks
        if (!count(module_implements('node_grants')) && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'HEAD') && ($cid = _block_get_cache_id($block)) && ($cache = cache_get($cid, 'cache_block'))) {
          $array = $cache->data;
        }
        else {
          $array = module_invoke($block->module, 'block_view', $block->delta);
          drupal_alter(array('block_view', "block_view_{$block->module}_{$block->delta}"), $array, $block);
        }
        if(!empty($array['content'])) {
          context_set('context_ui', 'context_ui_editor_present', TRUE);
        }
        break;
      }
    }
  }
  
  /**
   * Generate the safe weight range for a block being added to a region such that
   * there are enough potential unique weights to support all blocks.
   */
  protected function max_block_weight() {
    $blocks = $this->get_blocks();
    $block_count = 0;
    foreach ($blocks as $region => $block_list) {
      $block_count += count($block_list);
    }
    // Add 2 to make sure there's space at either end of the block list
    return round(($block_count + 2) / 2);
  }

  /**
   * Check or set whether a rebuild of the block info cache is needed.
   */
  function rebuild_needed($set = NULL) {
    if (isset($set) && $set != variable_get('context_block_rebuild_needed', FALSE)) {
      variable_set('context_block_rebuild_needed', $set);
    }
    return (bool) variable_get('context_block_rebuild_needed', FALSE);
  }

  /**
   * Helper function to generate a list of blocks from a specified region. If provided a context object,
   * will generate a full list of blocks for that region distinguishing between system blocks and
   * context-provided blocks.
   *
   * @param $region
   *   The string identifier for a theme region. e.g. "left"
   * @param $context
   *   A context object.
   *
   * @return
   *   A keyed (by "module_delta" convention) array of blocks.
   */
  function get_blocks($region = NULL, $context = NULL, $reset = FALSE) {
    static $block_info;
    $theme_key = variable_get('theme_default', 'garland');

    if (!isset($block_info) || $reset) {
      $block_info = array();
      if (!$reset) {
        $block_info = context_cache_get('block_info');
      }
      if (empty($block_info)) {
        if (module_exists('block')) {
          $block_blocks = _block_rehash($theme_key);
          $block_info = array();
          // Change from numeric keys to module-delta.
          foreach ($block_blocks as $block) {
            $block = (object) $block;
            unset($block->theme, $block->status, $block->weight, $block->region, $block->custom, $block->visibility, $block->pages);
            $block->bid = "{$block->module}-{$block->delta}";
            $block_info[$block->bid] = $block;
          }
        }
        else {
          $block_info = array();
          foreach (module_implements('block_info') as $module) {
            $module_blocks = module_invoke($module, 'block_info');
            if (!empty($module_blocks)) {
              foreach ($module_blocks as $delta => $block) {
                $block = (object) $block;
                $block->module = $module;
                $block->delta = $delta;
                $block->bid = "{$block->module}-{$block->delta}";
                $block_info[$block->bid] = $block;
              }
            }
          }
        }
        context_cache_set('block_info', $block_info);
      }
      // Allow other modules that may declare blocks dynamically to alter
      // this list.
      drupal_alter('context_block_info', $block_info);

      // Gather only region info from the database.
      if (module_exists('block')) {
        $result = db_select('block')
          ->fields('block')
          ->condition('theme', $theme_key)
          ->execute();
        foreach ($result as $row) {
          if (isset($block_info["{$row->module}-{$row->delta}"])) {
            $block_info["{$row->module}-{$row->delta}"] = (object) array_merge((array) $row, (array) $block_info["{$row->module}-{$row->delta}"]);
            unset($block_info["{$row->module}-{$row->delta}"]->status);
            unset($block_info["{$row->module}-{$row->delta}"]->visibility);
            unset($block_info["{$row->module}-{$row->delta}"]->cache);
          }
        }
      }
    }

    $blocks = array();

    // No region specified, provide all blocks.
    if (!isset($region)) {
      $blocks = $block_info;
    }
    // Region specified.
    else {
      foreach ($block_info as $bid => $block) {
        if (isset($block->region) && $block->region == $region) {
          $blocks[$bid] = $block;
        }
      }
    }

    // Add context blocks if provided.
    if (is_object($context) && $options = $this->fetch_from_context($context)) {
      if (!empty($options['blocks'])) {
        foreach ($options['blocks'] as $block) {
          if (
            isset($block_info["{$block['module']}-{$block['delta']}"]) && // Block is valid.
            (!isset($region) || (!empty($region) && $block['region'] == $region)) // No region specified or regions match.
          ) {
            $context_block = $block_info["{$block['module']}-{$block['delta']}"];
            $context_block->weight = $block['weight'];
            $context_block->region = $block['region'];
            $context_block->context = !empty($context->name) ? $context->name : 'tempname';
            $blocks[$context_block->bid] = $context_block;
          }
        }
      }
      uasort($blocks, array('context_reaction_block', 'block_sort'));
    }
    return $blocks;
  }

  /**
   * Sort callback.
   */
  static function block_sort($a, $b) {
    return ($a->weight - $b->weight);
  }

  /**
   * Compatibility wrapper around json_decode().
   */
  protected function json_decode($json, $assoc = FALSE) {
    // Requires PHP 5.2.
    if (function_exists('json_decode')) {
      return json_decode($json, $assoc);
    }
    return context_reaction_block::_json_decode($json);
  }

  /**
   * From http://www.php.net/manual/en/function.json-decode.php#91216
   * with modifications for consistency with output of json_decode().
   *
   * Original author: walidator.info 2009.
   */
  static function _json_decode($json) {
    $comment = FALSE;
    $out = '$x = ';
    for ($i=0; $i < strlen($json); $i++) {
      if (!$comment) {
        switch ($json[$i]) {
          case '{':
            $out .= ' (object) array(';
            break;
          case '}':
            $out .= ')';
            break;
          case '[':
            $out .= ' array(';
            break;
          case ']':
            $out .= ')';
            break;
          case ':';
            $out .= '=>';
            break;
          default:
            $out .= $json[$i];
            break;
        }
      }
      else {
        $out .= $json[$i];
      }
      if ($json[$i] == '"') {
        $comment = !$comment;
      }
    }
    eval($out . ';');
    return $x;
  }

  /**
   * Block renderer for AJAX requests. Triggered when $_GET['context_block']
   * is set. See ->execute() for how this is called.
   */
  function render_ajax($param) {
    // Besure the page isn't a 404 or 403.
    $headers = drupal_get_http_header();
    if (array_key_exists('status', $headers) && ($headers['status'] == "404 Not Found" || $headers['status'] == "403 Forbidden")) {
      return;
    }
    // Set the header right away. This will inform any players in the stack
    // that we are in the middle of responding to an AJAX request.
    drupal_add_http_header('Content-Type', 'text/javascript; charset=utf-8');

    if (strpos($param, ',') !== FALSE) {
      list($bid, $context) = explode(',', $param);
      list($module, $delta) = explode('-', $bid, 2);
      // Check token to make sure user has access to block.
      if (empty($_GET['context_token']) || $_GET['context_token'] != drupal_get_token($bid)) {
        echo drupal_json_encode(array('status' => 0));
        exit;
      }

      // Ensure $bid is valid.
      $info = $this->get_blocks();
      if (isset($info[$bid])) {
        module_load_include('module', 'block', 'block');
        $block = $info[$bid];
        $block->title = isset($block->title) ? $block->title : '';
        $block->context = $context;        
        $block->region = '';
        $rendered_blocks = _block_render_blocks(array($block)); // For E_STRICT warning
        $block = array_shift($rendered_blocks);
        if (empty($block->content['#markup'])) {
          $block->content['#markup'] = "<div class='context-block-empty'>" . t('This block appears empty when displayed on this page.') . "</div>";
        }
        $block = $this->editable_block($block);
        $renderable_block = _block_get_renderable_array(array($block)); // For E_STRICT warning
        echo drupal_json_encode(array(
          'status' => 1,
          'block' => drupal_render($renderable_block),
        ));
        drupal_exit();
      }
    }
    echo drupal_json_encode(array('status' => 0));
    drupal_exit();
  }
}
