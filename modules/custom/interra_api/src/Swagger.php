<?php

namespace Drupal\interra_api;

use Drupal\Component\Serialization\Yaml;
use Drupal\interra_api\Load;
use Drupal\dkan_schema\Schema;

class Swagger {

  private $interraConfigDir = 'profiles/dkan2/modules/custom/interra_api/config';

  private function loadConfig() {
    return Yaml::decode(file_get_contents($this->interraConfigDir . '/siteMap.yml'));
  }

  public function load() {
    $swagger = $this->initial();
    $swagger['paths'] = $this->paths();
    $swagger['definitions'] = $this->definitions();
    return $swagger;
  }

  private function initial() {
    $siteConfig = \Drupal::config('system.site');
    $s = [
      "swagger" => "2.0",
      "info" => [
        'description' => 'Catalog APIs. This user interface is generated with swagger. The full definition can be viewed at /api/v1/swagger.json',
        'version' => '0.0.1',
        'title' => $siteConfig->get('name') . ' API',
      ],
      "paths" => [],
      'host' => \Drupal::request()->getHost(),
      'basePath' => '/api/v1/',
      'schemes' => ['http', 'https']
    ];
    return $s;
  }

  private function definitions () {
    $schema = new Schema();
    $collections = $schema->config['collections'];
    $definitions = [];
    foreach ($collections as $collection) {
      $definitions[$collection] = $schema->loadFullSchema($collection);
    }
    return $definitions;
  }

  private function paths () {
    $schema = new Schema();
    $paths  = [
      '/schema.json' => [
        'get' => [
          'summary' => 'Schema for the catalog',
          'operationId' => 'schema',
          'description' => 'A list of all of the schemas for the catalog.',
          'produces' => [ 'application/json' ],
          'responses' => [
            200 => [
              'description' => 'This is a file so will either be 200 or 404',
              'schema' => [
                'type' => 'object',
                'properties' => [
                  'collections' => [
                    'type' => 'array',
                    'items' => [
                      'type' => 'string',
                      'title' => 'Collections',
                      'description' => 'A list of strings of the collections in the catalog.',
                    ],
                  ],
                  'schema' => [
                    'type' => 'object',
                    'title' => 'Schema',
                    'description' => 'Schemas for all of the catalog collections',
                  ],
                  'map' => [
                    'type' => 'object',
                    'description' => 'A mapping of expected keys for collections and the actual value. For example every collection should have an identifier. Map allows implementing a different key for identifier or other required keys.',
                    'title' => 'Map',
                  ],
                  'uiSchema' => [
                    'type' => 'object',
                    'title' => 'UISchema',
                    'description' => 'A UISchema for the forms for each collection. See Mozilla\'s react-json-schema-form for details.',
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
      // TODO: Add these.
      //'/search-index.json',
      //'/sitemap.json',
      //'/swagger.json',
    ];
    $collections = $schema->config['collections'];
    foreach ($collections as $collection) {
      $paths['/collections/' . $collection . '.json'] = [
        'get' => [
          'summary' => 'All results for ' . $collection . ' collection',
          'operationId' => $collection . 'Collection',
          'produces' => [ 'application/json' ],
          'responses' => [
            200 => [
              'description' => 'This is a file so will either be 200 or 404',
              'schema' => [
                'type' => 'array',
                'items' => [
                  '$ref' => '#/definitions/' . $collection,
                ],
              ],
            ],
          ],
        ],
      ];
      $paths['/collections/' . $collection . '/{document}.json'] = [
        'get' => [
          'description' => 'An individual document in the ' . $collection,
          'parameters' => [[
            'in' => 'path',
            'name' => 'document',
            'type' => 'string',
            'required' => true,
            'description' => 'The identifier for the document.',
          ]],
          'operationId' => $collection . 'CollectionDoc',
          'produces' => [ 'application/json' ],
          'responses' => [
            200 => [
              'description' => 'This is a file so will either be 200 or 404',
              'schema' => [
                'type' => 'array',
                'items' => [
                '$ref' => '#/definitions/' . $collection,
                ],
              ],
            ],
          ],
        ],
      ];
    }
    return $paths;
  }

}
