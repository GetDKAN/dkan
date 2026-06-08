<?php

namespace Drupal\dkan_metastore;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Schemas\ExceptionSchema;
use Opis\JsonSchema\ValidationContext;

/**
 * Tool to validate a JSON schema recursively.
 *
 * Compensates for the fact that opis/json-schema v2's SchemaLoader only reports
 * validation errors at the root level. Recurses through entire JSON Schema
 * document to find every "node" (usually a nested object) that behaves like a
 * schema and validates it.
 *
 * This may be worth releasing as a standalone library some day.
 */
class SchemaValidator {

  /**
   * Schema retriever service.
   */
  protected SchemaRetriever $retriever;


  /**
   * Opis Schema Parser.
   */
  protected SchemaParser $parser;

  /**
   * Generic Opis validation context, used for all validations.
   */
  protected ValidationContext $genericContext;

  /**
   * Opis Schema Loader, used to resolve every schema node.
   */
  protected SchemaLoader $loader;

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_metastore\SchemaRetriever $retriever
   *   Service to retrieve schema JSON by ID, for checkAllSchemas().
   */
  public function __construct(SchemaRetriever $retriever) {
    $this->retriever = $retriever;
    $this->parser = new SchemaParser();
    $this->loader = new SchemaLoader($this->parser);
    $this->genericContext = new ValidationContext([], $this->loader);
  }

  /**
   * Validate a schema by ID.
   *
   * @param string $id
   *   Metastore Schema ID (e.g. "dataset") to validate.
   *
   * @return string|null
   *   NULL on success; an error message string if the schema cannot be parsed.
   */
  public function validateSchemaId(string $id): ?string {
    try {
      $json = $this->retriever->retrieve($id);
    }
    catch (\Throwable $e) {
      return 'Could not retrieve schema: ' . $e->getMessage();
    }
    return $this->validateSchemaJson($json);
  }

  /**
   * Validate a JSON Schema document under opis/json-schema v2.
   *
   * @param string $json
   *   Schema JSON string.
   *
   * @return string|null
   *   NULL on success; an error message string if the schema cannot be parsed.
   */
  public function validateSchemaJson(string $json): ?string {
    $decoded = json_decode($json, FALSE);
    if ($decoded === NULL && json_last_error() !== JSON_ERROR_NONE) {
      return 'Schema is not valid JSON: ' . json_last_error_msg();
    }
    if (!is_object($decoded) && !is_bool($decoded)) {
      return 'Schema must be a JSON object or boolean.';
    }
    if (is_bool($decoded)) {
      return NULL;
    }

    try {
      $schema = $this->loader->loadObjectSchema($decoded);
      $schema->validate($this->genericContext);
    }
    catch (SchemaException | ParseException $e) {
      return $e->getMessage();
    }

    // Visit every genuine schema position (root first) and resolve it against
    // the cached tree, which preserves each node's draft/base/root/$ref.
    $nodes = [];
    $this->collectNodes($decoded, '#', $nodes);
    return $this->validateCollectedNodes($nodes);
  }

  /**
   * Validate collected schema nodes and return the first parse failure.
   *
   * @param array $nodes
   *   Collected [object $node, string $pointer] pairs.
   *
   * @return string|null
   *   First parse error found, or NULL when all nodes are valid.
   */
  protected function validateCollectedNodes(array $nodes): ?string {
    foreach ($nodes as [$node, $pointer]) {
      $schema = $this->loader->loadObjectSchema($node);
      if (!($schema instanceof ExceptionSchema)) {
        continue;
      }
      try {
        $error = $schema->validate($this->genericContext);
        $message = $error ? $error->message() : 'Could not parse schema at ' . $pointer;
      }
      catch (SchemaException $e) {
        $message = $e->getMessage();
      }
      return $message;
    }

    return NULL;
  }

  /**
   * Collect every genuine JSON Schema position in a decoded schema document.
   *
   * @param mixed $node
   *   Current node. Only objects are schema positions worth checking; booleans
   *   are valid schemas with nothing to descend into; anything else is ignored.
   * @param string $pointer
   *   JSON pointer of $node, for error reporting.
   * @param array $out
   *   Accumulator of [object $node, string $pointer] pairs.
   */
  protected function collectNodes($node, string $pointer, array &$out): void {
    if (!is_object($node)) {
      return;
    }
    $out[] = [$node, $pointer];

    $this->collectSchemaValuedNodes($node, $pointer, $out);
    $this->collectSchemaMapNodes($node, $pointer, $out);
    $this->collectSchemaListNodes($node, $pointer, $out);
    $this->collectItemsNodes($node, $pointer, $out);
    $this->collectDependenciesNodes($node, $pointer, $out);
    $this->collectSlotsNodes($node, $pointer, $out);
  }

