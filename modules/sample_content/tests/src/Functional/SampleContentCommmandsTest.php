<?php

namespace Drupal\Tests\sample_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

class SampleContentCommmandsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected static $modules = [
    'sample_content',
  ];



}
