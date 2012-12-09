<?php

class JJRelationGridField extends GridField {

	public function __construct($name, $title = null, $owner = null, $relationName = '', GridFieldConfig_RecordEditor $config = null) {
		if (!$config) {
			$config = GridFieldConfig_RecordEditor::create();
		}

		$dataList = null;
		// check if session var is set, else use the whole class

		$class = '';
		if ($owner->$relationName() instanceof DataList) {
			$dataList = $owner->$relationName();
			$class = $dataList->dataClass();
		} else {
			// one-to relation
			$class = $owner->$relationName()->class;
			$dataList = DataList::create($class)->where('ID=' . $owner->$relationName()->ID);
		}

		//$config->removeComponentsByType('GridFieldDeleteAction');
		$config->addComponent(new GridFieldToggleRelationAction($owner, $relationName, $dataList));
		$config->addComponent(new GridFieldRelationManager($owner->class, $relationName));

		$showRelated = Session::get('GridField_' . $owner->class . '_' . $relationName . '_showRelated');
		
		$list = $showRelated ? $dataList : DataList::create($class);


		parent::__construct($name, $title, $list, $config);
	}

}