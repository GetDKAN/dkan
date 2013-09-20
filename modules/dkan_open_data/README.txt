NOTES:

The data.json file validates correctly with the Project Open Data validator: http://project-open-data.github.io/json-validator/

However the following changes should be made to the DKAN Dataset content type to ensure validation if that is a requirement:

+ Data Dictionary should be changed to a link field
+ Contact Person, Contact email, and Tags are required by Project Open Data so they should be marked as 'required' in the dataset content type

The following fields were left out, but could easily be added by adding them as fields to the dataset content type and either patching the dkan_open_data module file or copying the dkan_open_data module and using a custom version of it instead:

Data Quality
Category
Homepage URL
RSS Feed
System of Records
