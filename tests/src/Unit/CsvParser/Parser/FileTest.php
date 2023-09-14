<?php

namespace Drupal\Tests\dkan\Unit\CsvParser\Parser;

use Drupal\dkan\CsvParser\Parser\Csv;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group csvparser
 */
class FileTest extends TestCase {

  public function test(): void {
    $parser = Csv::getParser();
    $parser->feed(file_get_contents(dirname(__DIR__, 4) . '/data/countries.csv'));
    $parser->finish();

    $records = $parser->getRecords();

    $this->assertCount(5, $records);
  }

}
