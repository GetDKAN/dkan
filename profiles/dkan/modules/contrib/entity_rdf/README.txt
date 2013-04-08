Entity RDF module
-----------------

The Entity RDF module is a replacement for the Drupal 7 core RDF module offering a tight integration between the RDF mappings and Entity API. Each RDF mapping is attached to its appropriate property definition in the the Entity API property info.

The Entity RDF module attempts to solve some of the issues and shortcomings of the Drupal 7 core RDF module:
- RDF mappings can not only be assigned to entity properties and fields but also to field internal values, which is useful for compound fields such as addressfield for example
- References to classes and properties are stored as full URIs to avoid depending on prefix bindings (the use of CURIEs is left up to the administration UI and/or the serialization)
- RDF mappings are no longer carried around in the entity object (the stucture info is separate from the data)
- RDF mappings are available in the entity metadata wrapper
- Default RDF mappings are no longer automatically set for common properties, but instead they need to be enabled. Suggestions for mappings can be provided to the administrator setting the mappings.



Mappings are visible Entity API's property info, for example:
entity_metadata_wrapper('node', 1)->getPropertyInfo();
entity_get_property_info('node');
