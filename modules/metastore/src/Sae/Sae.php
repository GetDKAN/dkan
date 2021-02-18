<?php

declare(strict_types = 1);

namespace Drupal\metastore\Sae;

use Drupal\metastore\Storage\MetastoreStorageInterface;
use JsonSchema\Validator;
use Contracts\BulkRetrieverInterface;
use Contracts\StorerInterface;
use Contracts\RemoverInterface;
use Contracts\IdGeneratorInterface;
use Rs\Json\Merge\Patch;

/**
 * Class Sae.
 *
 * The Services API Engine coordinates the interactions
 * between data validation and manipulating the
 * data appropriately.
 *
 * It supports these interactions for the http verbs:
 * GET, POST, PUT, PATCH and DELETE.
 *
 * @package Sae
 */
class Sae
{
  /**
   * @var \Contracts\StorerInterface
   */
    private $storage;
    private $jsonSchema;

  /**
   * @var \Contracts\IdGenerator
   */
    private $idGenerator;

//    public function __construct(StorerInterface $storage, string $json_schema)
    public function __construct(MetastoreStorageInterface $storage, string $json_schema)
    {
        $this->storage = $storage;
        $this->jsonSchema = $json_schema;
    }

    public function setIdGenerator(IdGeneratorInterface $id_generator)
    {
        $this->idGenerator = $id_generator;
    }

  /**
   * Get.
   *
   * @param string $id
   *   The identifier for the data we are getting.
   *
   * @return string|array|null
   *   The data.
   *
   * @throws \Exception
   *   No data with the identifier was found, or the storage
   *   does not support bulk retrieval of data.
   */
    public function get(string $id = null)
    {
        if (isset($id)) {
            return $this->storage->retrieve($id);
        } elseif ($this->storage instanceof BulkRetrieverInterface) {
            return $this->storage->retrieveAll();
        } else {
            throw new \Exception(
                'Neither data for the id, nor storage supporting bulk retrieval found.'
            );
        }
    }

  /**
   * Post.
   *
   * @param string $json_data
   *   The data as a json string.
   *
   * @return string
   *   The identifier for the data.
   *
   * @throws \Exception
   *   If the data is invalid, or could not be stored.
   */
    public function post(string $json_data): string
    {

        $validation_info = $this->validate($json_data);
        if (!$validation_info['valid']) {
            throw new \Exception(json_encode((object) $validation_info));
        }

        $id = null;
        if ($this->idGenerator) {
            $id = $this->idGenerator->generate();
        }
        return $this->storage->store($json_data, "{$id}");
    }

  /**
   * Put.
   *
   * @param string $id
   *   The identifier for the data we are getting.
   *
   * @param string $json_data
   *   The data as a json string.
   *
   * @return string|array|null
   *   The data.
   *
   * @throws \Exception
   *   If the data is invalid, or could not be stored.
   */
    public function put(string $id, string $json_data)
    {
        $validation_info = $this->validate($json_data);
        if (!$validation_info['valid']) {
            throw new \Exception(json_encode((object) $validation_info));
        }

        return $this->storage->store($json_data, "{$id}");
    }

  /**
   * Patch.
   *
   * @param string $id
   *   The identifier for the data we are getting.
   *
   * @param string $json_data
   *   The data as a json string.
   *
   * @return string|array|null
   *   The data.
   *
   * @throws \Exception
   *   If the data is invalid, or could not be stored.
   */
    public function patch(string $id, string $json_data)
    {
        $json_data_original = $this->storage->retrieve($id);
        if (!$json_data_original) {
            return false;
        }

        $data_original = json_decode($json_data_original);
        $data = json_decode($json_data);
        $patched = (new Patch())->apply(
            $data_original,
            $data
        );

        $new = json_encode($patched);

        $validation_info = $this->validate($new);
        if (!$validation_info['valid']) {
            throw new \Exception(json_encode((object) $validation_info));
        }

        return $this->storage->store($new, "{$id}");
    }

  /**
   * Delete.
   *
   * @param string $id
   *   The identifier for the data we are getting.
   *
   * @return bool
   *   True if the identifier was found and delete, false otherwise.
   */
    public function delete(string $id)
    {
        return $this->storage->remove($id);
    }

  /**
   * Validate.
   *
   * @param string $json_data
   *   The data as a json string.
   *
   * @return array
   *   The validation result.
   */
    public function validate(string $json_data)
    {
        $data = json_decode($json_data);

        $validator = new Validator();
        $validator->validate($data, json_decode($this->jsonSchema));

        $is_valid = $validator->isValid();

        return ['valid' => $is_valid, 'errors' => $validator->getErrors()];
    }
}
