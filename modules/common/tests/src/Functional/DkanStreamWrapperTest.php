<?php

namespace Drupal\Tests\common\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

class DkanStreamWrapperTest extends ExistingSiteBase {
  public function testPublicScheme() {
    $api = json_decode(file_get_contents('dkan://metastore'));
    $this->assertEquals("API Documentation", $api->info->title);
  }

}
