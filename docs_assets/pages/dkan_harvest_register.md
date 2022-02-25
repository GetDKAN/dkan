@page dkanharvestregister dkan:harvest:register

Register a new harvest.

#### Arguments

- Harvest plan configuration as a JSON string. Wrap in single quotes, do not add spaces between elements.

#### Options
- **identifier** The harvest id.
- **extract-type** Extract type.
- **extract-uri** Extract URI.
- **transform** A transform class to apply. You may pass multiple transforms.
- **load-type** Load class.

#### Usage

    dkan-harvest:register '{"identifier":"myHarvestId","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"http://example.com/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'

Or

    dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json

#### Aliases

- dkan-harvest:register
