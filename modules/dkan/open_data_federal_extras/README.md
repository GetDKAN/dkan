[![Build Status](https://travis-ci.org/NuCivic/open_data_federal_extras.svg?branch=master)](https://travis-ci.org/NuCivic/open_data_federal_extras)

Open Data Federal Agency Extras module
========================

Open Data Federal Agency Extras module. Extends DKAN Dataset to include selected Project Open Data and other federal requirements

## Additional Fields

 * Bureau Code
 * Program Code
 * Data Quality
 * primary IT Investment UII
 * System of Records


### Requirements
Requires DKAN Dataset module.

### Update Program code list
1. Go to http://project-open-data.github.io/schema/#programCode
2. Download "Federal Program Inventory"
3. Export in csv to ``fed_program_code_list``
4. cd 'fed_program_code_list'
6. Make sure filenames at beginning of ``list_to_array.php`` are correct.
5. Run ``php -r " require 'list_to_array.php'; opfe_list_to_array_process();``
