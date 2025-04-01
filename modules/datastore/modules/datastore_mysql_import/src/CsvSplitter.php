<?php

namespace Drupal\datastore;

use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use League\Csv\Buffer;
use League\Csv\Reader;
use League\Csv\Writer;

class CsvSplitter {

  protected ResourceMapper $resourceMapper;

  public function __construct(
    ResourceMapper $resourceMapper,
  ) {
    $this->resourceMapper = $resourceMapper;
  }

  public function chunkify(int $start_record, int $chunk_size, string $identifier, ?string $version = NULL) {
    $data_resource = $this->resourceMapper->get($identifier, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $version);
    $path = $data_resource->getFilePath(TRUE);
    $reader = Reader::createFromPath($path);
    $writer = Writer::createFromPath($path . 'chunk.csv');
    $tabular_data_reader = $reader->slice($start_record, $chunk_size);

    $document = Reader::createFromPath('path/to/file.csv');
    $document->setHeaderOffset(0);
    $altBuffer = Buffer::from($document->slice(0, $write_chunk_length));
    $writer = Writer::createFromPath('/path/to/output.csv');
    $buffer->to($writer, Buffer::EXCLUDE_HEADER);


    // Manage the write chunk so we don't end up writing every single record.
    // We can't use array_chunk() because it would turn the iterator into an
    // array and fill memory.
    $write_chunk_count = 0;
    $write_chunk_length = 100;
    $write_chunk = [];
    foreach ($tabular_data_reader as $row) {
      $write_chunk[] = $row;
      if (++$write_chunk_count >= $write_chunk_length) {
        $write_chunk_count = 0;
        $writer->insertAll($write_chunk);
        $write_chunk = [];
      }
    }
  }

}