  /**
   * Collect single-value sub-schema keywords.
   */
  protected function collectSchemaValuedNodes(object $node, string $pointer, array &$out): void {
    // Keywords whose value is a single sub-schema.
    $schema_valued = [
      'additionalProperties',
      'additionalItems',
      'unevaluatedProperties',
      'unevaluatedItems',
      'contains',
      'propertyNames',
      'not',
      'if',
      'then',
      'else',
      'contentSchema',
    ];
    foreach ($schema_valued as $keyword) {
      if (isset($node->$keyword)) {
        $this->collectNodes($node->$keyword, "{$pointer}/{$keyword}", $out);
      }
    }
  }

  /**
   * Collect object-map sub-schema keywords.
   */
  protected function collectSchemaMapNodes(object $node, string $pointer, array &$out): void {
    // Keywords whose value is a map of name => sub-schema.
    $schema_map = [
      'properties',
      'patternProperties',
      '$defs',
      'definitions',
      'dependentSchemas',
    ];
    foreach ($schema_map as $keyword) {
      if (isset($node->$keyword) && is_object($node->$keyword)) {
        foreach ($node->$keyword as $name => $sub) {
          $this->collectNodes($sub, "{$pointer}/{$keyword}/{$name}", $out);
        }
      }
    }
  }

  /**
   * Collect list-of-sub-schema keywords.
   */
  protected function collectSchemaListNodes(object $node, string $pointer, array &$out): void {
    // Keywords whose value is a list of sub-schemas.
    $schema_list = ['allOf', 'anyOf', 'oneOf', 'prefixItems'];
    foreach ($schema_list as $keyword) {
      $sub_schemas = $node->$keyword ?? NULL;
      if (!is_array($sub_schemas)) {
        continue;
      }
      foreach ($sub_schemas as $i => $sub) {
        $this->collectNodes($sub, "{$pointer}/{$keyword}/{$i}", $out);
      }
    }
  }

  /**
   * Collect item sub-schemas.
   */
  protected function collectItemsNodes(object $node, string $pointer, array &$out): void {
    // items: a single schema (object/bool) or an array of schemas.
    if (!isset($node->items)) {
      return;
    }
    if (!is_array($node->items)) {
      $this->collectNodes($node->items, "{$pointer}/items", $out);
      return;
    }
    foreach ($node->items as $i => $sub) {
      $this->collectNodes($sub, "{$pointer}/items/{$i}", $out);
    }
  }

  /**
   * Collect dependency sub-schemas.
   */
  protected function collectDependenciesNodes(object $node, string $pointer, array &$out): void {
    // The dependencies keyword (draft 6/7): an object/bool value is a schema;
    // an array value is a list of property names — not a schema, so skip it.
    $dependencies = $node->dependencies ?? NULL;
    if (!is_object($dependencies)) {
      return;
    }
    foreach ($dependencies as $name => $sub) {
      if (is_object($sub) || is_bool($sub)) {
        $this->collectNodes($sub, "{$pointer}/dependencies/{$name}", $out);
      }
    }
  }

  /**
   * Collect $slots fallback sub-schemas.
   */
  protected function collectSlotsNodes(object $node, string $pointer, array &$out): void {
    // The $slots opis extension (allowSlots defaults on): object/bool fallbacks
    // are sub-schemas; string fallbacks are slot names — skip those.
    $slots = $node->{'$slots'} ?? NULL;
    if (!is_object($slots)) {
      return;
    }
    foreach ($slots as $name => $fallback) {
      if (is_object($fallback) || is_bool($fallback)) {
        $this->collectNodes($fallback, "{$pointer}/\$slots/{$name}", $out);
      }
    }
  }

  /**
   * Fetch all registered schemas and check for validation failures.
   *
   * @return array
   *   Map of schema id => error message. Empty when all schemas are clean.
   */
  public function checkAllSchemas(): array {
    /** @var \Drupal\dkan_metastore\SchemaRetriever $retriever */
    $problems = [];
    foreach ($this->retriever->getAllIds() as $id) {
      if ($error = $this->validateSchemaId($id)) {
        $problems[$id] = $error;
      }
    }
    return $problems;
  }

}
