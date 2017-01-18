<?php

/**
 * @file
 * Base MigrateItem class for Harvest Migrations.
 *
 * Should be a simpler files retriving impletation for locally stored files.
 */

/**
 * Base MigrateItem class for Harvest Migrations.
 */
class HarvestItem extends MigrateItem {

  /**
   * A uri string template for locally cached harvest files.
   *
   * @var string
   */
  protected $itemUriSubject;

  /**
   * Constructor.
   *
   * @param string $item_uri_subject
   *        Item URI.
   */
  public function __construct($item_uri_subject) {
    parent::__construct();
    $this->itemUriSubject = $item_uri_subject;
  }

  /**
   * {@inheritdoc}
   *
   * Implementors are expected to return an object representing a source item.
   */
  public function getItem($id) {
    // Make sure we actually have an ID.
    if (empty($id)) {
      return NULL;
    }
    $item_uri = $this->constructItemUrl($id);
    // And make sure we actually got a URL to fetch.
    if (empty($item_uri)) {
      return NULL;
    }
    // Get the XML object at the specified URL.
    $item_content = $this->loadItemContent($item_uri);
    if ($item_content !== FALSE) {
      return $item_content;
    }
    else {
      $migration = Migration::currentMigration();
      $message = t('Loading of !objecturl failed:', array('!objecturl' => $item_uri));
      $migration->getMap()->saveMessage(
        array($id), $message, MigrationBase::MESSAGE_ERROR);
      return NULL;
    }
  }

  /**
   * Creates a valid URL pointing to current item.
   *
   * The default implementation simply replaces the :id token in the URL with
   * the ID obtained from MigrateListXML. Override if the item URL is not so
   * easily expressed from the ID.
   *
   * @param mixed $id
   *        XML item ID.
   *
   * @return string
   *         Formatted string with replaced tokens.
   */
  protected function constructItemUrl($id) {
    return str_replace(':id', $id, $this->itemUriSubject);
  }

  /**
   * Loads the XML.
   *
   * Default XML loader - just use Simplexml directly. This can be overridden
   * for preprocessing of XML (removal of unwanted elements, caching of XML if
   * the source service is slow, etc.)
   *
   * @param string $item_uri
   *        URL to the XML file.
   *
   * @return SimpleXMLElement
   *         Loaded XML
   */
  protected function loadItemContent($item_uri) {
    return file_get_contents($item_uri);
  }

}
