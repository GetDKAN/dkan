# DKAN Harvest

The prototype.

## Setup

Currently there is no UI for the harvests. To setup run the following in the database:

```
INSERT INTO harvest_source (source_id, config) VALUES ('dkandemo', '{"sourceId":"dkandemo","source":{"type":"DataJson","uri":"http:\/\/demo.getdkan.com\/data.json"},"transforms":[{"Filter":{"keyword":"environment"}},{"Override":{"publisher.name":"DKAN Demo"}},"DataJsonToDkan"],"load":{"migrate":false,"collectionsToUpdate":["dataset","publisher", "license", "theme", "keyword"],"type":"Dkan8"}}');
```

That source config looks like:

```json

{
  "sourceId": "dkandemo",
  "source": {
    "type": "DataJson",
    "uri": "http:\/\/demo.getdkan.com\/data.json"
  },
  "transforms": [
    {
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
    "collectionsToUpdate": ["dataset", "organization", "license", "theme", "keyword"],
    "type": "Dkan8"
  }
}
```

About the load configs:

* **migrate**: whether or not files should be downloaded locally
* **collectionsToUpdate**: after the initial harvest you might not want to update anything but datasets.
* **type**: Here is where we could create a D7 version if desired.

## Harvest Config

There is also internal configuration for each harvest that is separate from the harvest source:

* **log**: You can use the ``harvest_log`` table or stdout.
* **destSchema**: The destination schema. Should be the current site schema.
* **fileLocation**: Where to save the cached files. Defaults to ``sites/default/files``

## Drush Commands

| Command | Args |  Notes |
|-------| ----|-----|
| dkan-harvest:list | | Lists avaialble harvests.|
| dkan-harvest:cache | $sourceId  | Caches harvest locally  to file system. |
| dkan-harvest:run | $sourceId  | Runs harvest. |
| dkan-harvest:revert | $sourceId  | Reverts harvest. |

