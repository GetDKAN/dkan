<?php

namespace Drupal\harvest\ETL\Transform;

class AddRandomNumber extends Transform
{
    public function run($item): object
    {
        $copy = clone $item;
        $copy->random_number = random_int(0, 100000);
        return $item;
    }
}
