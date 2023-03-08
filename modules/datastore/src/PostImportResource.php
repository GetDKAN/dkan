<?php

namespace Drupal\datastore;

class PostImportResource {

  /**
   * Resource identifier.
   *
   * @var string
   */
  private $resourceIdentifier;

  /**
   * Resource version.
   *
   * @var string
   */
  private $resourceVersion;

  /**
   * Post import status.
   *
   * @var string
   */
  private $postImportStatus;

  /**
   * Post import message.
   *
   * @var string
   */
  private $postImportMessage;


  /**
   * PostImportResource constructor.
   */
  public function __construct(
    $resource_identifier,
    $resource_version,
    $post_import_status,
    $post_import_message
    ) {
    $this->resourceIdentifier = $resource_identifier;
    $this->resourceVersion = $resource_version;
    $this->postImportStatus = $post_import_status;
    $this->postImportMessage = $post_import_message;
  }

  public function setResults($resource_identifier, $resource_version, $post_import_status, $post_import_message) {
    $this->resourceIdentifier = $resource_identifier;
    $this->resourceVersion = $resource_version;
    $this->postImportStatus = $post_import_status;
    $this->postImportMessage = $post_import_message;

    return $this;
  }

  public function getResults() {
    return $this;
  }

  public function removeResults() {
    //unset() called from the PostImportResourceProcessor
    return TRUE;
  }

  // public function __destruct() {
  //   //destroy object
  // }

}
