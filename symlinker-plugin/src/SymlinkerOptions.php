<?php

namespace Dkan\Composer\Plugin\Symlinker;

/**
 * Per-project options from the 'extras' section of the composer.json file.
 *
 * Projects that describe scaffold files do so via their scaffold options. This
 * data is pulled from the 'file-system-Symlink' portion of the extras section
 * of the project data.
 *
 * @internal
 */
class SymlinkerOptions {

  /**
   * The raw data from the 'extras' section of the top-level composer.json file.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Creates a scaffold options object.
   *
   * @param array $extras
   *   The contents of the 'extras' section.
   *
   * @return self
   *   The scaffold options object representing the provided scaffold options
   */
  public static function create(array $extras) {
    return new self(
      $extras['symlinker-plugin'] ?? [],
      $extras['drupal-scaffold'] ?? []
    );
  }

  /**
   * ScaffoldOptions constructor.
   *
   * @param array $options
   *   The options taken from the 'symlinker-plugin' section.
   * @param array $scaffoldOptions
   *   The scaffold options used to configure Drupal's scaffolding plugin.
   */
  protected function __construct(array $options, array $scaffoldOptions = []) {
    // Some defaults for locations.
    $project_root = FALSE;
    $web_root = FALSE;

    // Grab from symlinker.
    if ($value = $options['locations']['project-root'] ?? FALSE) {
      $project_root = $value;
    }
    if ($value = $options['locations']['web-root'] ?? FALSE) {
      $web_root = $value;
    }

    // Grab from scaffold.
    if ($value = $scaffoldOptions['locations']['project-root'] ?? FALSE) {
      if ($project_root !== FALSE && $project_root !== $value) {
        throw new \Exception('symlinker plugin configuration locations:project-root can not override Drupal scaffold plugin.');
      }
      $project_root = $value;
    }
    if ($value = $scaffoldOptions['locations']['web-root'] ?? FALSE) {
      if ($web_root !== FALSE && $web_root !== $value) {
        throw new \Exception('symlinker plugin configuration locations:web-root can not override Drupal scaffold plugin.');
      }
      $web_root = $value;
    }

    $this->options = $options + [
        "symlink-on-install-update" => true,
        "locations" => [],
        "file-mapping" => [],
      ];

    // Define any default locations.
    $this->options['locations'] += [
      'project-root' => $project_root ?? '.',
      'web-root' => $web_root ?? '.',
    ];
  }

  public function symlinkOnInstallUpdate() {
    return $this->options['symlink-on-install-update'];
  }

  public function notSymlinkedMessage() {
    if ($message = $this->options['not-processed-message'] ?? FALSE) {
      return $message;
    }
    return [
      "The symlinker plugin never performed the symlinks.",
      "Please run: composer makesymlinks",
    ];
  }

  /**
   * Gets the location mapping table, e.g. 'webroot' => './'.
   *
   * @return array
   *   A map of name : location values
   */
  public function locations() {
    return $this->options['locations'];
  }

  /**
   * Determines whether a given named location is defined.
   *
   * @param string $name
   *   The location name to search for.
   *
   * @return bool
   *   True if the specified named location exist.
   */
  protected function hasLocation($name) {
    return array_key_exists($name, $this->locations());
  }

  /**
   * Gets a specific named location.
   *
   * @param string $name
   *   The name of the location to fetch.
   *
   * @return string
   *   The value of the provided named location
   */
  public function getLocation($name) {
    return $this->hasLocation($name) ? $this->locations()[$name] : FALSE;
  }

  /**
   * Determines if there are file mappings.
   *
   * @return bool
   *   Whether or not the scaffold options contain any file mappings
   */
  public function hasFileMapping() {
    return !empty($this->fileMapping());
  }

  /**
   * Returns the actual file mappings.
   *
   * @return array
   *   File mappings for just this config type.
   */
  public function fileMapping() {
    return $this->options['file-mapping'];
  }

}
