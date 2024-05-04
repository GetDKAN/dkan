<?php

namespace Drupal\common\Contracts;

use Contracts\RetrieverInterface as ContractsRetrieverInterface;

/**
 * {@inheritDoc}
 *
 * @todo For now, this interface must inherit from getdkan/contracts because
 *   it is used by a number of other packages, with no clear point of
 *   encapsulation. Within getdkan/dkan we should use this sub-interface.
 *
 * @see \Procrastinator\Job\AbstractPersistentJob::get()
 */
interface RetrieverInterface extends ContractsRetrieverInterface {

}
