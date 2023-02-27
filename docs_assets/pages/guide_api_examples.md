# API Examples

Each dataset page displays live examples for basic queries you can use for that dataset.
This page will show additional examples for the trickier usage.

## How to set the workflow state through the API.

1. Get the current workflow state and confirm there is at least one revision.


    GET https://[site-domain]/api/1/metastore/schemas/dataset/items/[identifier]/revisions


2. Let's say the returned result says the revision is published "true" and state "published", here is how we change the state to hidden.


    POST https://[site-domain]/api/1/metastore/schemas/dataset/items/[identifier]/revisions HTTP/1.1

    Authorization: Basic user:password

    {
        "state": "hidden",
        "message": "Testing state change"
    }


3. Run the GET again to confirm the state is now "hidden". The dataset is publically available if you know the URL but it will not be included in the site's search results.


## How to run a query against multiple tables with a JOIN.
Define the tables you want to query and give each an alias under "resouces". List the properties you want returned, if common data is using different headings in the table, set up an alias to collect the values to a single property in the results. Add any conditions you like to filter the data. Then add the join, defining the property and value to match.

    POST https://[site-domain]/api/1/datastore/query/[identifier]/0 HTTP/1.1
    Authorization: Basic user:password

    {
        "resources": [
            {
                "id": "07eaa697-694d-5aa9-a105-1dad5509fc47",
                "alias": "a"
            },
            {
                "id": "2fde366a-7026-54bc-bda5-63b5435afbd0",
                "alias": "b"
            }
        ],
        "properties": [
            {
              "resource": "a",
              "property": "first_name"
            },
            {
                "resource": "a",
                "property": "last_name"
            },
            {
                "resource": "b",
                "property": "state"
            },
            {
                  "resource": "b",
                  "property": "county"
            },
            {
              "alias": "postal_code",
              "expression": {
                "operator": "*",
                "operands": [
                    {
                        "resource": "a",
                        "property": "postal_code"
                    },
                    {
                        "resource": "b",
                        "property": "zip"
                    }
                ]
              }
            }
        ],
        "conditions": [
          {
            "resource": "a",
            "property": "carrier",
            "value": "75573",
            "operator": "="
          }
        ],
        "joins": [
            {
                "resource": "b",
                "on": [
                  {
                    "resource": "a",
                    "property": "id"
                  },
                  {
                    "resource": "b",
                    "property": "id"
                  }
                ]
            }
        ]
    }

## How to run a fulltext query on multiple columns.
Make sure that you have created fulltext indexes for the columns in the table. The default table alias is "t", if you are only querying one table, you can leave this line out "resource":"t".
Below would give you the first 5 results for service_type = "General" AND matches any word that starts with "knee" OR equals "ankle" in either the description or notes column.

    POST https://[site-domain]/api/1/datastore/query/[identifier]/0 HTTP 1.1

    {
      "offset":0,
      "limit":5,
      "rowIds":true,
      "conditions":[
        {
          "resource":"t",
          "property":"service_type",
          "value":"General",
          "operator":"="
        },
        {
          "groupOperator":"or",
          "conditions": [
            {
              "resource":"t",
              "property":"description, notes",
              "value":"knee*",
              "operator":"match"
            },
            {
              "resource":"t",
              "property":"description, notes",
              "value":"ankle",
              "operator":"match"
            }
          ]
        }
      ],
      "sorts":[
        {
          "property":"decision_date",
          "order":"desc"
        }
      ]
    }

