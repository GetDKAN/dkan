# Project Open Data

DKAN complies with the <a href="https://project-open-data.cio.gov/">Project Open Data</a> (POD) requirements by providing fields that map to POD's required fields and publishing them to a Data.json file.

NuCivic has also published the <a href="https://github.com/NuCivic/open_data_federal_extras">Open Data Federal Extras</a> module which includes several additional POD fields.

The Data.json file required by POD is produced by the <a href="https://github.com/NuCivic/open_data_schema_map">Open Data Schema Map</a> (ODSM) module which creates a user interface that maps POD fields to DKAN fields and global settings.

DKAN does not provide all of the non-required fields. However it is easy to add global values for those using the <a href="https://github.com/NuCivic/open_data_schema_map">ODSM</a> module or by <a href="/dkan-documentation/dkan-developers/adding-fields-dkan">adding new fields</a> to DKAN.

## Default Map Between POD Fields and DKAN Fields

Below is a table with a default map between POD's fields and DKAN's fields. It is easy to add or adjust these mappings in DKAN by adding new fields or changing the settings in the <a href="https://github.com/NuCivic/open_data_schema_map">ODSM</a> module.

Here are Project Open Data v1.1 fields and how they map to DKAN fields:

### Catalog
|Field|Label|Required|DKAN Field|Open Data Federal Extras Field|Open Data Schema Map|
|----|----|-----|-----|------|------|
|@context|Metadata Context|No|||X|
|@id|Metadata Catalog ID|No|||X|
|@type|Metadata Type|No|||X|
|conformsTo|Schema Version|Always|||X|
|describedBy|Data Dictionary|No|||X|
|dataset|Dataset|Always|||X|

### Dataset
|Field|Label|Required|DKAN Field|Open Data Federal Extras Field|Open Data Schema Map|
|----|----|-----|-----|------|------|
|@type|Metadata Type|No|||x|
|title|Title|Always|Title|||
|description|Description|Always|Description|||
|keyword|Tags|Always|Tags|||
|modified|Last Update|Always|Changed|||
|publisher|Publisher|Always|Groups|||
|contactPoint|Contact Name and Email|Always|Contact Name|||
|identifier|Unique Identifier|Always|Identifier|||
|accessLevel|Public Access Level|Always|Public Access Level|||
|bureauCodeUSG|Bureau Code|Always||Bureau Code||
|programCodeUSG|Program Code|Always||Program Code||
|license|License|If-Applicable|License|||
|rights|Rights|If-Applicable|Public Access Level|||
|spatial|Spatial|If-Applicable|Spatial / Geographical Coverage Area|||
|temporal|Temporal|If-Applicable|Temporal Coverage|||
|distribution|Distribution|If-Applicable|Resources|||
|accrualPeriodicity|Frequency|No|Frequency|||
|conformsTo|Data Standard|No||||
|dataQualityUSG|Data Quality|No||||
|describedBy|Data Dictionary|No|Data Dictionary|||
|describedByType|Data Dictionary Type|No||||
|isPartOf|Collection|No||||
|issued|Release Date|No|Published|||
|language|Language|No|||X|
|landingPage|Homepage URL|No||Landing Page||
|primaryITInvestmentUIIUSG|Primary IT Investment UII|No||||
|references|Related Documents|No|Related Content|||
|systemOfRecordsUSG|System of Records|No||||
|theme|Category|No||||

### Distribution Fields

|Field|Label|Required|DKAN Field|Open Data Federal Extras Field|Open Data Schema Map|
|----|----|-----|-----|------|------|
|@type|Metadata Type|No|||X|
|accessURL|Access URL|If-Applicable|Field Upload or Field Link|||
|conformsTo|Data Standard|No||||
|describedBy|Data Dictionary|No||||
|describedByType|Data Dictionary Type|No||||
|description|Description|No|Description|||
|downloadURL|Download URL|If-Applicable|Field Upload or Field Link|||
|format|Format|No|Format*|||
|mediaType|Media Type|If-Applicable|MimeType*|||
|title|Title|No|Title|||

* These are not actual fields in DKAN but stored by field data.

The "x" for ODSM means that values for that are most likely best handled by a global value instead of per Dataset value. Those values are stored and rendered by ODSM.

## Notes on Fields Not Defined by DKAN by Default

A couple of notes from above. 

There are several non-required fields that can be implemented either globally using ODSM or by adding additional fields to DKAN and mapping them with <a href="https://github.com/NuCivic/open_data_schema_map">ODSM</a>. 

<a href="/dkan-documentation/dkan-developers/adding-fields-dkan">Adding additional fields</a> to DKAN takes minutes and does not require any coding.

The fields not included in DKAN or ODFE are:

* Data Standard
* Data Quality
* Data Dictionary Type
* Collection
* Primary IT Investment UII
* System of Records
* Category

The fields like "Data Standard" and "Data Quality" may be the same for the entire Catalog. If that is the case it makes more sense to use the ODSM to define the values. This is true for all of the fields listed above except for "Category". The "Category" would most likely be different for each Dataset.