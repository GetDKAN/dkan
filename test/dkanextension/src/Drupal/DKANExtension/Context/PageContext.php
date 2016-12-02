<?php
namespace Drupal\DKANExtension\Context;
use Drupal\DKANExtension\ServiceContainer\Page;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class PageContext extends RawDKANContext {
  // Store pages to be referenced in an array.
  protected $pages = array();

  /**
   * Add page to context.
   *
   * @param $page
   */
  public function addPage($page) {
    $this->pages[$page['title']] = $page;
  }

  /**
   * @Given pages:
   */
  public function addPages(TableNode $pagesTable) {
    foreach ($pagesTable as $pageHash) {
      if (!isset($pageHash['name'])) {
        throw new \Exception('name value missing for page.');
      }
      if (!isset($pageHash['url'])) {
        throw new \Exception('url value missing for page.');
      }
      $page = new Page($pageHash['name'], $pageHash['url']);
      $this->getPageStore()->store($page);
      $this->pages[$pageHash['name']] = $pageHash;
    }
  }

  /**
   * @Given I am on (the) :page page
   * @Given I visit (the) :page page
   */
  public function givenOnPage($page) {
    $this->visitPage($page);
  }

  /**
   * @Then I should be on (the) :page page
   */
  public function iShouldBeOnPage($page){
    parent::assertOnPage($page);
  }

  /**
   * @Then The page status should be :type
   */
  public function pageStatusShouldBe($type) {
    switch ($type) {
      case 'ok':
        $code = 200;
        break;

      case 'access denied':
        $code = 403;
        break;

      case 'not found':
        $code = 404;
        break;

      case 'error':
        $code = 500;
        break;
    }

    parent::assertCurrentPageCode($code);
  }

  /**
   * @Given I should not be able to access :page_title
   */
  public function iShouldNotBeAbleToAccessPage($page_title) {
    if (isset($this->pages[$page_title])) {
      $session = $this->getSession();
      $url = $this->pages[$page_title]['url'];
      $session->visit($this->locatePath($url));
      try {
        $code = $session->getStatusCode();
        if ($code == 200) {
          throw new \Exception("200 OK: the page is accessible.");
        }
      } catch (UnsupportedDriverActionException $e) {
        // Some drivers don't support status codes, namely Selenium2Driver so
        // just drive on.
      }
    }
    else {
      throw new \Exception("Page $page_title not found in the pages array, was it added?");
    }
  }

  /**
   * @Given I should be able to edit :page
   */
  public function iShouldBeAbleToEditPage($page) {
    $node = $this->getNodeByTitle($page);
    if(!$node) {
      throw new \Exception(sprintf($page . " node not found."));
    }

    $session = $this->getSession();
    $url = "/node/" . $node->nid . "/edit";
    $session->visit($this->locatePath($url));
    $code = $session->getStatusCode();
    if ($code == 403) {
      throw new \Exception("403 Forbidden: the server refused to respond.");
    }
  }

  /**
   * @Given I should not be able to edit :page
   */
  public function iShouldNotBeAbleToEditPage($page) {
    $node = $this->getNodeByTitle($page);
    if(!$node) {
      throw new \Exception(sprintf($page . " node not found."));
    }

    $session = $this->getSession();
    $url = "/node/" . $node->nid . "/edit";
    $session->visit($this->locatePath($url));
    $code = $session->getStatusCode();
    if ($code == 200) {
      throw new \Exception("200 OK: the page is accessible.");
    }
  }

  /**
   * @Given I should be able to delete :page
   */
  public function iShouldBeAbleToDeletePage($page) {
    $node = $this->getNodeByTitle($page);
    if(!$node) {
      throw new \Exception(sprintf($page . " node not found."));
    }

    $session = $this->getSession();
    $url = "/node/" . $node->nid . "/delete";
    $session->visit($this->locatePath($url));
    $code = $session->getStatusCode();
    if ($code == 403) {
      throw new \Exception("403 Forbidden: the server refused to respond.");
    }
  }

  /**
   * @Given I should not be able to delete :page
   */
  public function iShouldNotBeAbleToDeletePage($page) {
    $node = $this->getNodeByTitle($page);
    if(!$node) {
      throw new \Exception(sprintf($page . " node not found."));
    }

    $session = $this->getSession();
    $url = "/node/" . $node->nid . "/delete";
    $session->visit($this->locatePath($url));
    $code = $session->getStatusCode();

    if ($code == 200) {
      throw new \Exception("200 OK: the page is accessible.");
    }
  }

  /**
   * @Given I should be able to access the :page_title page
   */
  public function iShouldBeAbleToAccessPage($page_title) {
    $this->assertCanViewPage($page_title);
  }

  /**
   * @Given I should be denied access to the :page_title page
   */
  public function iShouldBeDeniedToAccessPage($page_title) {
    // Assume mean getting a 403 (Access Denied), not just missing or an error.
    $this->assertCanViewPage($page_title, null, 403);
  }

  /**
   * @Given The :page_title page should not be found
   */
  public function pageShouldBeNotFound($page_title) {
    $this->assertCanViewPage($page_title, null, 404);
  }


  /**
   * @Given I visit the edit page for :named_entity
   */
  public function iVisitTheEntityEditPage($named_entity) {
    $this->visitPage($named_entity, "edit");
  }

  /**
   * @Given I visit the delete page for :named_entity
   */
  public function iVisitTheEntityDeletePage($named_entity) {
    // Assume mean getting a 403 (Access Denied), not just missing or an error.
    $this->visitPage($named_entity, "delete");
  }

  /**
   * Checks if the current URL contains the specified base path.
   */
  public function containsBasePath($session, $base_path) {
    $current_path = $session->getCurrentUrl();
    $base_path = $this->getMinkParameter("base_url") . $base_path;
    if (strpos($current_path, $base_path) === 0) {
      return TRUE;
    }

    return FALSE;
  }
}
