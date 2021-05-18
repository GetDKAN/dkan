/*
 * Hooks to add or modify request information.
 */

const hooks = require('hooks');

/*
 * Skip the following tests until our database contains more relevant data,
 * or we can create some as needed.
 */
endpointsToSkip = [
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Get a property > 200 > application/json',
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Replace a property > 200 > application/json',
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Update a property > 200 > application/json',
  '/api/1/datastore/sql > Query resources in datastore > 200 > application/json',
  '/api/1/harvest/plans > Register a new harvest > 200 > application/json',
  '/api/1/harvest/plans/{plan_id} > Get single harvest plan > 200 > application/json',
  '/api/1/harvest/runs > List previous runs for a harvest id > 200 > application/json',
  '/api/1/harvest/runs > Run a harvest > 200 > application/json',
  '/api/1/harvest/runs/{run_id} > Information about a previous run > 200 > application/json',
  '/api/1/datastore/imports > Datastore import > 200 > application/json',
  '/api/1/datastore/imports/{identifier} > Datastore statistics > 200 > application/json',
  '/api/1/datastore/imports/{identifier} > Delete a datastore > 200 > application/json',
  '/api/1/datastore/imports/{identifier} > Delete a datastore > 200 > application/json',
  '/api/1/datastore/query > Query one or more datastore resources > 200 > application/json',
  '/api/1/datastore/query/download > Query one or more datastore resources for file download > 200 > application/json',
  '/api/1/datastore/query/download > Query one or more datastore resources for file download with get > 200 > application/json',
  '/api/1/datastore/query/{identifier} > Query a single datastore resource > 200 > application/json',
  '/api/1/datastore/query/{identifier} > Query a single datastore resource with get > 200 > application/json',
  '/api/1/datastore/query/{identifier}/download > Query a single datastore resources for file download > 200 > application/json',
  '/api/1/datastore/query/{identifier}/download > Query a single datastore resources for file download with get > 200 > application/json'

];
endpointsToSkip.forEach(endpoint => hooks.before(endpoint, (transaction) => {
  transaction.skip = true;
}));

/*
 * Using Dredd hooks to set the 64-bit encoded username:password for basic
 * authorization.
 *
 * Another attempt at doing this via dredd.yml's `user` option did not work as
 * intended since it applied it indiscriminately for all request paths and
 * verbs.
 */
endpointsRequiringAuth = [
  '/api/1/metastore/schemas/dataset/items > Create a dataset > 201 > application/json',
  '/api/1/metastore/schemas/dataset/items > Create a dataset > 409 > application/json',
  '/api/1/metastore/schemas/dataset/items/{identifier} > Replace a dataset > 200 > application/json',
  '/api/1/metastore/schemas/dataset/items/{identifier} > Replace a dataset > 403 > application/json',
  '/api/1/metastore/schemas/dataset/items/{identifier} > Update a dataset > 200 > application/json',
  '/api/1/metastore/schemas/dataset/items/{identifier} > Delete a dataset > 200 > application/json',
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Get a property > 200 > application/json',
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Replace a property > 200 > application/json',
  '/api/1/metastore/schemas/{schema_id}/items/{identifier} > Update a property > 200 > application/json',
  '/api/1/harvest/plans > List harvest identifiers > 200 > application/json',
  '/api/1/harvest/plans > Register a new harvest > 200 > application/json',
  '/api/1/harvest/plans/{plan_id} > Get single harvest plan > 200 > application/json',
  '/api/1/harvest/runs > List previous runs for a harvest id > 200 > application/json',
  '/api/1/harvest/runs > Run a harvest > 200 > application/json',
  '/api/1/harvest/runs/{run_id} > Information about a previous run > 200 > application/json',
  '/api/1/datastore/imports > List datastores > 200 > application/json',
  '/api/1/datastore/imports > Datastore import > 200 > application/json',
  '/api/1/datastore/imports/{identifier} > Delete a datastore > 200 > application/json',
];
endpointsRequiringAuth.forEach(endpoint => hooks.before(endpoint, (transaction) => {
  transaction.request.headers.Authorization = 'Basic dGVzdHVzZXI6Mmpxek9BblhTOW1tY0xhc3k=';
}));
