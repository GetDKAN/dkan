# Geocoding

DKAN's native Datastore offers a geocoder which can add a "lat" and "lon" field to resources that have plain-text address information. This will allow users to provide map coordinates and a map preview for selected resources.

To use the geocoder, click "Manage Datastore" on a resource that has a csv file uploaded to it with address fields.

## Dependencies

The [Geocoder](https://drupal.org/project/geocoder) module is required for geocoding. It is not included by default with DKAN but can be downloaded here: 

https://drupal.org/project/geocoder

## Instructions

Click the "Geolocate" button and select a source and the fields that will be used for the geocoding:

![geolocate](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-03-11%20at%208.46.51%20AM.png)

## Geolocation Services

Geolocation services offered are 

* [Google](https://developers.google.com/maps/articles/geocodestrat)
* [Yahoo](http://developer.yahoo.com/boss/geo/)
* [Nominatim](href="http://developer.mapquest.com/web/products/open/nominatim)
* [Yandex](http://api.yandex.com/maps/doc/geocoder/desc/concepts/input_params.xml)

Note that Nominatim is a driven by [Open Street Map](http://www.openstreetmap.org/) data, which is the most open of the options offered.

In the Geolocate Addressses field enter the field or fields from the file that make up the address to geolocate.

## Geolocation Limits

The number of rows that can be geolocated is determined by the service you select. Google, for example, allows you to geolocate up to 2500 times per day before paying.

### Adding Service API Keys

The [Geocoder](https://drupal.org/project/geocoder) module supports adding API keys for the Yahoo and Google services. Users can sign up for those services and, in Google's case, geocode up to 100,000 addressees per day.
