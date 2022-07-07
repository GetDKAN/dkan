@page tut_data_dictionaries How to use Data Dictionaries

## Tutorial I: Catalog-wide data dictionary

The simplest way to use data dictionaries on your site is to create one for the entire catalog. To do this, let's first create a new dictionary using the API (a GUI process for this is not yet available). We are going to take our list of fields from one of the sample datasets that ships with DKAN, "Data on bike lanes in Florida."

```http
POST http://mydomain.com/api/1/metastore/schemas/data-dictionary/items
Authorization: Basic username:password

{
    "title": "Demo Dictionary",
    "data": {
        "fields": [
            {
                "name": "objectid",
                "type": "integer",
                "title": "OBJECTID"
            },
            {
                "name": "roadway",
                "type": "integer",
                "title": "ROADWAY"
            },
            {
                "name": "road_side",
                "type": "string",
                "title": "ROAD_SIDE"
            },
            {
                "name": "lncd",
                "type": "integer",
                "title": "LNCD"
            },
            {
                "name": "descr",
                "type": "string",
                "title": "DESCR"
            },
            {
                "name": "begin_post",
                "type": "number",
                "title": "BEGIN_POST"
            },
            {
                "name": "end_post",
                "type": "number",
                "title": "END_POST"
            },
            {
                "name": "shape_leng",
                "type": "number",
                "title": "Shape Leng"
            }
        ]
    }
}
```

We get a response that tells us the identifier for the new dictionary is `7fd6bb1f-2752-54de-9a33-81ce2ea0feb2`.

We now need to set the data dictionary mode to _sitewide_, and the sitewide data dictionary to this identifier. For now, we must do this through drush:

```sh
drush -y config:set metastore.settings data_dictionary_mode 1
drush -y config:set metastore.settings data_dictionary_sitewide 7fd6bb1f-2752-54de-9a33-81ce2ea0feb2
```

Both of these settings will be available in the admin UI soon.