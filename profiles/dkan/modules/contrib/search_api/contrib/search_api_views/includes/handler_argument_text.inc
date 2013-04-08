<?php

/**
 * Views argument handler class for handling fulltext fields.
 */
class SearchApiViewsHandlerArgumentText extends SearchApiViewsHandlerArgument {

  /**
   * Get the title this argument will assign the view, given the argument.
   *
   * This usually needs to be overridden to provide a proper title.
   */
  public function title() {
    return t('Search for "@arg"', array('@field' => $this->definition['title'], '@arg' => $this->argument));
  }

}
