<?php
namespace Drupal\DKANExtension\ServiceContainer;

use \Drupal\DKANExtension\ServiceContainer\StoreInterface;


class Page {

  protected $name;

  protected $url;

  public function __construct($name, $url) {
    #TODO Add more checking here.
    if (gettype($name) !== 'string') {
      throw new \Exception('$name value is not a string');
    }
    if (gettype($url) !== 'string') {
      throw new \Exception('$url value is not a string');
    }
    $this->name = $name;
    $this->url = $url;
  }

  public function getName() {
    return $this->name;
  }

  public function getUrl() {
    return $this->url;
  }
}

/**
 * Store urls or paths as named elements for easy reuse.
 */
class PageStore implements StoreInterface {

  protected $pages = array();

  /**
   * Stores a Page item by it's name.
   *
   * @param Page $page
   * @throws \Exception
   */
  function store($page) {
    if (!$page instanceof Page) {
      throw new \Exception("PageStore can only store Page objects.");
    }
    $name = $page->getName();
    if (isset($this->pages[$name])) {
      throw new \Exception("A named Path with name '$name' already exists.");
    }
    // This should point to the same objects if they get updated.
    $this->pages[$name] = $page;
  }

  /**
   * Get a Path from the Store if it exists.
   *
   * @param String $name Name of a Path.
   * @return Page|bool The Path if found or FALSE if not.
   */
  function retrieve($name) {
    if (!isset($this->pages[$name])) {
      return FALSE;
    }
    return clone $this->pages[$name];
  }

  /**
   * Delete a Path from the Store if it exists.
   *
   * @param String $name Name of a Page.
   * @return Page|bool The Path if found or FALSE if not.
   */
  function delete($name) {
    if (!isset($this->pages[$name])) {
      return FALSE;
    }
    unset($this->pages[$name]);
    return TRUE;
  }

  /**
   * Delete a Path from the Store if it exists.
   *
   * @return Page[] Return all the stored Pages
   */
  function getAll() {
    return $this->pages;
  }

  /**
   * Delete all stored Pages.
   */
  function flush() {
    $this->pages = array();
  }
}
