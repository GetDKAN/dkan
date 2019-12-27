<?php

/**
 * @file
 * Zip up code for release and delete unneeded files.
 */

// Get latest release name.
$handler = curl_init('https://api.github.com/repos/GetDKAN/dkan/releases/latest');
curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($handler, CURLOPT_HTTPHEADER, [
  'User-Agent: DKAN Jenkins',
  'Accept: application/vnd.github.v3+json',
]);
$result = json_decode(curl_exec($handler));
curl_close($handler);
$dkan_version = $result->tag_name;

$file_name = "{$dkan_version}.zip";

if (!file_exists($file_name)) {
  `wget -O {$file_name} https://github.com/GetDKAN/dkan/archive/{$file_name}`;
}
else {
  echo "Already got the file {$file_name}" . PHP_EOL;
}

$folder_name = "dkan-{$dkan_version}";
if (!file_exists($folder_name)) {
  "Extracting {$file_name}" . PHP_EOL;
  `unzip {$file_name}`;
  echo "{$file_name} was extracted" . PHP_EOL;
}
else {
  echo "The file {$file_name} has already been extracted to {$folder_name}" . PHP_EOL;
}

$readme = file_get_contents("{$folder_name}/README.md");
if (substr_count($readme, $dkan_version) == 0) {
  echo "Adding version to README file" . PHP_EOL;
  $new_readme = str_replace("# DKAN Open Data Platform", "# DKAN Open Data Platform ({$dkan_version})", $readme);
  file_put_contents("{$folder_name}/README.md", $new_readme);
  echo "Added version to README file" . PHP_EOL;
}
else {
  echo "Version has already been added to the README file" . PHP_EOL;
}

$files = get_all_files_with_extension($folder_name, "info");
foreach ($files as $file) {
  add_version_to_info_file($file, $dkan_version);
}

$tar_file_name = "{$dkan_version}.tar.gz";
if (!file_exists($tar_file_name)) {
  echo "Compressing {$folder_name}" . PHP_EOL;
  `zip -9 -r {$file_name} {$folder_name}`;
  `tar -zcvf {$tar_file_name} {$folder_name}`;
  echo "{$folder_name} zip and tar.gz archives were created" . PHP_EOL;
}
else {
  echo "{$folder_name} has already been compressed";
}

// Upload assets to release.
upload_assets($dkan_version, $file_name, 'application/zip');
upload_assets($dkan_version, $tar_file_name, 'application/gzip');

function add_version_to_info_file($path, $version) {
  $content = file_get_contents($path);
  if (substr_count($content, "version") == 0) {
    echo "Adding version number to {$path}" . PHP_EOL;
    $content = trim($content);
    $content .= PHP_EOL . "version = {$version}" . PHP_EOL;
    file_put_contents($path, $content);
    echo "Adding version number to {$path}" . PHP_EOL;

  }
  else {
    echo "Version number already added to {$path}" . PHP_EOL;
  }
}

function get_all_files_with_extension($path, $ext) {
  $files_with_extension = [];
  $subs = get_all_subdirectories($path);
  foreach ($subs as $sub) {
    $files = get_files_with_extension($sub, $ext);
    $files_with_extension = array_merge($files_with_extension, $files);
  }
  return $files_with_extension;
}

function get_files_with_extension($path, $ext) {
  $files_with_extension = [];
  $files = get_files($path);
  foreach ($files as $file) {
    $e = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext == $e) {
      $files_with_extension[] = $file;
    }
  }
  return $files_with_extension;
}

function get_all_subdirectories($path) {
  $all_subs = [];
  $stack = [$path];
  while (!empty($stack)) {
    $sub = array_shift($stack);
    $all_subs[] = $sub;
    $subs = get_subdirectories($sub);
    $stack = array_merge($stack, $subs);
  }
  return $all_subs;
}

function get_subdirectories($path) {
  $directories_info = shell_table_to_array(`ls {$path} -lha | grep '^dr'`);
  $subs = [];
  foreach ($directories_info as $di) {
    if (isset($di[8])) {
      $dir = trim($di[8]);
      if ($dir != "." && $dir != "..") {
        $subs[] = "{$path}/{$dir}";
      }
    }
  }
  return $subs;
}

function get_files($path) {
  $files_info = shell_table_to_array(`ls {$path} -lha | grep -v '^dr'`);
  $files = [];
  foreach ($files_info as $fi) {
    if (isset($fi[8])) {
      $file = trim($fi[8]);
      $files[] = "{$path}/{$file}";
    }
  }
  return $files;
}

function shell_table_to_array($shell_table) {
  $final = [];
  $lines = explode(PHP_EOL, $shell_table);

  foreach ($lines as $line) {
    $parts = preg_split('/\s+/', $line);
    if (!empty($parts)) {
      $final[] = $parts;
    }
  }

  return $final;
}

function upload_assets($tag, $file_name, $content_type) {
  echo "Uploading asset {$file_name} for DKAN release {$tag}..." . PHP_EOL;

  // Request to get the release upload_url.
  $handler = curl_init('https://api.github.com/repos/GetDKAN/dkan/releases/tags/' . $tag);
  curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($handler, CURLOPT_HTTPHEADER, [
    'User-Agent: DKAN Jenkins',
    'Accept: application/vnd.github.v3+json',
  ]);
  $result = json_decode(curl_exec($handler));
  curl_close($handler);

  // Get real upload_url.
  $upload_url = $result->upload_url;
  $matches = [];
  if (preg_match('/(.*){\?name,label}/', $upload_url, $matches)) {
    $upload_url = $matches[1];
  } else {
    throw new \Exception("The upload URL is invalid.");
  }

  // Get filepath.
  $path = realpath($file_name);

  // Set params for POST to upload_url.
  $params = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 600,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
      "accept: application/vnd.github.v3+json",
      "authorization: token " . getenv('JENKINS_GITHUB_TOKEN'),
      "cache-control: no-cache",
      "content-type: " . $content_type,
    ),
    CURLOPT_POSTFIELDS => file_get_contents($path),
    CURLOPT_VERBOSE => 1,
  ];
  $real_upload_url = $upload_url . '?name=' . $file_name;
  echo "Uploading asset to {$real_upload_url}" . PHP_EOL;

  // POST file to upload_url.
  $handler = curl_init($real_upload_url);
  curl_setopt_array($handler, $params);
  $result = curl_exec($handler);
  $status_code = curl_getinfo($handler, CURLINFO_HTTP_CODE);
  curl_close($handler);

  if ($status_code >= 200 && $status_code < 300) {
    echo "Asset uploaded correctly." . PHP_EOL;
  } else {
    throw new \Exception("The asset was not uploaded.");
  }
}
