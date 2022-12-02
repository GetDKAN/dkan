<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\datastore\Exception\LocalizeException;
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
      'destination' => $config['filePath'],
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
   * Actually copy the file to disk.
   *
   * @param array $state
   *   State array.
   * @param \Procrastinator\Result $result
   *   Job result object.
   *
   * @return array
   *   Array with two elements: state and result.
   *
   * @throws \Drupal\datastore\Exception\LocalizeException
   */
  public function copy(array $state, Result $result): array {
    $bytesToRead = 10 * 1000 * 1000;
    $bytesCopied = 0;

    $fin = $this->ensureExistsForReading($state['source']);
    $fout = $this->ensureCreatingForWriting($state['destination']);

    while (!feof($fin)) {
      $bytesCopied += $this->readAndWrite($fin, $fout, $bytesToRead);
    }

    $result->setStatus(Result::DONE);
    fclose($fin);
    fclose($fout);
    $state['total_bytes_copied'] = $bytesCopied;
    $state['total_bytes'] = $bytesCopied;

    return ['state' => $state, 'result' => $result];
  }

  /**
   * Read incoming data and write to disk.
   *
   * @param resource $fin
   *   Incoming file resource/stream.
   * @param resource $fout
   *   Local file resource to write to.
   * @param int $bytesToRead
   *   How many bytes to read.
   *
   * @return int
   *   Bytes written.
   *
   * @throws \Drupal\datastore\Exception\LocalizeException
   */
  private function readAndWrite($fin, $fout, int $bytesToRead): int {
    $bytesRead = fread($fin, $bytesToRead);
    $bytesWritten = fwrite($fout, $bytesRead);
    return $bytesWritten;
  }

  /**
   * Ensure the target file can be read from.
   *
   * @param string $from
   *   The target filename.
   *
   * @return false|resource
   *   File resource.
   */
  private function ensureExistsForReading(string $from) {
    $fin = @fopen($from, "rb");
    if ($fin === FALSE) {
      throw new LocalizeException("opening", $from);
    }
    return $fin;
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
