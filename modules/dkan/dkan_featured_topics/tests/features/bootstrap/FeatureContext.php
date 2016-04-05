<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  // Store pages to be referenced in an array.
  protected $pages = array();
  protected $groups = array();

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    // Set the default timezone to NY
    date_default_timezone_set('America/New_York');
  }

  private function panels_load_page_by_id($id) {
    ctools_include('page', 'page_manager', 'plugins/tasks');
    ctools_include('page_manager.admin', 'page_manager', '');
    ctools_include('content');
    ctools_include('export');

    $tasks = page_manager_get_tasks_by_type('page');
    $page_types = array();

    foreach ($tasks as $task) {
      // Disabled page return empty
      if ($pages = page_manager_load_task_handlers($task)) {
        $page_types[] = $pages;
      }
    }

    // Not all display objects are loaded, make sure to load them
    foreach ($page_types as &$pages) {
      foreach ($pages as &$page) {
        if (empty($page->conf['display']) && !empty($page->conf['did'])) {
          $page->conf['display'] = panels_load_display($page->conf['did']);
        }
      }
    }

    // Page types will have all panel page objects fully loaded
    foreach ($page_types as $page_type) {
      foreach ($page_type as $key => $value) {
        if ($key == $id) {
          return $value;
        }
      }
    }
  }

  private function panels_display_pane_add(&$display, $region, $type_name, $subtype_name, $configuration, $style) {
    $content_type = ctools_get_content_type($type_name);
    $subtype = ctools_content_get_subtype($content_type, $subtype_name);
    $pane = panels_new_pane($type_name, $subtype_name, TRUE);
    $pane->configuration = $configuration;
    $pane->style = $style;

    $display->add_pane($pane, $region);
    panels_save_display($display);
  }

  private function panels_display_pane_remove(&$display, $region, $type_name, $subtype_name) {
    foreach ($display->content as $key => $pane) {
      if($pane->panel == $region && $pane->type == $type_name && $pane->subtype == $subtype_name) {
        $region_pane_key = array_search($pane->pid, $display->panels[$region]);
        unset($display->panels[$region][$region_pane_key]);
        unset($display->content[$key]);
      }
    }
    panels_save_display($display);
  }

  /**
   * @BeforeScenario @FeaturedTopics
   */
  public function beforeFeaturedTopics()
  {
    $page = $this->panels_load_page_by_id('page_front_page_panel_context');
    $configuration = array(
      'items_per_page' => 6
    );
    $style = array(
      'settings' => NULL
    );
    $this->panels_display_pane_add($page->conf['display'], 'middle', 'views_panes', 'dkan_topics_featured-panel_pane_1', $configuration, $style);
  }

  /**
   * @AfterScenario @FeaturedTopics
   */
  public function afterFeaturedTopics()
  {
    $page = $this->panels_load_page_by_id('page_front_page_panel_context');
    $this->panels_display_pane_remove($page->conf['display'], 'middle', 'views_panes', 'dkan_topics_featured-panel_pane_1');
  }

  /**
   * Build the cache key so that the editor and IPE can properly find
   * everything needed for this display.
   */
  public function panels_panel_context_cache_key($task_name, $handler_id, $args) {
    $arguments = array();
    foreach ($args as $arg) {
      // Sadly things like panels everywhere actually use non-string arguments
      // and they basically can't be represented here. Luckily, PE also does
      // not use a system where this matters, so replace its args with a 0
      // for a placeholder.
      if (is_string($arg)) {
        $arguments[] = $arg;
      }
      else {
        $arguments[] = '0';
      }
    }
    $cache_key = 'panel_context:' . $task_name . '::' . $handler_id . '::' . implode('\\', $arguments) . '::';
    return $cache_key;
  }
}
