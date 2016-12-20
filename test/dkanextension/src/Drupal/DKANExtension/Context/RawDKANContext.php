<?php
namespace Drupal\DKANExtension\Context;

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Testwork\Environment\Environment;
use Drupal\DKANExtension\ServiceContainer\EntityStore;
use Drupal\DKANExtension\ServiceContainer\PageStore;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use EntityFieldQuery;

/**
 * Defines application features from the specific context.
 */
class RawDKANContext extends RawDrupalContext implements DKANAwareInterface {

  /** @var  \Drupal\DrupalExtension\Context\MinkContext */
  protected $minkContext;

  /** @var  \Devinci\DevinciExtension\Context\JavascriptContext */
  protected $jsContext;

  /**
   * @var \Drupal\DKANExtension\Context\PageContext
   */
  protected $pageContext;
  /**
   * @var \Drupal\DKANExtension\Context\SearchAPIContext
   */
  protected $searchContext;
  /**
   * @var \Drupal\DKANExtension\ServiceContainer\EntityStore
   */
  protected $entityStore;
  /**
   * @var \Drupal\DKANExtension\ServiceContainer\PageStore
   */
  protected $pageStore;
  /**
   * @var  \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;
  /**
   * @var Session
   */
  protected $fakeSession;

  protected $cacheSettings;

  public function setEntityStore(EntityStore $entityStore) {
    $this->entityStore = $entityStore;
  }

  public function getEntityStore() {
    return $this->entityStore;
  }

  public function setPageStore(PageStore $pageStore) {
    $this->pageStore = $pageStore;
  }

  public function getPageStore() {
    return $this->pageStore;
  }

  /**
   * @BeforeSuite
   */
  public static function disableAdminMenuCache(BeforeSuiteScope $scope) {
    // Turn off cache so the menu lives in the html.
    variable_set('admin_menu_cache_client', FALSE);
  }

  /**
   * @AfterSuite
   */
  public static function enableAdminMenuCache(AfterSuiteScope $scope) {
    variable_set('admin_menu_cache_client', TRUE);
  }

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    /** @var Environment $environment */
    $environment = $scope->getEnvironment();
    $this->searchContext = $environment->getContext('Drupal\DKANExtension\Context\SearchAPIContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
    // This context needs to be registered and hasn't been up to now. Don't load if we don't need it.
    //$this->drushContext = $environment->getContext('Drupal\DrupalExtension\Context\DrushContext');
    $this->jsContext = $environment->getContext('Devinci\DevinciExtension\Context\JavascriptContext');
    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');

  }

  /**
   * Save cache settings in a temporary variable.
   */
  protected function saveCacheSettings() {
    $this->cacheSettings = array(
      "cache" => variable_get("cache"),
      "page_cache_maximum_age" => variable_get("page_cache_maximum_age"),
      "cache_lifetime" => variable_get("cache_lifetime"),
    );
  }
  /**
   * @BeforeScenario @cacheDisabled
   */
  public function pageCacheIsOff()
  {
    $this->saveCacheSettings();
    variable_set("cache", FALSE);
  }

  /**
   * @BeforeScenario @cacheEnabled
   */
  public function pageCacheIsOn()
  {
    $this->saveCacheSettings();
    variable_set("cache", TRUE);
    variable_set("page_cache_maximum_age", 300);
    variable_set("cache_lifetime", 180);
  }

  /**
   * @AfterScenario
   */
  public function restoreCacheSettings()
  {
    if($this->cacheSettings) {
      variable_set("cache", $this->cacheSettings["cache"]);
      variable_set("page_cache_maximum_age", $this->cacheSettings["page_cache_maximum_age"]);
      variable_set("cache_lifetime", $this->cacheSettings["cache_lifetime"]);
      $this->cacheSettings = null;
    }
  }

