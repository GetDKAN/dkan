<?php

namespace Drupal\common;

use Drupal\dkan\DataResource as DkanDataResource;
use Procrastinator\JsonSerializeTrait;

class DataResource extends DkanDataResource implements \JsonSerializable {

  use JsonSerializeTrait;

}
