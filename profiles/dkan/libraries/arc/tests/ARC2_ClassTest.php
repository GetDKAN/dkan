<?php
require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__DIR__) . '/ARC2.php';
require_once dirname(__DIR__) . '/ARC2_Class.php';

class ARC2_ClassTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
         $this->arc2 = new ARC2_Class(array(), new stdclass);
    }

    public function testCamelCase() {
         $this->assertSame("Fish", $this->arc2->camelCase("fish"));
         $this->assertSame("fish", $this->arc2->camelCase("fish", true));
         $this->assertSame("fish", $this->arc2->camelCase("fish", true, true));

         $this->assertSame("FishHeads", $this->arc2->camelCase("fish_heads"));
         $this->assertSame("fishHeads", $this->arc2->camelCase("fish_heads", true));
         $this->assertSame("fishHeads", $this->arc2->camelCase("fish_heads", true, true));

         $this->assertSame("ALLCAPITALS", $this->arc2->camelCase("ALL_CAPITALS"));
    }

    public function testDeCamelCase() {
         $this->assertSame("fish", $this->arc2->deCamelCase("fish"));
         $this->assertSame("Fish", $this->arc2->deCamelCase("fish", true));

         $this->assertSame("fish heads", $this->arc2->deCamelCase("fish_heads"));
         $this->assertSame("Fish heads", $this->arc2->deCamelCase("fish_heads", true));

         $this->assertSame("ALL CAPITALS", $this->arc2->deCamelCase("ALL_CAPITALS"));
    }


    public function testV() {
        $this->assertSame(false, $this->arc2->v(null));
        $this->assertSame(false, $this->arc2->v("cats", false, array()));
        $this->assertSame(true, $this->arc2->v("cats", false, array("cats" => true)));

        $o = new stdclass;
        $o->cats = true;
        $this->assertSame(true, $this->arc2->v("cats", false, $o));
    }

    public function testV1() {
        $this->assertSame(false, $this->arc2->v1(null));
        $this->assertSame(false, $this->arc2->v1("cats", false, array()));
        $this->assertSame(true, $this->arc2->v1("cats", false, array("cats" => true)));
        $this->assertSame("blackjack", $this->arc2->v1("cats", "blackjack", array("cats" => null)));

        $o = new stdclass;
        $o->cats = true;
        $this->assertSame(true, $this->arc2->v1("cats", false, $o));

        $o = new stdclass;
        $o->cats = 0;
        $this->assertSame("blackjack", $this->arc2->v1("cats", "blackjack", $o));
    }

    public function testExtractTermLabel() {
        $this->assertSame("bar", $this->arc2->extractTermLabel('http://example.com/foo#bar'));
        $this->assertSame("bar cats", $this->arc2->extractTermLabel('http://example.com/foo#bar?cats'));
        $this->assertSame("bar", $this->arc2->extractTermLabel('#bar'));
        $this->assertSame("bar", $this->arc2->extractTermLabel('http://example.com/bar'));
        $this->assertSame("bar", $this->arc2->extractTermLabel('http://example.com/bar/'));
    }

}
