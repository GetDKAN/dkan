@page harvest Harvest

The DKAN **Harvest** module provides integration with the [harvest](https://github.com/GetDKAN/harvest) library. This enables you to use the public feed or API of another data portal and import items from that portal’s catalog into your own.

For example, [Data.gov](https://data.gov/) harvests all of its datasets from the [data.json](https://project-open-data.cio.gov/v1.1/schema/) files of [hundreds of U.S. federal, state and local data portals](https://catalog.data.gov/harvest).

 A "harvest" is the execution of a [harvest plan](#harvest-plan).

## Drush Commands

| Command | Args | Notes |
| -- | -- | -- |
| dkan:harvest:list         | n/a          | Lists avaialble harvests. |
| dkan:harvest:register     | $config      | Register a new harvest, file saved to the *dkan_harvest/plans* directory. |
| dkan:harvest:deregister   | $identifier  | Deletes the cached plan file. |
| dkan:harvest:cache        | $identifier  | This will fetch the source data, apply the source configuration and cache the data to a local file. |
| dkan:harvest:run          | $identifier  | This will harvest the current cache for the selected sources. |
| dkan:harvest:revert       | $identifier  | Reverts harvest. |


<h2 id="harvest-plan">Harvest Plan schema</h2>
The harvest plan is the configuration used to import data into your catalog.
\ref https://github.com/GetDKAN/harvest/blob/master/schema/schema.json
<!-- /include blob/master/schema/schema.json -->

\code{.json}
  {
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$id": "harvest-plan",
  "type": "object",
  "title": "Harvest Plan",
  "required": [
    "identifier",
    "extract",
    "load"
  ],
  "properties": {
    "identifier": {
      "type": "string",
      "title": "The plan's identifier",
      "pattern": "^(.*)$"
    },
    "extract": {
      "type": "object",
      "title": "Extract",
      "required": [
        "type",
        "uri"
      ],
      "properties": {
        "type": {
          "type": "string",
          "title": "Class utilized to extract the data from the source."
        },
        "uri": {
          "type": "string",
          "title": "The URL or Location of the Source",
          "examples": [
            "http://demo.getdkan.com/data.json"
          ]
        }
      }
    },
    "transforms": {
      "type": "array",
      "title": "The Transforms for the Harvest",
      "additionalProperties": false,
      "items": {
        "type": "string"
      }
    },
    "load": {
      "type": "object",
      "title": "The Load settings for the Harvest",
      "required": [
        "type"
      ],
      "properties": {
        "type": {
          "type": "string",
          "title": "Class utilized to load the harvested data."
        }
      }
    }
  }
}
\endcode

## Setup

Currently there is no UI for creating or running a harvest, use the drush commands above to harvest data into your catalog.

1. Register a new harvest source, passing in the harvest plan configuration as JSON, wrapped in single quotes, do not add spaces between elements:
  - Create a unique name as the **identifier**
  - Provide the **extract** object with type and uri values: type being the class that matches the data structure, (most likely "\\Harvest\\ETL\\Extract\\DataJson"), and the full URI for the source endpoint (such as `http://source/data.json` or `file://source/data.json`)
  - Provide the **load** object that defines the type of content you want to create, most likely datasets, so use: "\\Drupal\\harvest\\Load\\Dataset"

2. If you would also like to make changes to the data you are harvesting, you can create custom **transforms** that will modify the data before saving to your catalog. Add multiple transforms as an array. [Learn more here](#transforms).

**Example**
\code{.bash}
drush dkan-harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
\endcode

## Run the harvest
Once you have registered a harvest source, run the import, passing in the identifier as an arguement `drush dkan-harvest:run example`

<h2 id="transforms">How to create transforms</h2>
Transforms allow you to modify what you are harvesting.
Let's say you want to harvest from a Socrata catalog and only want to import the distributions with csv files. [Create a custom module](https://www.drupal.org/docs/8/creating-custom-modules) with the following structure: custom_module/src/Harvest/Transform/Socrata.php

\code{.php}
<?php

namespace Drupal\custom_module\Harvest\Transform;

use Harvest\ETL\Transform\Transform;

/**
 * Class Socrata.
 */
class Socrata extends Transform {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function run($item) {
    // Convert URL identifier to just the ID.
    $identifier = $item->identifier;
    $item->identifier = $this->getIdentifier($identifier);

    // Add a keyword when keywords are null.
    if (empty($item->keyword)) {
      $item->keyword = ['No keywords provided'];
    }

    // Add a description if null.
    if (empty($item->description)) {
      $item->description = 'No description provided';
    }

    // Add titles for csv distributions.
    if ($item->distribution) {
      foreach ($item->distribution as $key => $dist) {
        if ($dist->mediaType != "text/csv") {
          unset($item->distribution[$key]);
        }
        else {
          $dist->title = "{$item->identifier}.csv";
          $item->distribution[$key] = $dist;
        }
      }
    }

    return $item;
  }

  /**
   * Private.
   *
   * Convert the url identifier to a non-url based identifier.
   */
  private function getIdentifier($identifier) {
    $path = parse_url($identifier, PHP_URL_PATH);
    $path = str_replace('/api/views/', "", $path);
    return $path;
  }
}
\endcode

**Example with new transform**

\code{.bash}
drush dkan-harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":["\\Drupal\\custom_module\\Transform\\Socrata"],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
\endcode
