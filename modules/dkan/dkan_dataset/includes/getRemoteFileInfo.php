<?php

namespace dkanDataset;

/**
 * Encapsulate logic for retriving information from remote files.
 */
class GetRemoteFileInfo {

  /**
   * CURL header info of the remote URL.
   *
   * @var info
   */
  public $url;

  /**
   * Class constructor.
   */
  public function __construct($url, $agent, $followRedirect = TRUE) {
    $this->url = $url;
    $this->info = $this->getFileInfo($this->url);
  }

  /**
   * Gets header info for requested file.
   */
  public function getInfo() {
    return $this->info;
  }

  /**
   * Returns the content type for a remote file.
   */
  public function getType() {
    if ($info = $this->getInfo()) {
      if (!empty($info["Content-Type"])) {
        $content_types = array_values($info["Content-Type"]);
        $array_size = count($content_types);
        $last_element = $array_size - 1;

        $type = $content_types[$last_element];

        $pieces = explode(";", $type);

        return trim($pieces[0]);
      }
    }

    return NULL;
  }

  /**
   * Return a canonical file extension.
   *
   * Try to use the mimetype to return the best possible correct extension. Use
   * the parsed extension from the URL as backup.
   *
   * @return extension
   *   Content extension.
   */
  public function getExtension() {
    // Parse the extension from the URL.
    $path = parse_url($this->getEffectiveUrl(), PHP_URL_PATH);
    $extension_parsed = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if ($extension_parsed) {
      return $extension_parsed;
    }
    elseif ($this->getType()) {
      // Use drupal file mimetypes store.
      include_once DRUPAL_ROOT . '/includes/file.mimetypes.inc';
      $mimetype_mappings = file_mimetype_mapping();
      $mimetype_keys = array_keys($mimetype_mappings['mimetypes'], $this->getType());

      // If the destination mimetype in unknown to us then default to the
      // extension as parsed from the url.
      if (empty($mimetype_keys)) {
        return $extension_parsed;
      }

      // Get the candidate extensions from the mimetype_keys.
      $extensions_lookup = array();
      foreach ($mimetype_keys as $mimetype_key) {
        $extensions_lookup = array_merge($extensions_lookup,
          array_keys($mimetype_mappings['extensions'], $mimetype_key));
      }

      // If we couldn't find any potential candidates or the extension from the
      // url matches one of the candidate extensions then use it.
      if (empty($extensions_lookup) || in_array($extension_parsed, $extensions_lookup)) {
        return $extension_parsed;
      }

      // At this point we may have multiple candidate extensions and we couldn't
      // find the best one. Default to the first element.
      return array_pop($extensions_lookup);
    }
  }

  /**
   * Return effective_url (last URL after redirects).
   */
  public function getEffectiveUrl() {
    $info = $this->getInfo();

    if (!empty($info['Location'])) {
      $urls = array_values($info["Location"]);
      $array_size = count($urls);
      $last_element = $array_size - 1;

      $url = $urls[$last_element];

      return trim($url);
    }
    else {
      return $this->url;
    }
  }

  /**
   * Returns the name for a remote file.
   *
   * This doesn't just check the end of the string for the filename because
   * a file URL like this:
   * https://data.expamle.gov/api/views/abc-123/rows.csv?accessType=DOWNLOAD
   * will have a filename of 'this_file_name.csv' in the Content Disposition.
   */
  public function getName() {
    if ($info = $this->getInfo()) {
      $spellings = [
        'Content-Disposition',
        'Content-disposition',
        'content-disposition'
      ];

      foreach ($spellings as $spelling) {
        if (isset($info[$spelling]) && $name = $this->checkDisposition($info[$spelling])) {
          return $name;
        }
      }

      // Check URL for filename at end of string.
      if ($name = $this->getNameFromUrl()) {
        return $name;
      }
      else {
        return NULL;
      }
    }

    return NULL;
  }

  /**
   * Helper function.
   */
  private function getFileInfoHelper($url, $no_body = TRUE) {
    ob_start();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    if ($no_body) {
      curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Range: bytes=0-1000"));

    $ok = curl_exec($ch);

    curl_close($ch);

    $data = ob_get_contents();
    @ob_end_clean();

    if ($ok) {
      $info = $this->parseRequestData($data);
      if (empty($info['Content-Type'])) {
        return FALSE;
      }
      return $info;
    }

    return FALSE;
  }

  /**
   * Retrieves info from url.
   */
  private function getFileInfo($url) {
    if ($info = $this->getFileInfoHelper($url)) {
      return $info;
    }

    if ($info = $this->getFileInfoHelper($url, FALSE)) {
      return $info;
    }

    return FALSE;
  }

  /**
   * Converts headers from curl request to array.
   */
  private function parseRequestData($request_data) {
    $info = [];
    $pieces = explode(PHP_EOL, $request_data);

    foreach ($pieces as $piece) {
      $key_value = explode(":", $piece);
      if (count($key_value) >= 2) {
        $key = array_shift($key_value);
        $info[$key][] = implode(":", $key_value);
      }
    }

    return $info;
  }

  /**
   * Retrieves URL from end of string.
   */
  private function getNameFromUrl() {

    $url = $this->getEffectiveUrl();

    $parsed = parse_url($url);

    if (isset($parsed['path'])) {
      $pieces = explode('/', $parsed['path']);
      return $pieces[count($pieces) - 1];
    }

    return FALSE;
  }

  /**
   * Finds filename from Content Disposition header.
   */
  private function checkDisposition($disposition) {
    $disposition = array_shift($disposition);

    $regexes = [
      '/.*?filename=(.+)/i',
      '/.*?filename="(.+?)"/i',
      '/.*?filename=([^; ]+)/i'
    ];

    foreach ($regexes as $regex) {
      if (preg_match($regex, $disposition, $matches)) {
        return trim($matches[1]);
      }
    }

    if ($exploded = explode('filename=', $disposition)) {
      return trim($exploded[1]);
    }

    return FALSE;
  }

}
