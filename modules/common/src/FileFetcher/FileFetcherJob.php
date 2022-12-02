<?php

namespace Drupal\common\FileFetcher;

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

    $config = $this->validateConfig($config);
    $state = [
      'source' => $config['filePath'],
      'total_bytes' => 0,
      'total_bytes_copied' => 0,
      'temporary' => FALSE,
      'keep_original_filename' => $config['keep_original_filename'] ?? FALSE,
      'destination' => $config['filePath'],
      'temporary_directory' => $config['temporaryDirectory'],
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
   * Validate configuration for localizer.
   *
   * @param mixed $config
   *   Config array, must contain a filePath element.
   *
   * @return array
   *   Validated configuration.
   */
  private function validateConfig($config): array {
    if (!is_array($config)) {
      throw new \Exception("Constructor missing expected config filePath.");
    }
    if (!isset($config['temporaryDirectory'])) {
      $config['temporaryDirectory'] = "/tmp";
    }
    if (!isset($config['filePath'])) {
      throw new \Exception("Constructor missing expected config filePath.");
    }
    return $config;
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
    if ($state['keep_original_filename']) {
      return $this->getTemporaryFileOriginalName($state);
    }
    else {
      return $this->getTemporaryFile($state);
    }
  }

  /**
   * Create a temporary path and filename based on original.
   *
   * @param array $state
   *   State array.
   *
   * @return string
   *   Temporary filename.
   */
  private function getTemporaryFileOriginalName(array $state): string {
    $file_name = basename($state['source']);
    return "{$state['temporary_directory']}/{$file_name}";
  }

  /**
   * Get a temporary path and filename not strictly based on original.
   *
   * @param array $state
   *   State array.
   *
   * @return string
   *   Temporary file name with path.
   */
  private function getTemporaryFile(array $state): string {
    $info = parse_url($state['source']);
    $file_name = "";
    if (isset($info["host"])) {
      $file_name .= str_replace(".", "_", $info["host"]);
    }
    $file_name .= str_replace("/", "_", $info['path']);
    return $state['temporary_directory'] . '/' . $this->sanitizeString($file_name);
  }

  /**
   * Sanitize string, replacing non-alphanumeric characters with underscores.
   *
   * @param string $string
   *   Incoming string.
   *
   * @return string
   *   Sanitized string.
   */
  private function sanitizeString(string $string): string {
    return preg_replace('~[^a-z0-9.]+~', '_', strtolower($string));
  }

  /**
   * Actually copy the file to disk.
   *
   * @param array $state
   *   State array.
   * @param \Procrastinator\Result $result
   *   Job result object.
   * @param int $timeLimit
   *   Time limit for executing job.
   *
   * @return array
   *   Array with two elements: state and result.
   *
   * @throws \Drupal\datastore\Exception\LocalizeException
   */
  public function copy(array $state, Result $result, int $timeLimit = PHP_INT_MAX): array {
    [$from, $to] = $this->validateAndGetInfoFromState($state);
    $bytesToRead = 10 * 1000 * 1000;
    $bytesCopied = 0;

    $fin = $this->ensureExistsForReading($from);
    $fout = $this->ensureCreatingForWriting($to);

    while (!feof($fin)) {
      $bytesCopied += $this->readAndWrite(
        $fin,
        $fout,
        $bytesToRead,
        $state
      );
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
   * @param array $state
   *   Job state array.
   *
   * @return int
   *   Bytes written.
   *
   * @throws \Drupal\datastore\Exception\LocalizeException
   */
  private function readAndWrite($fin, $fout, int $bytesToRead, array $state): int {
    [$from, $to] = $this->validateAndGetInfoFromState($state);

    $bytesRead = fread($fin, $bytesToRead);
    if ($bytesRead === FALSE) {
      throw new LocalizeException("reading from", $from);
    }
    $bytesWritten = fwrite($fout, $bytesRead);
    if ($bytesWritten === FALSE) {
      throw new LocalizeException("writing to", $to);
    }
    return $bytesWritten;
  }

  /**
   * Validate state for presence of source and destination, return them.
   *
   * @param array $state
   *   State array.
   *
   * @return array
   *   Array of source and destination strings.
   */
  private function validateAndGetInfoFromState(array $state): array {
    if (!isset($state['source']) && !isset($state['destination'])) {
      throw new \Exception("Incorrect state missing source, destination, or both.");
    }
    return [$state['source'], $state['destination']];
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

    if ($fout === FALSE) {
      throw new LocalizeException("creating", $to);
    }

    return $fout;
  }

}
