Dataset API
=============

GET
---

GET is used to request data from the data catalog. No authentication is needed. 

.. code-block::

    # Get all of the datasets
    http://dkan/api/v1/dataset

    # Get a specific dataset by adding the dataset identifier to the end
    http://dkan/api/v1/dataset/{identifier of your dataset}


.. note::
  
    POST, PUT, PATCH and DELETE commands require the following:
    
    - Basic Auth. 
    - Headers need a **Content-Type** with **application/json** 


Log In
------

For actions that require authentication, the user must also have the 'API User' role. This gives the user permission to post, put, and delete datasets through the api.

.. code-block::

    POST http://docker:32774/api/dataset/user/login
    Content-Type: application/x-www-form-urlencoded
    Accept: application/json

    {
      "username": "{username}",
      "password": "{password}"
    }


POST
----

POST is used to send data to a server to create/update a dataset.

The data sent to the server with POST is stored in the request body of the HTTP request

.. code-block:: 

    POST http://dkan/api/v1/dataset
    Content-Type: application/json
    Accept: application/json

    {
        "title": "Title of Dataset",
        "description": "Description for dataset",
        "identifier": "03ac1e65-bec9-4e36-883d-181c04c95d99",
        "accessLevel": "public",
        "bureauCode": ["1234:56"],
        "@type": "dcat:Dataset",
        "keyword": [
            "Keyword One",
            "Keyword Two",
            "Keyword Three"
        ],
        "contactPoint": {
            "@type": "vcard:Contact",
            "fn": "Firstname Lastname",
            "hasEmail": "mailto:first.last@example.com"
        },
        "theme": [
            "Topic One",
            "Topic Two"
        ]
    }



PUT
-------

PUT is used to update content in your catalog. PUT requests are idempotent, so calling the same PUT request multiple times will always produce the same result. In contrast, calling a POST request repeatedly has the side effect of creating the same content multiple times.

.. code-block::

  PUT http://dkan/api/v1/dataset/{identifier of your dataset}
  Content-Type: application/json
  Accept: application/json

  {
    "description": "UPDATED description for dataset",
    "theme": [
        "Topic Two",
        "Topic Three"
    ]
  }

DELETE
--------

Delete a dataset

.. code-block:: 

  DELETE http://dkan/api/v1/dataset/{identifier of your dataset}
  Content-Type: application/json
  Accept: application/json

