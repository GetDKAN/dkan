# DKAN Harvest

The prototype.

## Classes

### Harvest

Instantiated to run the entire harvest.

Example harvest might look like:

$config:

```json

{
  "destSchema": "pod-simple",
  "fileLocation": "/var/www/docroot/sites/default/files"
}
```

$harvests:

```json

[{
  "id": "dkandemo",
  "source": {
    "type": "DataJson",
    "uri": "http://demo.getdkan.com/data.json"
  },
  "transforms": [{
      "Filter": {
        "keyword": "environment"
      }
    }, {
      "Override": {
        "publisher.name": "DKAN Demo"
      }
    },
    "DataJsonToDkan"
  ],
  "load": {
    "migrate": false,
    "collectionsToUpdate": ["dataset"],
    "type": "Dkan8"
  }
}, {
  "id": "dkandemo2",
  "source": {
    "type": "DataJson",
    "uri": "http://demo.getdkan.com/data.json"
  },
  "transforms": [{
      "Filter": {
        "keyword": "bugdet"
      }
    }, {
      "Override": {
        "publisher.name": "DKAN Demo"
      }
    },
    "DataJsonToDkan"
  ],
  "load": {
    "migrate": true,
    "collectionsToUpdate": ["dataset"],
    "type": "Dkan8"
  }
}]
```

```php

$Harvest = new Harvest($config);
foreach ($harvests as $harvest) {
  // Initialize.
  $Harvest->init($harvest);
  // Save remote files locally.
  $Harvest->cache();
  // Pull from remote files.
  $items = $Harvest->extract();
  // Perform transforms like "filter" and "override" as well as put the json objects into to $node object and field_json_schema.
  $items = $Harvest->transform($items);
  // Save to DKAN.
  $Harvest->load($items);
}

