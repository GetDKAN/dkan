@page tut_dataset How to add a Dataset

## API

You will need to authenticate with a user account possessing the 'api user' role. Use _Basic Auth_.

Run a POST to `/api/1/metastore/schemas/dataset/items` with a json formatted request body, the minimal elements are:

<code>
{
  "title": "My new dataset",
  "description": "Description for my new dataset.",
  "identifier": "11111111-1111-4111-1111-111111111111",
  "accessLevel": "public",
  "modified": "2020-02-02",
  "keyword": [
    "test"
  ]
}
</code>

## GUI

1. Log in to the site.
2. Navigate to Admin > Content > Datasets.
3. Click the "+ Add new dataset" button.
4. Use the _Download URL_ field to enter a url to your file.
5. Fill in the form and click "submit".
