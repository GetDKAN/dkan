<?php

use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Context\Step\Given;
use Symfony\Component\Process\Process;

require 'vendor/autoload.php';

class FeatureContext extends DrupalContext
{
    /**
     * @Then /^I wait for the dialog box to appear$/
     */
    public function iWaitForTheDialogBoxToAppear()
    {
        $this->getSession()->wait(2000, "jQuery('#user-login-dialog').children().length > 0");
    }

    /**
     * Selects option in select field with specified by node title.
     *
     * @When /^(?:|I )select node named "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)"$/
     */
    public function selectNodeOption($select, $option)
    {
        $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT nid FROM node WHERE title = \'$option\'')->fetchField();\"");
        $option = $this->readDrushOutput();
        $option = trim(str_replace(array("'"), "", $option));
        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
        $this->getSession()->getPage()->selectFieldOption($select, $option);
    }

    /**
     * @Given /^I am a "([^"]*)" of the group "([^"]*)"$/
     */
    public function iAmAMemberOfTheGroup($role, $group_name) {
      $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT nid FROM node WHERE title = \'$group_name\'')->fetchField();\"");
      $option = $this->readDrushOutput();
      $gid = trim(str_replace(array("'"), "", $option));
      $user = $this->user;
      $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT uid FROM users WHERE name = \'$user->name\'')->fetchField();\"");
      $option = $this->readDrushOutput();
      $user_id = trim(str_replace(array("'"), "", $option));
      $this->assertDrushCommandWithArgument('php-eval', "\"return db_query('SELECT rid FROM og_role WHERE name = \'$role\'')->fetchField();\"");
      $option = $this->readDrushOutput();
      $rid = trim(str_replace(array("'"), "", $option));
      $this->assertDrushCommandWithArgument('og-add-user',"node $gid $rid $user_id");
    }

    /**
     * Click on map icon as identified by its z-index.
     *
     * @Given /^I click map icon number "([^"]*)"$/
     */
    public function iClickMapIcon($num) {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//img[contains(@style,"z-index: ' . $num . '")]')

        );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find map icon: "%s"', $num));
        }
        $element->click();
    }

    /**
     * Copy of "I fill in" but doesn't escape "(".
     *
     * When using "I fill in" it escaped autocomplete fields node id. Just using
     * the title wouldn't work. The following focuses on the field and selects
     * the first dropdown.
     *
     * @Given /^I fill in the autocomplete field "([^"]*)" with "([^"]*)"$/
     */
    public function iFillInTheAutoFieldWith($field, $value) {
      $session = $this->getSession();
      $page = $session->getPage();
      $xpath = $page->find('xpath', '//input[@name="' . $field . '"]');
      $field = $this->fixStepArgument($field);
      $value = $this->fixStepArgument($value);
      // Focus means autocoplete will actually show up.
      $this->getSession()->getDriver()->focus('//input[@name="' . $field . '"]');
      $page->fillField($field, $value);
      $this->iWaitForSeconds(1);
      // Selects the first dropdown since there is no id or other way to
      // reference the desired entry.
      $title = $session->getPage()->find(
          'xpath',
          $session->getSelectorsHandler()->selectorToXpath('xpath', '//*[@class="reference-autocomplete"]')

      );
      $title->click();
    }

    /**
     * @Given /^I empty the field "([^"]*)"$/
     */
    public function iEmptyTheField($locator) {
      $session = $this->getSession();
      $page = $session->getPage();
      $field = $page->findField($locator);

      if (null === $field) {
          throw new ElementNotFoundException(
              $this->getSession(), 'form field', 'id|name|label|value', $locator
          );
      }

      $field->setValue("");
    }

    /**
     * Wait for the given number of seconds. ONLY USE FOR DEBUGGING!
     *
     * @Given /^I wait for "([^"]*)" seconds$/
     */
    public function iWaitForSeconds($arg1) {
      sleep($arg1);
    }

}
