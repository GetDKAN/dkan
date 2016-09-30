# Datastore API

DKAN offers a Datastore API as a custom endpoint for the Drupal Services module. This API is designed to be as compatible as possible with the CKAN Datastore API: http://ckan.readthedocs.org/en/latest/maintaining/datastore.html The only documented difference is that the CKAN Datastore API supports POST requests while the DKAN Datastore API only supports GET requests. The DKAN Dataset APIs will support POST but the DKAN Datastore API will not. Requests can be sent over HTTP. Data can be returned as JSON, XML, or JSONP.

### Datastore API URL

Datastores can be queried at: http://EXAMPLE.COM/api/action/datastore/search The default return format is XML. JSON can be retrieved with '.json' at the end: http://EXAMPLE.COM/api/action/datastore/search.json

### Request Parameters

<table>

<tbody>

<tr>

<th>Parameters:</th>

<td>

*   **resource_id** (_mixed_) – id (string) or ids (array) of the resource to be searched against.
*   **filters** (_mixed_) – array or string of matching conditions to select
*   **q** (_string_) – full text query*
*   **offset** (_int_) – offset this number of rows
*   **limit** (_int_) – maximum number of rows to return (default: 100)
*   **fields** (_array or comma separated string_) – fields to return (default: all fields in original order)
*   **sort** (_string_) – comma separated field names with ordering
*   **join** (_array_) – array of fields to join from multiple tables

</td>

</tr>

</tbody>

</table>

### Return Values

Return is in XML, JSON or JSONP.

<table>

<tbody>

<tr>

<th>Return type:</th>

<td>

A dictionary with the following keys

</td>

</tr>

<tr>

<th>Parameters:</th>

<td>

*   **fields** (_list of fields_) – fields/columns and metadata
*   **offset** (_int_) – query offset value
*   **limit** (_int_) – query limit value
*   **count** (_int_) – number of total matching records
*   **records** (_list of dictionaries_) – list of matching results

</td>

</tr>

</tbody>

</table>

### Examples

The following is a simple example with two resources that contain 4 records each. Please note that the resource_id would be a UUID not single digit number in real scenario.

<pre>Resource: 1
+---------+-------------+----+------------+
| country | population  | id | timestamp  |
+---------+-------------+----+------------+
| US      | 315,209,000 |  1 | 1359062329 |
| CA      | 35,002,447  |  2 | 1359062329 |
| AR      | 40,117,096  |  3 | 1359062329 |
| JP      | 127,520,000 |  4 | 1359062329 |
+---------+-------------+----+------------+

Resource 2
+---------+-----------+----+------------+
| country | squarekm  | id | timestamp  |
+---------+-----------+----+------------+
| US      | 9,629,091 |  1 | 1359062713 |
| CA      | 9,984,670 |  2 | 1359062713 |
| AR      | 2,780,400 |  3 | 1359062713 |
| JP      | 377,930   |  4 | 1359062713 |
+---------+-----------+----+------------+
</pre>

#### Simple query example:

`http://example.com/api/dataset/search?resource_id=1&filters[country]=AR,US&fields=country,population,timestamp&sort[country]=asc` Returns the country, population, and timestamp fields for US and AR from dataset 1 sorting by the country in ascending order.

#### Text Search

Paths with the 'query' argument will search the listed fields within the dataset. `http://example.com/api/dataset/search?resource_id=1&&fields=country,population&query=US` Will return the country and population from US

#### Joining

If you wish to query multiple tables, indicate the table as an array key in the following fields: `http://example.com/api/dataset/search?resource_id[pop]=1&resource_id[size]=2&filters[pop][country]=US,AR&join[pop]=country&join[size]=country` Returns the country, population, squarekm and id for US and AR from datasets 11 and 13. * query search available in DKAN Datastore module as of 11/22/2013.