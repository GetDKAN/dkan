<?php

namespace Drupal\field_example\Plugin\Field\FieldType;

use Drupal\jsonb\Plugin\Field\FieldType\JsonItem;

/**
 * Plugin implementation of the 'json' field type.
 *
 * @FieldType(
 *   id = "json_schema",
 *   label = @Translation("JSON Schema Field"),
 *   description = @Translation("This field stores a JSON object or an array of JSON objects."),
 *   category = @Translation("Document"),
 *   default_widget = "jsonb_textarea",
 *   default_formatter = "jsonb_default"
 * )
 */
class JsonSchemaItem extends JsonItem {

}
