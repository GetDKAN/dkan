<?php

namespace Drupal\common\Storage;

use Drupal\dkan\Storage\JobStore as DkanJobStore;

/**
 * Backwards compatibility for the JobStore class.
 *
 * @deprecated in 2.21.0 and removed in 2.22.0. Use 
 *   \Drupal\dkan\Storage\JobStore instead.
 * @see https://github.com/GetDKAN/dkan/issues/4412
 */
class JobStore extends DkanJobStore {

}
