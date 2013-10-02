<?php

/**
 * @file
 * Contains the SearchApiViewsCache class.
 */

/**
 * Plugin class for caching Search API views.
 */
class SearchApiViewsCache extends views_plugin_cache_time {

  /**
   * Static cache for get_results_key().
   *
   * @var string
   */
  protected $_results_key = NULL;

  /**
   * Static cache for getSearchApiQuery().
   *
   * @var SearchApiQueryInterface
   */
  protected $search_api_query = NULL;

  /**
   * Overrides views_plugin_cache::cache_set().
   *
   * Also stores Search API's internal search results.
   */
  public function cache_set($type) {
    if ($type != 'results') {
      return parent::cache_set($type);
    }

    $cid = $this->get_results_key();
    $data = array(
      'result' => $this->view->result,
      'total_rows' => isset($this->view->total_rows) ? $this->view->total_rows : 0,
      'current_page' => $this->view->get_current_page(),
      'search_api results' => $this->view->query->getSearchApiResults(),
    );
    cache_set($cid, $data, $this->table, $this->cache_set_expire($type));
  }

  /**
   * Overrides views_plugin_cache::cache_get().
   *
   * Additionally stores successfully retrieved results with
   * search_api_current_search().
   */
  public function cache_get($type) {
    if ($type != 'results') {
      return parent::cache_get($type);
    }

    // Values to set: $view->result, $view->total_rows, $view->execute_time,
    // $view->current_page.
    if ($cache = cache_get($this->get_results_key(), $this->table)) {
      $cutoff = $this->cache_expire($type);
      if (!$cutoff || $cache->created > $cutoff) {
        $this->view->result = $cache->data['result'];
        $this->view->total_rows = $cache->data['total_rows'];
        $this->view->set_current_page($cache->data['current_page']);
        $this->view->execute_time = 0;

        // Trick Search API into believing a search happened, to make facetting
        // et al. work.
        $query = $this->getSearchApiQuery();
        search_api_current_search($query->getOption('search id'), $query, $cache->data['search_api results']);

        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Overrides views_plugin_cache::get_results_key().
   *
   * Use the Search API query as the main source for the key.
   */
  public function get_results_key() {
    global $user;

    if (!isset($this->_results_key)) {
      $query = $this->getSearchApiQuery();
      $query->preExecute();
      $key_data = array(
        'query' => $query,
        'roles' => array_keys($user->roles),
        'super-user' => $user->uid == 1, // special caching for super user.
        'language' => $GLOBALS['language']->language,
        'base_url' => $GLOBALS['base_url'],
      );
      // Not sure what gets passed in exposed_info, so better include it. All
      // other parameters used in the parent method are already reflected in the
      // Search API query object we use.
      if (isset($_GET['exposed_info'])) {
        $key_data[$key] = $_GET[$key];
      }

      $this->_results_key = $this->view->name . ':' . $this->display->id . ':results:' . md5(serialize($key_data));
    }

    return $this->_results_key;
  }

  /**
   * Get the Search API query object associated with the current view.
   *
   * @return SearchApiQueryInterface|null
   *   The Search API query object associated with the current view; or NULL if
   *   there is none.
   */
  protected function getSearchApiQuery() {
    if (!isset($this->search_api_query)) {
      $this->search_api_query = FALSE;
      if (isset($this->view->query) && $this->view->query instanceof SearchApiViewsQuery) {
        $this->search_api_query = $this->view->query->getSearchApiQuery();
      }
    }

    return $this->search_api_query ? $this->search_api_query : NULL;
  }

}
