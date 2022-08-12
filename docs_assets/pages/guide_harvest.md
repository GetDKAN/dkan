# How to create a harvest {#guide_harvest}

Use drush commands to [harvest](glossary.html#term_harvest) data into your catalog.

## Register a harvest

  Register a new [Harvest Plan](glossary.html#term_harvestplan).
  - Create a unique name as the **identifier** of your harvest
  - Provide the full URI for the data source

  **Example**

      drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json

  You can view a list of all registered harvest plans with @ref dkanharvestlist


## Run the harvest
  Once you have registered a harvest source, run the import, passing in the identifier as an arguement

    drush dkan:harvest:run myHarvestId

## View the status of the harvest
Navigate to `admin/dkan/harvest` to view the status of the extraction, the date the harvest was run, and the number of datasets that were added by the harvest. By clicking on the harvest ID, you will also see specific information about each dataset, and the status of the datastore import.

## Transforms
If you would also like to make changes to the data you are harvesting, you can create custom  **transforms** that will modify the data before saving it to your catalog. Add multiple transforms as an array.

 ### How to create transforms
  @par
  Transforms allow you to modify what you are harvesting. [Click here](https://github.com/GetDKAN/socrata_harvest) to see an example of how you can create a custom module to add a transform class.
  @par
  **Example with a transform item**

      drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json  --transform="\\Drupal\\custom_module\\Transform\\CustomTransform"
