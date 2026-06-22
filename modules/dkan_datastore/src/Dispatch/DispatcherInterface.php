<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Datastore dispatcher service interface.
 */
interface DispatcherInterface {

  /**
   * Dispatches the given input.
   *
   * @param \Drupal\dkan_datastore\Dispatch\DispatchInput $input
   *   The input containing dataset information and metadata.
   *
   * @return \Drupal\dkan_datastore\Dispatch\DispatchResult
   *   The result of the dispatch process.
   */
  public function dispatch(DispatchInput $input): DispatchResult;

}
