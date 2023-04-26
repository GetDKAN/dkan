<?php

namespace Drupal\Tests\common\Functional;

use weitzman\DrupalTestTraits\ExistingSiteBase;

class DkanStreamWrapperTest extends ExistingSiteBase {
  public function testPublicScheme() {
    $uri = 'dkan://metastore';
    $api = json_decode(file_get_contents('dkan://metastore'));
    $this->assertEquals("API Documentation", $api->info->title);

    $manager = \Drupal::service('stream_wrapper_manager');
    $scheme = $manager->getScheme($uri);
    $this->assertEquals("dkan", $scheme);
    $descriptions = $manager->getDescriptions();
    $this->assertStringContainsString("Simple way to request DKAN", "$descriptions[$scheme]");
    $names = $manager->getNames();
    $this->assertEquals("DKAN documents", "$names[$scheme]");

    $path = $manager->getViaScheme($scheme)->getDirectoryPath();
    $this->assertStringContainsString("/api/1", $path);
    $ext = $manager->getViaScheme($scheme)->getExternalUrl();
    $this->assertStringContainsString("http://web/api/1", $ext);
  }

}
