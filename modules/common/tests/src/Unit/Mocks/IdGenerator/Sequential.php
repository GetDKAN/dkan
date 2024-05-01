<?php

namespace Drupal\Tests\common\Unit\Mocks\IdGenerator;

use Drupal\common\Contracts\IdGeneratorInterface;

class Sequential implements IdGeneratorInterface
{
    private int $id = 0;
    public function generate(): int
    {
        $this->id++;
        return $this->id;
    }
}
