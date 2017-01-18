<?php

namespace Drupal\DKANExtension\Context;

use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class DataStoryContext extends RawDKANEntityContext{

    public function __construct(){
        parent::__construct(
            'node',
            'dkan_data_story'
        );
    }

    /**
     * Creates data stories from table.
     *
     * @Given data stories:
     */
    public function addDataStories(TableNode $datastoriestable){
        parent::addMultipleFromTable($datastoriestable);
    }
}
