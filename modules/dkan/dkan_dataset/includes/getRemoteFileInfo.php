<?php

namespace dkanDataset;

/**
 * Encapsulate logic for retriving information from remote files.
 */
class GetRemoteFileInfo {

  /**
   * Class constructor.
   */
  public function __construct($url, $agent, $followRedirect = TRUE, $tmp = '/tmp') {
    $this->url = $url;
    $this->agent = $agent;
    $this->followRedirect = $followRedirect;
    $this->tmp = $tmp;
  }

  /**
   * Retrieves headers from url.
   */
  public function curlHeader($url, $agent, $followRedirect, $tmp) {
    $info = array();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
    curl_setopt($ch, CURLOPT_FILETIME, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);

    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
    curl_setopt($ch, CURLOPT_COOKIE, "");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    $http_heading = curl_exec($ch);
    if (!$http_heading) {
      return NULL;
    }
    $info['header'] = $this->httpParseHeaders($http_heading);
    $info['info'] = curl_getinfo($ch);
    curl_close($ch);
    // If the server didn't support HTTP HEAD, use the shim.
    if ((!empty($info['header']['X-Error-Message']) && trim($info['header']['X-Error-Message']) == 'HEAD is not supported')
        || empty($info['header']['Content-Type'])) {
      return $this->curlHeadShim($url, $agent, $followRedirect, $tmp);
    }
    else {
      return $info;
    }
  }

  /**
   * Saves file to temp dir to parse header.
   */
  private function curlHeadShim($url, $agent, $followRedirect, $tmp) {
    $info = array();
    $ch = curl_init();
    $output = fopen('/dev/null', 'w');
    $header_dir = $tmp . '/curl_header';
    $headerfile = fopen($header_dir, 'w+');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $output);
    curl_setopt($ch, CURLOPT_WRITEHEADER, $headerfile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_exec($ch);
    fclose($headerfile);
    $http_heading = file_get_contents($header_dir);
    unset($header_dir);
    $info['info'] = curl_getinfo($ch);
    curl_close($ch);
    $info['header'] = $this->httpParseHeaders($http_heading);
    return $info;
  }

  /**
   * Gets header info for requested file.
   */
  public function getInfo() {
    if (!isset($this->info)) {
      $this->info = $this->curlHeader($this->url, $this->agent, $this->followRedirect, $this->tmp);
    }
    return $this->info;
  }

  /**
   * Returns the content type for a remote file.
   */
  public function getType() {
    if ($info = $this->getInfo()) {
      $type = $info['header']['Content-Type'];
      if ($explode = explode(";", $type)) {
        return $explode[0];
      }
      else {
        return $type;
      }
    }
    else {
      return NULL;
    }
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
   * a file URL like this
   * https://data.expamle.gov/api/views/abc-123/rows.csv?accessType=DOWNLOAD will
   * have a filename of 'this_file_name.csv' in the Content Disposition.
   */
  public function getName() {
    if ($info = $this->getInfo()) {
      // Check Location for proper URL.
      if (isset($info['header']['Location']) && valid_url($info['header']['Location'])) {
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
