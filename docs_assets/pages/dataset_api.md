@page datasetapi Metastore API


## GET

GET is used to request data from the data catalog. No authentication is needed. $id = identifier of the dataset.

Get all of the datasets in the catalog:

```
​/api​/1​/metastore​/schemas​/dataset​/items
```

Get a specific dataset by adding the dataset identifier ($id = identifier value) to the end:

```
/api​/1​/metastore​/schemas​/dataset​/items​/$id
```

View the API of a specific dataset:

```
​/api​/1​/metastore​/schemas​/dataset​/items​/$id​/docs
```
<h2 id="identifiers">Identifiers</h2>
The dataset API has an additional endpoint that breaks out specific sub-elements
of the dataset schema (distribution, publisher, theme, and keyword) so that these
elements can be worked with as individual objects.
Each element is assigned a unique identifier in addition to it's original schema values,
this endpoint can be viewed at:

```
/api/1/metastore/schemas/dataset/items​/$id?show-reference-ids
```

The reference ids are used to make updates to dataset properties such as *distribution*, *publisher*, *theme*, or *keyword*. The property name is what you would use in place of $schema_id in the examples below.


@note
    POST, PUT, PATCH and DELETE commands require the following:
    - Basic Auth.
    - Headers need a **Content-Type** with **application/json**



## Log In

For actions that require authentication, the user must also have the **API User** role. This gives the user permission to post, put, and delete datasets through the api.

```json
POST http://domain.com/api/dataset/user/login
Content-Type: application/x-www-form-urlencoded
Accept: application/json

{
    "username": "{username}",
    "password": "{password}"
}
```

## POST

POST is used to send data to a server to create a dataset.
The data sent to the server with POST is stored in the request body of the HTTP request.

Create a dataset: `/api​/1​/metastore​/schemas​/dataset/items`

```json
POST http://domain.com/api​/1​/metastore​/schemas​/dataset​/items
Content-Type: application/json
Accept: application/json

{
    "title": "Title of Dataset",
    "description": "Description for dataset",
    "identifier": "03ac1e65-bec9-4e36-883d-181c04c95d99",
    "accessLevel": "public",
    "@type": "dcat:Dataset",
    "keyword": [
        "Keyword One",
        "Keyword Two",
        "Keyword Three"
    ],
    "issued": "2012-10-30",
    "modified": "2019-06-06",
    "contactPoint": {
        "@type": "vcard:Contact",
        "fn": "Firstname Lastname",
        "hasEmail": "mailto:first.last@example.com"
    },
    "theme": [
        "Topic One",
        "Topic Two"
    ],
    "publisher": {
        "@type": "org:Organization",
        "name": "Committee on International Affairs"
    },
    "distribution": [
        {
            "@type": "dcat:Distribution",
            "downloadURL": "http://dkan/sites/default/files/distribution/c9e2d352-e24c-4051-9158-f48127aa5692/district_centerpoints_0.csv",
            "mediaType": "text/csv",
            "format": "csv",
            "description": "Sample data resource file.",
            "title": "District Names"
        }
    ],
}
```

## PUT

PUT is used to replace content. PUT requests are idempotent, so calling the same PUT request multiple times will always produce the same result. In contrast, calling a POST request repeatedly has the side effect of creating the same content multiple times.

Replace a dataset: `/api​/1​/metastore​/schemas​/dataset/items​/$id`

```json
PUT http://domain.com/api​/1​/metastore​/schemas​/dataset​/items/03ac1e65-bec9-4e36-883d-181c04c95d99
Content-Type: application/json
Accept: application/json

{
    "title": "Title of Replacement Dataset",
    "description": "This will replace the existing dataset.",
    "identifier": "03ac1e65-bec9-4e36-883d-181c04c95d99",
    "accessLevel": "public",
    "@type": "dcat:Dataset",
    "keyword": [
        "red",
        "blue",
        "green"
    ],
    "contactPoint": {
        "@type": "vcard:Contact",
        "fn": "Firstname Lastname",
        "hasEmail": "mailto:first.last@example.com"
    },
    "theme": [
        "City Planning",
        "Education"
    ]
}
```

Replace a property: `/api​/1​/metastore​/schemas​/$schema_id/items​/$id`

```json
PUT http://domain.com/api​/1​/metastore​/schemas​/theme/items​/03ac1e65-bec9-4e36-883d-181c04c95d99
Content-Type: application/json
Accept: application/json
{
    "theme": [
        "Financing"
    ]
}
```

## PATCH

PATCH is used to make changes to part of the content in your catalog, so you only send the parameters which you want to update.

Update a dataset: `/api​/1​/metastore​/schemas​/dataset/items​/$id`

```json
PUT http://domain.com/api​/1​/metastore​/schemas​/dataset​/items​/03ac1e65-bec9-4e36-883d-181c04c95d99
Content-Type: application/json
Accept: application/json

{
    "description": "UPDATED description for dataset",
    "theme": [
        "Topic Two",
        "Topic Three"
    ]
}
```

Update a property: `/api​/1​/metastore​/schemas​/$schema_id/items​/$id`

```json
PATCH http://domain.com/api​/1​/metastore​/schemas​/keyword/items​/03ac1e65-bec9-4e36-883d-181c04c95d99
Content-Type: application/json
Accept: application/json

{
    "keyword": [
        "Example"
    ]
}
```

## DELETE

Delete a dataset: `api​/1​/metastore​/schemas​/dataset​/items/$id`

```
DELETE http://domain.com/api​/1​/metastore​/schemas​/dataset​/items/03ac1e65-bec9-4e36-883d-181c04c95d99
Content-Type: application/json
Accept: application/json
```

