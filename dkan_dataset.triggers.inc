<?php

/**
 * Implements hook_node_update()
 */
function dkan_dataset_node_update($node){
	if($node->type == 'resource'){
		ctools_include('Dataset', 'dkan_dataset');
		foreach($node->field_dataset_ref['und'] as $dataset_nid){
			$dataset = Dataset::instance($dataset_nid['target_id']);
			$dataset->incrementRevision('Update to resource \''. $node->title . '\'');
			$dataset->save();
		}
	}
}
