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
  public $info = FALSE;
  public $url;
  public $agent;
  public $followRedirect;

  /**
   * Class constructor.
   */
  public function __construct($url, $agent, $followRedirect = TRUE) {
    $this->url = $url;
    $this->agent = $agent;
    $this->followRedirect = $followRedirect;

    $this->info = $this->curlHeader($this->url, $this->agent, $this->followRedirect);
  }

  /**
   * Retrieves headers from url.
   */
  public function curlHeader($url, $agent, $followRedirect) {
    $info = array();

    $ch = $this->getBaseCh($url, $agent, $followRedirect);

    // This changes the request method to HEAD. No need to "GET" the hole link.
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    $http_heading = curl_exec($ch);

    if (!$http_heading) {
      // Should set the GetRemoteFileInfo::$info to false.
      return FALSE;
    }

    $info['header'] = $this->httpParseHeaders($http_heading);
    $info['info'] = curl_getinfo($ch);
    $info['effective_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return $info;
  }

  /**
   * Helper method to construct a base cURL handle.
   */
  private function getBaseCh($url, $agent, $followRedirect) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    // Spoof the User Agent.
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);

    // Wait only 5 seconds.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    // Return the transfer as a string of the return value of curl_exec()
    // instead of outputting it out directly.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    // Follow redirects.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

    // Force the use of a new connection instead of a cached one.
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

    // Attempt to retrieve the modification date of the remote document.
    curl_setopt($ch, CURLOPT_FILETIME, TRUE);

    // Cookies.
    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
    curl_setopt($ch, CURLOPT_COOKIE, "");

    // Include the header in the output.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);

    return $ch;
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
      $type = $info['header']['Content-Type'];
      // If the url had redirects, CURL will stack the Content Types from all
      // the urls. Get the last url.
      if (is_array($type)) {
        $type = array_pop($type);
      }

      if ($explode = explode(";", $type)) {
        return $explode[0];
      }
      else {
        return $type;
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

    if (is_null($this->getType())) {
      return $extension_parsed;
    }

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

  /**
   * Return effective_url (last URL after redirects).
   */
  public function getEffectiveUrl() {
    $info = $this->getInfo();
    if (!empty($info)) {
      return $info['effective_url'];
    }
    return FALSE;
  }

  /**
   * Retrieves URL from end of string.
   */
  public function getNameFromUrl() {
    $basename = basename($this->url);
    $name = explode('.', $basename);
    if (count($name) > 2) {
      $name = parse_url($basename);
      if (isset($name['path'])) {
        return $name['path'];
      }
    }
    elseif (count($name) == 1) {
      return $name[0];
    }
    return FALSE;
  }

  /**
   * Finds filename from Content Disposition header.
   */
  public function checkDisposition($disposition) {
    if (preg_match('/.*?filename=(.+)/i', $disposition, $matches)) {
      return trim($matches[1]);
    }
    elseif (preg_match('/.*?filename="(.+?)"/i', $disposition, $matches)) {
      return trim($matches[1]);
    }
    elseif (preg_match('/.*?filename=([^; ]+)/i', $header, $matches)) {
      return trim($matches[1]);
    }
    elseif ($exploded = explode('filename=', $disposition)) {
      return trim($exploded[1]);
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
      // Check Location for proper URL.
      // When URL have redirects the ['header']['Location'] will be an array.
      if (isset($info['header']['Location']) && is_array($info['header']['Location'])) {
        $location = $info['header']['Location'];
        $location = array_shift($location);
      }

      if (isset($location) && valid_url($location)) {
        if ($name = $this->getNameFromUrl($this->url)) {
          return $name;
        }
      }

      // Check content disposition.
      if (isset($info['header']['Content-Disposition'])) {
        return $this->checkDisposition($info['header']['Content-Disposition']);
      }
      elseif (isset($info['header']['Content-disposition'])) {
        return $this->checkDisposition($info['header']['Content-disposition']);
      }
      elseif (isset($info['header']['content-disposition'])) {
        return $this->checkDisposition($info['header']['content-disposition']);
      }
      // Check URL for filename at end of string.
      if ($name = $this->getNameFromUrl($this->url)) {
        return $name;
      }
      else {
        return NULL;
      }
    }
    else {
      return NULL;
    }
  }

  /**
   * Converts headers from curl request to array.
   */
  public function httpParseHeaders($raw_headers) {
    $headers = array();
    $key = '';
    foreach (explode("\n", $raw_headers) as $i => $h) {
      $h = explode(':', $h, 2);
      if (isset($h[1])) {
        if (!isset($headers[$h[0]])) {
          $headers[$h[0]] = trim($h[1]);
        }
        elseif (is_array($headers[$h[0]])) {
          $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
        }
        else {
          $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
        }
        $key = $h[0];
      }
      else {
        if (substr($h[0], 0, 1) == "\t") {
          $headers[$key] .= "\r\n\t" . trim($h[0]);
        }
        elseif (!$key) {
          $headers[0] = trim($h[0]);trim($h[0]);
        }
      }
    }
    return $headers;
  }

}
