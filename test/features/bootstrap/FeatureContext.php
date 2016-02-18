<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext
{

// This file is only meant for temporary custom step functions or overrides to the dkanextension.
// Changes should be implemented in dkanextension so that it works across all projects.

  /**
   * @When I attach the file :path to :field using file resup
   */
  public function iAttachTheDrupalFileUsingFileResup($path, $field)
  {
    $field = $this->fixStepArgument($field);
    $session = $this->getSession();
    $page = $session->getPage();
    $session->executeScript('jQuery(".file-resup-wrapper input").show()');
    $session->executeScript('jQuery(".file-resup-wrapper input[name=\'' . $field . '\']").parent().find("input[type=\'file\']").attr("id", "' . $field . '")');

    // Relative paths stopped working after selenium 2.44.
    $offset = 'features/bootstrap/FeatureContext.php';
    $dir =  __file__;
    $test_dir = str_replace($offset, "", $dir);
    $path = $this->getMinkParameter('files_path') . '/' . $path;
    $session->getPage()->attachFileToField($field, $path);
  }

  /**
   * Wait for upload file to finish
   *
   * Wait until the class="progress-bar" element is gone,
   * or timeout after 3 minutes (180,000 ms).
   *
   * @Given /^I wait for the file upload to finish$/
   */
  public function iWaitForUploadFileToFinish() {
    $this->getSession()->wait(180000, 'jQuery(".progress-bar").length === 0');
  }

  public function fixStepArgument($argument)
  {
    return str_replace('\\"', '"', $argument);
  }
}
