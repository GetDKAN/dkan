@page tut_harvest How to create a harvest

Use drush commands to [harvest](glossary.html#term_harvest) data into your catalog.

## Register a harvest

1. Register a new [Harvest Plan](glossary.html#term_harvestplan).
  - Create a unique name as the **identifier**
  - Provide the **extract** object with type and uri values: type being the class that matches the data structure, default is "\\Harvest\\ETL\\Extract\\DataJson")
  - Provide the full URI for the data source (such as `http://example.com/data.json` or `file://source/data.json`)
  - Provide the **load** object that defines the type of content you want to create, default is: "\\Drupal\\harvest\\Load\\Dataset"

2. If you would also like to make changes to the data you are harvesting, you can create custom  **transforms** that will modify the data before saving to your catalog. Add multiple transforms as an array.

  **Example**

      drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json

  You can view a list of all registered harvest plans with @ref dkanharvestlist

  ### How to create transforms
  @par
  Transforms allow you to modify what you are harvesting. [Click here](https://github.com/GetDKAN/socrata_harvest) to see an example of how you can create a custom module to add a transform class.
  @par
  **Example with a transform item**

      drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json  --transform="\\Drupal\\custom_module\\Transform\\CustomTransform"


## Run the harvest
Once you have registered a harvest source, run the import, passing in the identifier as an arguement

    drush dkan:harvest:run example

## View the status of the harvest
Navigate to `admin/dkan/harvest` to view the status of the extraction, the date the harvest was run, and the number of datasets that were added by the harvest. By clicking on the harvest ID, you will also see specific information about each dataset, and the status of the datastore import.
