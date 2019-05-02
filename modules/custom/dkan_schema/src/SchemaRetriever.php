<?php

namespace Drupal\dkan_schema;

use Contracts\Retriever;

class SchemaRetriever implements Retriever {

    private $directory;

    public function __construct()
    {
       $this->findSchemaDirectory();
    }

    public function getAllIds() {
        return [
          'dataset'
        ];
    }

    public function retrieve(string $id): ?string {
        if (in_array($id, $this->getAllIds())) {
            return file_get_contents($this->directory . "/collections/{$id}.json");
        }
        throw new \Exception("Schema {$id} not found.");
    }

    public function getSchemaDirectory() {
        return $this->directory;
    }

    private function findSchemaDirectory() {
        // Look at the root of drupal.
        if (file_exists(DRUPAL_ROOT . "/schema")) {
            $this->directory = DRUPAL_ROOT . "/schema";
        }
        // Otherwise we will use our default schema.
        else if (file_exists(__DIR__ . "/../../../../schema")) {
            $this->directory = __DIR__ . "/../../../../schema";
        }
        else {
            throw new \Exception("No schema found.");
        }
    }
}

