Open Data Federal Extras module
========================

This module extends DKAN Dataset to include selected Project Open Data and other federal requirements. See the [Project Open Data Schema](https://project-open-data.cio.gov/v1.1/schema/) for more information (this module essentially adds the fields marked "USG"). It includes a list of U.S. federal bureau and program codes, with a script to keep program codes up-to-date.

## Additional Fields

 * Bureau Code
 * Program Code
 * Data Quality
 * primary IT Investment UII
 * System of Records

Enabling the module will add these fields to your Dataset content type. Note that _disabling_ the module will _not_ remove them. To remove the fields completely (which will permanently delete all data in those fields), _uninstall_ the module from the module administration screen or via `drush pm-uninstall`. 

### Updating the Program Code list
1. Go to http://project-open-data.github.io/schema/#programCode
2. Download "Federal Program Inventory"
3. Export in csv to ``fed_program_code_list``
4. cd 'fed_program_code_list'
6. Make sure filenames at beginning of ``list_to_array.php`` are correct.
5. Run ``php -r " require 'list_to_array.php'; opfe_list_to_array_process();``

### The Bureau Code List

The Bureau Code list is built from a CSV downloaded from [seanherron/OMB-Agency-Bureau-and-Treasury-Codes](https://github.com/seanherron/OMB-Agency-Bureau-and-Treasury-Codes). This repository has not been updated recently and new sources for this data are currently being considered. 
