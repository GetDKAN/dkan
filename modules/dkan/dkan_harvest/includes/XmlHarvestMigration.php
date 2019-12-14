<?php

/**
 * XMLHarvestMigration trait -- use in other harvest classes to use XML source.
 *
 * For XML-based source, add the following to your harvest class' __construct():
 *
 * $this->source = new HarvestMigrateSourceList(
 *   new HarvestList($this->dkanHarvestSource->getCacheDir()),
 *   new MigrateItemXML($this->itemUrl),
 *   array(),
 *   $this->sourceListOptions
 * );
 */
abstract class XmlHarvestMigration extends HarvestMigration {

  /**
   * {@inheritdoc}
   *
   * So we can create our special field mapping class.
   *
   * @todo Find a cleaner way to just substitute a different mapping class.
   *
   * @param mixed $destination_field
   *   A machine-name of destination field.
   * @param mixed $source_field
   *   A name of source field.
   * @param bool $warn_on_override
   *   Set to FALSE to prevent warnings when there's an existing mapping
   *        for this destination field.
   *
   * @return MigrateXMLFieldMapping
   *   MigrateXMLFieldMapping
   */
  public function addFieldMapping($destination_field, $source_field = NULL, $warn_on_override = TRUE) {
    // Warn of duplicate mappings.
    if ($warn_on_override && !is_null($destination_field) && isset($this->codedFieldMappings[$destination_field])) {
      $this->reportMessage(
        t('!name addFieldMapping: !dest was previously mapped, overridden',
          array('!name' => $this->machineName, '!dest' => $destination_field)),
        MigrationBase::MESSAGE_WARNING);
    }
    $mapping = new MigrateXMLFieldMapping($destination_field, $source_field);
    if (is_null($destination_field)) {
      $this->codedFieldMappings[] = $mapping;
    }
    else {
      $this->codedFieldMappings[$destination_field] = $mapping;
    }
    return $mapping;
  }

  /**
   * Apply migration mappings.
   *
   * A normal $data_row has all the input data as top-level fields - in this
   * case, however, the data is embedded within a SimpleXMLElement object in
   * $data_row->xml. Explode that out to the normal form, and pass on to the
   * normal implementation.
   */
  protected function applyMappings() {
    // We only know what data to pull from the xpaths in the mappings.
    foreach ($this->getFieldMappings() as $mapping) {
      $source = $mapping->getSourceField();
      if ($source && !isset($this->sourceValues->{$source})) {
        $xpath = $mapping->getXpath();
        if ($xpath) {
          // Derived class may override applyXpath().
          $source_value = $this->applyXpath($this->sourceValues, $xpath);
          if (!is_null($source_value)) {
            $this->sourceValues->$source = $source_value;
          }
        }
      }
    }
    parent::applyMappings();
  }

  /**
   * Gets item from XML using the xpath.
   *
   * Default implementation - straightforward xpath application.
   *
   * @param object $data_row
   *   A row containing items.
   * @param string $xpath
   *   An xpath used to find the item.
   *
   * @return SimpleXMLElement
   *   Found element
   */
  public function applyXpath($data_row, $xpath) {
    $result = $data_row->xml->xpath($xpath);
    if ($result) {
      if (count($result) > 1) {
        $return = array();
        foreach ($result as $record) {
          $return[] = (string) $record;
        }
        return $return;
      }
      else {
        return (string) $result[0];
      }
    }
    else {
      return NULL;
    }
  }

  /*
   * Helper function to generate a string from an array of elements.
   *
   * @param SimpleXMLElement $element
   *   The XML to extract the string from.
   * @param string $xpath
   *   Xpath of string to be extracted.
   * @param bool $as_html
   *   If true, wrap each element in a <p> tag.
   *
   * @return string
   *   The generated string.
   */
  protected function stringIfExists(SimpleXMLElement $element, $xpath, $as_html=FALSE) {
    $result = $element->xpath($xpath);
    $return_string = '';
    if (!empty($result)) {
      foreach ($result as $item) {
        if ($as_html) {
          $return_string .= '<p>' . (string)$item . '</p>';
        } else {
          $return_string .= (string)$item . ' ';
        }
      }
    }
    return trim($return_string);
  }
}
