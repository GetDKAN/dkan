<?php

namespace Drupal\datastore\DataDictionary;

use \ArrayAccess;

/**
 * Simple single dimensional map of token literals to output token strings.
 *
 * Used when compiling date format strings.
 */
interface CompilationMapInterface extends ArrayAccess {

}
