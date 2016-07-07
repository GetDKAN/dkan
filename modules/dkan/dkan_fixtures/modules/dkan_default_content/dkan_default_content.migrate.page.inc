<?php

/**
 * Migration class used to import Default Data Stories from fixtures.
 */
class DefaultContentPagesImport extends DefaultContentPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
    $arguments['list_url'] = $arguments['endpoint'] . 'page_list.json';
    $arguments['item_url'] = $arguments['endpoint'] . 'page_show?id=:id.json';
    $arguments['content_type'] = 'page';

    parent::__construct($arguments);

    // Add field mappings.
    $this->addFieldMapping('body', 'body_value');
    $this->addFieldMapping('body:summary', 'body_summary');
    $this->addFieldMapping('body:format', 'body_format');
  }

  /**
   *
   */
  public function prepareRow($row) {
    // Prepare 'body' field for field mapping.
    $body = $row->body->und[0];
    $row->body_value = $body->value;
    $row->body_summary = $body->summary;
    $row->body_format = $body->format;
  }

  /**
   * Do extra processing after import.
   */
  public function complete($entity, $row) {

    parent::complete($entity, $row);

    if (isset($row->is_homepage)) {
      // Setup imported page as the homepage.
      variable_set('site_frontpage', 'node/' . $entity->nid);
    } else {
      // Setup menu item on main menu.
      $this->addMainMenuLink($entity);
    }
  }

  /**
   * Add a menu link on Main Menu for entity.
   */
  private function addMainMenuLink($entity) {
    $item = array(
      'menu_name' => 'main-menu',
      'link_path' => 'node/' . $entity->nid,
      'router_path' => 'node/%',
      'link_title' => $entity->title,
      'options' => array(
        'attributes' => array(
          'title' => '',
        ),
        'identifier' => 'main-menu_about:node/' . $entity->nid,
      ),
      'module' => 'menu',
      'hidden' => 0,
      'external' => 0,
      'has_children' => 0,
      'expanded' => 0,
      'weight' => 0,
      'customized' => 1,
    );
    menu_link_save($item);
  }
}