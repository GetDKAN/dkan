@page tut_harvest How to create a harvest

## Setup

Currently there is no UI for creating or running a harvest, use the drush commands above to harvest data into your catalog.

1. Register a new harvest source, passing in the harvest plan configuration as JSON, wrapped in single quotes, do not add spaces between elements:
  - Create a unique name as the **identifier**
  - Provide the **extract** object with type and uri values: type being the class that matches the data structure, (most likely "\\Harvest\\ETL\\Extract\\DataJson"), and the full URI for the source endpoint (such as `http://source/data.json` or `file://source/data.json`)
  - Provide the **load** object that defines the type of content you want to create, most likely datasets, so use: "\\Drupal\\harvest\\Load\\Dataset"

2. If you would also like to make changes to the data you are harvesting, you can create custom **transforms** that will modify the data before saving to your catalog. Add multiple transforms as an array. [Learn more here](#transforms).

**Example**
\code{.bash}
drush dkan:harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
\endcode

## Run the harvest
Once you have registered a harvest source, run the import, passing in the identifier as an arguement `drush dkan:harvest:run example`

## See a list of all registered harvests

`drush dkan:harvest:list`