  /**
   * Get node by title from Database.
   *
   * @param $title: title of the node.
   *
   * @return Node or FALSE
   */
  public function getNodeByTitle($title) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->propertyCondition('title', $title)
      ->range(0, 1);
    $result = $query->execute();
    if (isset($result['node'])) {
      $nid = array_keys($result['node']);
      return entity_load('node', $nid);
    }
    return false;
  }

  /**
   * Get the currently logged in user.
   */
  public function getCurrentUser() {
    // Rely on DrupalExtension to keep track of the current user.
    return $this->drupalContext->user;
  }

  public function visitPage($named_page, $sub_path = null) {

    $page = $this->getPageStore()->retrieve($named_page);
    if (!$page) {
      throw new \Exception("Named page '$named_page' doesn't exist.");
    }
    $path = ($sub_path) ? $page->getUrl() . "/$sub_path" : $page->getUrl();
    $session = $this->getSession();
    $session = $this->visit($path, $session);
    $this->assertOnUrl($path);

    return $session;
  }

  public function getStatusCode($session = null) {
    if (!$session) {
      $session = $this->getSession();
    }
    try {
      return $session->getStatusCode();
    } catch (UnsupportedDriverActionException $e) {
      // Driver doesn't support this so we have to guess based on the page text.
      $results = $session->getPage()->findAll('css', 'h1');
      if (empty($results)) {
        // No H1s?  Maybe we're on the a page like the front page the doesn't have them.
        if(empty($session->getPage()->find('css', '#main'))) {
          //Let's assume that's a 500 error.
          return 500;
        }
      }
      // Check each of the results.
      foreach ($results as $h1) {
        $title = strtolower($h1->getText());
        if ($title == 'access denied') {
          return 403;
        }
        elseif ($title == 'page not found') {
          return 404;
        }
      }
      // Otherwise assume 200.
      return 200;
    }
  }

  public function assertOnUrl($assert_url, $session = null){
    if (!$session) {
      $session = $this->getSession();
    }

    $current_url = $session->getCurrentUrl();
    // Support relative paths when on a "base_url" page. Otherwise assume a full url.
    $current_url = str_replace($this->getMinkParameter("base_url"), "", $current_url);

    // Remove hash part from url since it's widely used
    // for client side routing and this can make some
    // test fail.
    $current_url = strtok($current_url, "#");

    // This code was setup to ignore url get params, but we are using them for datasets, so ignore this for now.
    //$current_url = drupal_parse_url($current_url);
    //$current_url = $current_url['path'];
    if($current_url !== $assert_url){
      throw new \Exception("Current page is $current_url, but $assert_url expected.");
    }
  }

  public function assertOnPage($named_page){
    $page = $this->getPageStore()->retrieve($named_page);
    if (!$page) {
      throw new \Exception("Named page '$named_page' doesn't exist.");
    }
    $assert_url = $page->getUrl();
    $this->assertOnUrl($assert_url);
  }


  public function assertCanViewPage($named_page, $sub_path = null, $assert_code = null){
    $session = $this->visitPage($named_page, $sub_path);
    $code = $this->getStatusCode();

    // First check that a certain status code is expected.
    if (isset($assert_code)) {
      if ($assert_code !== $code) {
        throw new \Exception("Page {$session->getCurrentUrl()} code doesn't match $assert_code. CODE: $code");
      }
      return $code;
    }

    // Throw an exception if a non-successful code was found.
    if ($code < 200 || $code >= 500) {
      throw new \Exception("Page {$session->getCurrentUrl()} has an error. CODE: $code");
    }
    elseif ($code == 404) {
      throw new \Exception("Page {$session->getCurrentUrl()} not found. CODE: $code");
    }
    elseif ($code == 403) {
      throw new \Exception("Page {$session->getCurrentUrl()} is access denied. CODE: $code");
    }
    return $code;
  }

  /**
   * @return \Behat\Mink\Session
   */
  public function getSessionFake() {
    if (isset($this->fakeSession)) {
      $session = $this->fakeSession();
      //$session->reset();
      return $session;
    }
    $driver = new GoutteDriver();
    $session = new Session($driver);
    $session->start();
    $this->fakeSession = $session;
    return $session;
  }


  public function visit($url, $session = null) {
    if (!$session) {
      $session = $this->getSession();
    }
    $url = $this->locatePath($url);
    $session->visit($url);
    return $session;
  }

  public function assertCurrentPageCode($assert_code = 200) {
    $session = $this->getSession();
    $code = $this->getStatusCode();
    if ($code !== $assert_code) {
      throw new \Exception("Page {$session->getCurrentUrl()} code doesn't match. ASSERT: $assert_code CODE: $code");
    }
  }

}
