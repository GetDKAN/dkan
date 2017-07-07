<?php
namespace Drupal\DKANExtension\Context;

use Drupal\DKANExtension\Context\RawDKANEntityContext;
use Drupal\DKANExtension\Context\ModeratorTrait;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\Console\Helper\Table;

/**
 * Defines application features from the specific context.
 */
class FeedbackContext extends RawDKANEntityContext {

  use ModeratorTrait;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    parent::__construct(
      'node',
      'feedback',
      NULL,
      array('rating', 'moderation', 'moderation_date')
      );
  }

  /**
   * Creates feedback from a table.
   *
   * @Given feedback:
   */
  public function addFeedbacks(TableNode $feedbacksTable) {
    parent::addMultipleFromTable($feedbacksTable);
  }

  /**
   * @Then The feedback :title is in :state moderation state
   */
  public function theFeedbackIsInModerationState($title, $state) {
    $node = reset($this->getNodeByTitle($title));
    if(!$node) {
      throw new \Exception(sprintf($title . " node not found."));
    }
    $this->isNodeInModerationState($node, $state);
  }

  /**
   * @Then I vote up the feedback :title
   */
  public function iVoteUpTheFeedback($title) {
    $link = $this->getVotingLink($title, 'fa-angle-up');
    if (!isset($link)) {
      throw new \Exception("Feedback " . $title . " not found.");
    }
    $link->click();
  }

  /**
   * @Then I vote down the feedback :title
   */
  public function iVoteDownTheFeedback($title) {
    $link = $this->getVotingLink($title, 'fa-angle-down');
    if (!isset($link)) {
      throw new \Exception("Feedback " . $title . " not found.");
    }
    $link->click();
  }

  private function getVotingLink($title, $link_class) {
    $links = $this->getSession()->getPage()->findAll('xpath', "//td[contains(@class,'views-field-title')]/a[text()='" . $title . "']/../../td/div/a[contains(@class, '" . $link_class . "')]");
    return array_pop($links);
  }

  /**
   * @Then The feedback :title should be rated :rating
   */
  public function theFeedbackIsRated($title, $rating) {
    $widgets = $this->getSession()->getPage()->findAll('xpath', "//td[contains(@class,'views-field-title')]/a[text()='" . $title . "']/../../td/div/div[contains(@class, 'rate-feedback-rating')]");
    $widget = array_pop($widgets);
    $actual = $widget->gettext();
    if ($actual != $rating) {
      throw new \Exception("Feedback was rated $actual instead of $rating");
    }
  }

  /**
   * @Then I should see a badge next to feedback :title
   */
  public function iShouldSeeABadgeNextToFeedback($title) {
    $badged_imgs = $this->getSession()->getPage()->findAll('xpath', "//td[contains(@class,'views-field-title')]/a[text()='" . $title . "']/../../td[contains(@class, 'authenticated-user')]");
    if (empty($badged_imgs)) {
      throw new \Exception("Feedback '$title' has no badge on the author picture.");
    }
  }

  /**
   * @Then I should not see a badge next to feedback :title
   */
  public function iShouldNotSeeABadgeNextToFeedback($title) {
    $badged_imgs = $this->getSession()->getPage()->findAll('xpath', "//td[contains(@class,'views-field-title')]/a[text()='" . $title . "']/../../td[contains(@class, 'authenticated-user')]");
    if (!empty($badged_imgs)) {
      throw new \Exception("Feedback '$title' has a badge on the author picture.");
    }
  }

  /**
   * Override RawDKANEntityContext::save()
   */
  public function save($fields) {
    global $user;
    $username = $fields['author'];
    $author = user_load_by_name($username);

    if ($author) {
      $current_user = $user;
      $user = $author;
    }

    parent::save($fields);

    if ($author) {
    // Restore the current behat user.
      $user = $current_user;
    }
  }

  /**
   * Override RawDKANEntityContext::postSave()
   */
  public function postSave($wrapper, $fields) {
    parent::postSave($wrapper, $fields);
    $this->moderate($wrapper, $fields);

    if (isset($fields['rating'])) {
      $votes = array(
        'entity_type' => 'node',
        'entity_id' => $wrapper->value()->nid,
        'value_type' => 'points',
        'value' => $fields['rating'],
        'tag' => 'feedback',
        );
      $criteria = NULL;
      votingapi_set_votes($votes, $criteria);
    }
  }
}
