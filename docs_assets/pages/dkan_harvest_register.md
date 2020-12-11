@page dkanharvestregister dkan:harvest:register

Register a new harvest.

#### Arguments

- **harvest_plan**. Harvest plan configuration as JSON, wrapped in single quotes, do not add spaces between elements.

#### Example

<code>
dkan-harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
</code>

#### Aliases

- dkan-harvest:register

@note <i class="fas fa-fire" style="color: #42b983"></i> Legend
    - An argument or option with square brackets is optional.
    - Any default value is listed at end of arg/option description.
    - An ellipsis indicates that an argument accepts multiple values separated by a space.
