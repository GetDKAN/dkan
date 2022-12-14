<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use GuzzleHttp\Client;
use Procrastinator\Job\AbstractPersistentJob;
use Procrastinator\Result;

/**
 * These can be utilized to make a local copy of a remote file aka fetch a file.
 */
class FileFetcherJob extends AbstractPersistentJob {

  /**
   * Constructor.
   */
  public function __construct(string $identifier, $storage, array $config = NULL) {
    parent::__construct($identifier, $storage, $config);

    if (!isset($config['filePath'])) {
      throw new \Exception("Constructor missing expected config filePath.");
    }

    $state = [
      'source' => $config['filePath'],
      'total_bytes' => 0,
      'total_bytes_copied' => 0,
      'temporary' => FALSE,
      'temporary_directory' => $config['temporaryDirectory'] ?? '/tmp',
    ];

    $this->getResult()->setData(json_encode($state));
  }

  /**
   * {@inheritdoc}
   */
  protected function runIt() {
    $state = $this->setupState($this->getState());
    $this->getResult()->setData(json_encode($state));
    $info = $this->copy($this->getState(), $this->getResult(), $this->getTimeLimit());
    $this->setState($info['state']);
    return $info['result'];
  }

  /**
   * Set up the job state.
   *
   * @param array $state
   *   Incoming state array.
   *
   * @return array
   *   Modified state array.
   */
  protected function setupState(array $state): array {
    $state['total_bytes'] = PHP_INT_MAX;
    $state['temporary'] = TRUE;
    $state['destination'] = $this->getTemporaryFilePath($state);

    return $state;
  }

  /**
   * Get temporary file path, depending on flag keep_original_filename value.
   *
   * @param array $state
   *   State.
   *
   * @return string
   *   Temporary file path.
   */
  private function getTemporaryFilePath(array $state): string {
    $file_name = basename($state['source']);
    return "{$state['temporary_directory']}/{$file_name}";
  }

  /**
   * Copy the file to local storage.
   *
   * @param array $state
   *   State array.
   * @param \Procrastinator\Result $result
   *   Job result object.
   *
   * @return array
   *   Array with two elements: state and result.
   */
  public function copy(array $state, Result $result): array {
    if (stream_is_local($state['source'])) {
      return $this->copyLocal($state, $result);
    }
    else {
      return $this->copyRemote($state, $result);
    }
  }

  /**
   * Copy local file to proper local storage.
   *
   * @param array $state
   *   State array.
   * @param \Procrastinator\Result $result
   *   Job result object.
   *
   * @return array
   *   Array with two elements: state and result.
   */
  protected function copyLocal(array $state, Result $result): array {
    $this->ensureCreatingForWriting($state['destination']);
    if (copy($state['source'], $state['destination'])) {
      $result->setStatus(Result::DONE);
    }
    else {
      throw new \Exception("File copy failed.");
    }
    $state['total_bytes_copied'] = $state['total_bytes'] = filesize($state['destination']);
    return ['state' => $state, 'result' => $result];
  }

  /**
   * Copy remote file to local storage.
   *
   * @param array $state
   *   State array.
   * @param \Procrastinator\Result $result
   *   Job result object.
   *
   * @return array
   *   Array with two elements: state and result.
   */
  protected function copyRemote(array $state, Result $result): array {
    $client = new Client();
    try {
      $fout = $this->ensureCreatingForWriting($state['destination']);
      $client->get($state['source'], ['sink' => $fout]);
      $result->setStatus(Result::DONE);
    }
    catch (\Exception $e) {
      $result->setStatus(Result::ERROR);
      $result->setError($e->getMessage());
    }

    $state['total_bytes_copied'] = $state['total_bytes'] = filesize($state['destination']);
    return ['state' => $state, 'result' => $result];
  }

  /**
   * Ensure the destination file can be created.
   *
   * @param string $to
   *   The destination filename.
   *
   * @return false|resource
   *   File resource.
   */
  private function ensureCreatingForWriting(string $to) {
    // Delete destination first to avoid appending if existing.
    if (file_exists($to)) {
      unlink($to);
    }
    $fout = fopen($to, "w");
    return $fout;
  }

}
