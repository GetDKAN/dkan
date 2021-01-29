@page tut_harvest How to create a harvest

## Setup

Use drush commands to harvest data into your catalog.

1. Register a new [harvest plan](#harvest-plan). A harvest plan is configuration as JSON, wrapped in single quotes, do not add spaces between elements:
  - Create a unique name as the **identifier**
  - Provide the **extract** object with type and uri values: type being the class that matches the data structure, (most likely "\\Harvest\\ETL\\Extract\\DataJson"), and the full URI for the source endpoint (such as `http://source/data.json` or `file://source/data.json`)
  - Provide the **load** object that defines the type of content you want to create, most likely datasets, so use: "\\Drupal\\harvest\\Load\\Dataset"

2. If you would also like to make changes to the data you are harvesting, you can create custom **transforms** that will modify the data before saving to your catalog. Add multiple transforms as an array.

**Example**
\code{.bash}
drush dkan:harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
\endcode

## Run the harvest
Once you have registered a harvest source, run the import, passing in the identifier as an arguement `drush dkan:harvest:run example`

## How to create transforms

Transforms allow you to modify what you are harvesting. [Click here](https://github.com/GetDKAN/socrata_harvest) to see an example of how you can create a custom module to add a transform class.

Example with new transform
\code{.bash}
drush dkan-harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":["\\Drupal\\custom_module\\Transform\\CustomTransform"],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
\endcode
