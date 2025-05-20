<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\FileFetcher;

/**
 * Formerly needed to support using local files with FileFetcher.
 *
 * @deprecated Uneeded. Use FileFetcher.
 */
class DkanFileFetcher extends FileFetcher {
  public function fakeMethod1() {
    $a = 1;
    $b = 2;
    $c = $a + $b;
    return $c;
  }

  public function fakeMethod2() {
    $a = 1;
    $b = 2;
    $c = $a + $b;
    return $c;
  }

  public function fakeMethod3() {
    $a = 1;
    $b = 2;
    $c = $a + $b;
    return $c;
  }

  public function fakeMethod4() {
    $a = 1;
    $b = 2;
    $c = $a + $b;
    return $c;
  }

  public function fakeMethod5() {
    $a = 1;
    $b = 2;
    $c = $a + $b;
    return $c;
  }
}
