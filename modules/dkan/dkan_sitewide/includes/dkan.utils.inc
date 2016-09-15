<?php

/**
 * Given a url it performs a HEAD
 * request to retrive the mime type.
 * @param  String $url 
 *         URL of the resource to check
 * @return String      
 *         Mime type of the resource
 */
function dkan_remote_mime($url) {

  // This is a wrapper function to easily 
  // decouple this function for recline later.
  return recline_remote_mime($url);
}


/**
 * Discovers extension based off of mimetype.
 *
 * @param string $mimetype
 *        Mimetype to search for.
 *
 * @return string
 *         Extension or mimetype.
 */
function dkan_data_type($mimetype) {

  // This is a wrapper function to easily 
  // decouple this function for recline later.
  return recline_get_data_type($mimetype);
}