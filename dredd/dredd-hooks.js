/*
 * Hooks to add or modify request information.
 */

const hooks = require('hooks');

/*
 * Skip the following tests until our database contains more relevant data,
 * or we can create some as needed.
 */
endpointsToSkip = [
  '/api/v1/{property} > Create a property > 201 > application/json',
  '/api/v1/{property}/{uuid} > Get a property > 200 > application/json',
  '/api/v1/{property}/{uuid} > Replace a property > 200 > application/json',
  '/api/v1/{property}/{uuid} > Update a property > 200 > application/json',
  '/api/v1/{property}/{uuid} > Delete a property > 200 > application/json',
  '/api/v1/sql/{query} > Query resources in datastore > 200 > application/json',
  '/api/v1/harvest > Register a new harvest > 200 > application/json',
  '/api/v1/harvest/info/{id} > List previous runs for a harvest id > 200 > application/json',
  '/api/v1/harvest/info/{id}/{run_id} > Information about a specific previous harvest run > 200 > application/json',
  '/api/v1/harvest/info/{id}/{run_id} > Information about a specific previous harvest run > 200 > application/json',
  '/api/v1/harvest/run/{id} > Runs a harvest > 200 > application/json',
  '/api/v1/datastore/{uuid} > Return a dataset with datastore headers and statistics > 200 > application/json',
  '/api/v1/datastore/{uuid} > Drop a datastore > 200 > application/json',
  '/api/v1/datastore/import/{uuid} > Datastore import > 200 > application/json',
  '/api/v1/datastore/import/{uuid}/deferred > Deferred datastore import > 200 > application/json',
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
  '/api/v1/dataset > Create a dataset > 201 > application/json',
  '/api/v1/dataset > Create a dataset > 409 > application/json',
  '/api/v1/dataset/{uuid} > Replace a dataset > 200 > application/json',
  '/api/v1/dataset/{uuid} > Update a dataset > 200 > application/json',
  '/api/v1/dataset/{uuid} > Delete a dataset > 200 > application/json',
  '/api/v1/{property} > Create a property > 201 > application/json',
  '/api/v1/{property}/{uuid} > Replace a property > 200 > application/json',
  '/api/v1/{property}/{uuid} > Update a property > 200 > application/json',
  '/api/v1/{property}/{uuid} > Delete a property > 200 > application/json',
  '/api/v1/harvest > Register a new harvest > 200 > application/json',
  '/api/v1/harvest/run/{id} > Runs a harvest > 200 > application/json',
  '/api/v1/datastore/{uuid} > Drop a datastore > 200 > application/json',
  '/api/v1/datastore/import/{uuid} > Datastore import > 200 > application/json',
  '/api/v1/datastore/import/{uuid}/deferred > Deferred datastore import > 200 > application/json',
];
endpointsRequiringAuth.forEach(endpoint => hooks.before(endpoint, (transaction) => {
  transaction.request.headers.Authorization = 'Basic dGVzdHVzZXI6Mmpxek9BblhTOW1tY0xhc3k=';
}));
