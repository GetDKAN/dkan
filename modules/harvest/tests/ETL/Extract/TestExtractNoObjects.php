<?php

namespace modules\harvest\tests\ETL\Extract;

use Drupal\harvest\ETL\Extract\Extract;

class TestExtractNoObjects extends Extract
{
    protected function getItems(): array
    {
        return ["Hello World!!"];
    }
}
