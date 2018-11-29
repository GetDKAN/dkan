Display Number of Datasets, Topics or Other Items
=================================================

Datasets, resources, tags and topics in DKAN are created using Drupal's content types and vocabularies. This means that we can use many of the Drupal modules and tools to extend the site display and functionality.

The Views module is one of the most popular and powerful Drupal modules. It provides a user interface to query content and other site assets and choose display options without any code.

The following recipe will use Views and Panals modules to create some simple site statistics and display them on the home page. The end result will look like:

.. figure:: https://user-images.githubusercontent.com/512243/46543802-ca9d8700-c88f-11e8-9824-0cd7f7e52934.png

Step 1
------

- Go to `admin/structure/views/import` and import the following two views:

Terms Count View:

.. code-block:: php

  $view = new view();
  $view->name = 'term_counts';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'taxonomy_term_data';
  $view->human_name = 'Term Counts';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Tags';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'none';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: COUNT(DISTINCT Taxonomy term: Name) */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'taxonomy_term_data';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['group_type'] = 'count_distinct';
  $handler->display->display_options['fields']['name']['label'] = '';
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['name']['element_type'] = 'h1';
  $handler->display->display_options['fields']['name']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['name']['element_default_classes'] = FALSE;
  /* Filter criterion: Taxonomy vocabulary: Machine name */
  $handler->display->display_options['filters']['machine_name']['id'] = 'machine_name';
  $handler->display->display_options['filters']['machine_name']['table'] = 'taxonomy_vocabulary';
  $handler->display->display_options['filters']['machine_name']['field'] = 'machine_name';
  $handler->display->display_options['filters']['machine_name']['value'] = array(
    'tags' => 'tags',
  );

  /* Display: Topics Block */
  $handler = $view->new_display('block', 'Topics Block', 'block_1');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['title'] = 'Topics';

  /* Display: Tags Block */
  $handler = $view->new_display('block', 'Tags Block', 'block_2');

Node Counts view:

.. code-block:: php

  $view = new view();
  $view->name = 'nodecounts';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'Node counts';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Datasets';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'none';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  $handler->display->display_options['row_options']['inline'] = array(
    'type_1' => 'type_1',
    'type' => 'type',
  );
  $handler->display->display_options['row_options']['separator'] = ': ';
  /* Field: COUNT(Content: Type) */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'node';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['group_type'] = 'count';
  $handler->display->display_options['fields']['type']['label'] = '';
  $handler->display->display_options['fields']['type']['element_type'] = 'h1';
  $handler->display->display_options['fields']['type']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['type']['element_default_classes'] = FALSE;
  $handler->display->display_options['fields']['type']['separator'] = '';
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'dataset' => 'dataset',
  );

  /* Display: Datasets Block */
  $handler = $view->new_display('block', 'Datasets Block', 'block');

  /* Display: Resources Block */
  $handler = $view->new_display('block', 'Resources Block', 'block_1');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['title'] = 'Resources';
  $handler->display->display_options['defaults']['filter_groups'] = FALSE;
  $handler->display->display_options['defaults']['filters'] = FALSE;
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'resource' => 'resource',
  );

These two views provide blocks for Dataset, Resource, Topic, and Tag counts.

- Clear the Drupal cache.

Step 2
------

Add Blocks to Home page:
^^^^^^^^^^^^^^^^^^^^^^^^
- Go to the Panelizer page for the home page. You can find this by finding the "Welcome" page in the content menu and clicking "edit". Replace the "edit" term in the URL with "panelizer". The URL should look like `node/1/panelizer`.
- Click "content" in the default view mode.
- Click the gear Icon under "Triplet First Column" and "Add Content"

.. figure:: https://user-images.githubusercontent.com/512243/46544377-68458600-c891-11e8-8498-08d0086bb2a7.png

Click "Views" in the left column and then "Node Counts"

.. figure:: https://user-images.githubusercontent.com/512243/46544495-bfe3f180-c891-11e8-8972-ddc136d52d72.png

Select "Dataset Block" as the display. Click "Finish" and then "Save" on the panelizer page.

- Repeat the process for the other types of statistics you want to add. To add Topics or Tags statistics select "Term Counts" instead of "Node Counts".
