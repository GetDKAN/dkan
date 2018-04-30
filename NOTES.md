# What is this?

You know what this is and why we are here.

# Approach

* Create very basic D8 install profile with a dataset content type
* Create read endpoints that mimic interra's
* Setup example where D8 DKAN spits out aformentioned endpoints with Interra
  front end
* Viola

# Drupal 8 Steps

* Use D8 core
* Create base profile
* Create dataset content type
* Export dataset content type
* Create custom endpoints
  /schema.json
  /routes.json
  /collections/organization.json
  /collections/organization/{document}.json
  /collections/distribution.json
  /collections/distribution/{document}.json
  /collections/keyword.json
  /collections/keyword/{document}.json
  /collections/license.json
  /collections/license/{document}.json
  /collections/theme.json
  /collections/theme/{document}.json
  /collections/dataset.json
  /collections/dataset/{document}.json
* Viola


