<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Contracts\ParserInterface;
use Drupal\common\Storage\DatabaseTableInterface;
use Procrastinator\Job\AbstractPersistentJob;
use Procrastinator\Result;
use ForceUTF8\Encoding;

/**
 *
 */
class ImportJob extends AbstractPersistentJob {
  protected $dataStorage;
  protected $parser;
  protected $resource;
  public const BYTES_PER_CHUNK = 8192;

  /**
   *
   */
  protected function __construct(string $identifier, $storage, array $config = NULL) {
    parent::__construct($identifier, $storage, $config);

    $this->dataStorage = $config['storage'];

    if (!($this->dataStorage instanceof DatabaseTableInterface)) {
      $storageInterfaceClass = DatabaseTableInterface::class;
      throw new \Exception("Storage must be an instance of {$storageInterfaceClass}");
    }

    $this->parser = $config['parser'];
    $this->resource = $config['resource'];
  }

  /**
   *
   */
  public function getStorage() {
    return $this->dataStorage;
  }

  /**
   * {@inheritdoc}
   */
  protected function runIt() {
    $filename = $this->resource->getFilePath();
    $size = @filesize($filename);
    if (!$size) {
      return $this->setResultError("Can't get size from file {$filename}");
    }

    if ($size <= $this->getBytesProcessed()) {
      return $this->getResult();
    }

    $maximum_execution_time = $this->getTimeLimit() ? (time() + $this->getTimeLimit()) : PHP_INT_MAX;

    try {
      $this->assertTextFile($filename);

      $h = fopen($filename, 'r');
      fseek($h, $this->getBytesProcessed());

      $this->parseAndStore($h, $maximum_execution_time);

      fclose($h);
    }
    catch (\Exception $e) {
      return $this->setResultError($e->getMessage());
    }

    // Flush the parser.
    $this->store();

    if ($this->getBytesProcessed() >= $size) {
      $this->getResult()->setStatus(Result::DONE);
    }
    else {
      $this->getResult()->setStatus(Result::STOPPED);
    }

    return $this->getResult();
  }

  /**
   *
   */
  protected function assertTextFile(string $filename) {
    $mimeType = mime_content_type($filename);
    if ("text" != substr($mimeType, 0, 4)) {
      throw new \Exception("Invalid mime type: {$mimeType}");
    }
  }

  /**
   *
   */
  protected function setResultError($message): Result {
    $this->getResult()->setStatus(Result::ERROR);
    $this->getResult()->setError($message);
    return $this->getResult();
  }

  /**
   *
   */
  protected function getBytesProcessed() {
    $chunksProcessed = $this->getStateProperty('chunksProcessed', 0);
    return $chunksProcessed * self::BYTES_PER_CHUNK;
  }

  /**
   *
   */
  protected function parseAndStore($fileHandler, $maximumExecutionTime) {
    $chunksProcessed = $this->getStateProperty('chunksProcessed', 0);
    while (time() < $maximumExecutionTime) {
      $chunk = fread($fileHandler, self::BYTES_PER_CHUNK);

      if (!$chunk) {
        $this->getResult()->setStatus(Result::DONE);
        $this->parser->finish();
        break;
      }
      $chunk = Encoding::toUTF8($chunk);
      $this->parser->feed($chunk);
      $chunksProcessed++;

      $this->store();
      $this->setStateProperty('chunksProcessed', $chunksProcessed);
    }
  }

  /**
   *
   */
  public function drop() {
    $results = $this->dataStorage->retrieveAll();
    foreach ($results as $id => $data) {
      $this->dataStorage->remove($id);
    }
    $this->getResult()->setStatus(Result::STOPPED);
  }

  /**
   *
   */
  protected function store() {
    $recordNumber = $this->getStateProperty('recordNumber', 0);
    $records = [];
    foreach ($this->parser->getRecords() as $record) {
      // Skip the first record. It is the header.
      if ($recordNumber != 0) {
        // @todo Identify if we need to pass an id to the storage.
        $records[] = json_encode($record);
      }
      else {
        $this->setStorageSchema($record);
      }
      $recordNumber++;
    }
    if (!empty($records)) {
      $this->dataStorage->storeMultiple($records);
    }
    $this->setStateProperty('recordNumber', $recordNumber);
  }

  /**
   *
   */
  protected function setStorageSchema($header) {
    $schema = [];
    $this->assertUniqueHeaders($header);
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'type' => "text",
      ];
    }
    $this->dataStorage->setSchema($schema);
  }

  /**
   * Verify headers are unique.
   *
   * @param $header
   *   List of strings
   *
   * @throws \Exception
   */
  protected function assertUniqueHeaders($header) {
    if (count($header) != count(array_unique($header))) {
      $duplicates = array_keys(array_filter(array_count_values($header), function ($i) {
          return $i > 1;
      }));
      throw new \Exception("Duplicate headers error: " . implode(', ', $duplicates));
    }
  }

  /**
   *
   */
  public function getParser(): ParserInterface {
    return $this->parser;
  }

  /**
   *
   */
  protected function serializeIgnoreProperties(): array {
    $ignore = parent::serializeIgnoreProperties();
    $ignore[] = "dataStorage";
    $ignore[] = "resource";
    return $ignore;
  }

}
