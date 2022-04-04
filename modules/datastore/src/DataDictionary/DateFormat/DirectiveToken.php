<?php

namespace Drupal\datastore\DataDictionary\DateFormat;

use Drupal\datastore\DataDictionary\TokenInterface;

/**
 * Frictionless/Strptime date format directive token.
 *
 * E.g. '%a', '%w', '%d'...
 */
class DirectiveToken extends LiteralToken implements TokenInterface {

}
