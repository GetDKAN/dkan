<?php

namespace Drupal\datastore\Plugin\DkanApiDocs;

use Drupal\common\Plugin\DkanApiDocsBase;

/**
 * Docs plugin.
 *
 * @DkanApiDocs(
 *  id = "datastore_api_docs",
 *  description = "Datastore docs"
 * )
 */
class DatastoreApiDocs extends DkanApiDocsBase {

  public function spec() {
    $spec = $this->getDoc('datastore');
    $querySchema = self::filterJsonSchemaUnsupported($this->replaceRefs($this->getDoc('datastore', 'query')));
    $spec["components"]["schemas"]["datastoreQuery"] = $querySchema;

    // Requirements are slightly different if resource is present in path.
    $resourceQuerySchema = $this->resourceQueryAlter($querySchema);
    $spec["components"]["schemas"]["datastoreResourceQuery"] = $resourceQuerySchema;


    return $spec;
  }

  private function replaceRefs($schema) {
    array_walk_recursive($schema, function (&$value, $key) {
      if ($key == '$ref') {
        $value = str_replace(
          "#/definitions",
          "#/components/schemas/datastoreQuery/definitions",
          $value
        );
      }
    });
    return $schema;
  }

  private function resourceQueryAlter($schema) {
    unset($schema["properties"]["resources"]);
    unset($schema["properties"]["joins"]);
    unset($schema["definitions"]);
    $schema["title"] = $this->t("Datastore Resource Query");
    $schema["description"] .= ". When querying against a specific resource, the \"resource\" property is always optional. If you want to set it explicitly, note that it will be aliased to simply \"t\".";
    return $schema;
  }

}
