<?php

class GridFieldToggleRelationAction extends GridFieldDeleteAction {

	protected $relationList = null;

	protected $ownerModel = null;
	protected $relationName = '';

	public function __construct(DataObject $owner, String $relationName, DataList $relationList) {
		$this->ownerModel = $owner;
		$this->relationName = $relationName;
		$this->relationList = $relationList;

	}
	/**
	 * Which GridField actions are this component handling
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getActions($gridField) {
		return array('togglerelation');
	}

	/* GridField_ColumnProvider */
	
	function augmentColumns($gridField, &$columns)
	{
		if(!in_array('ToggleRelation', $columns)) $columns[] = 'ToggleRelation';
	}
	
	function getColumnsHandled($gridField)
	{
		return array('ToggleRelation');
	}
	
	/*function getColumnContent($gridField, $record, $columnName)
	{
		$cb = CheckboxField::create('bulkSelect_'.$record->ID)
			->addExtraClass('bulkSelect');
		return $cb->Field();
	}*/
	
	function getColumnAttributes($gridField, $record, $columnName)
	{
		return array('class' => 'col-buttons');
	}
	
	function getColumnMetadata($gridField, $columnName)
	{
		if($columnName == 'ToggleRelation') {
			return array('title' => 'Select');
		}
	}

	/**
	 *
	 * @param GridField $gridField
	 * @param DataObject $record
	 * @param string $columnName
	 * @return string - the HTML for the column 
	 */
	public function getColumnContent($gridField, $record, $columnName) {

		// todo
		$isLinked = $this->isLinked($record->ID);
		$linkExtraClass = $isLinked ? 'jj-gridfield-button-unlink' : 'jj-gridfield-button-link';
		$title = $isLinked ? _t('GridAction.UnlinkRelation', "Unlink") : _t('GridAction.UnlinkRelation', "Link");
		$iconClass = $isLinked ? 'jj-chain--minus' : 'jj-chain--plus';

		$field = GridField_FormAction::create($gridField, 'ToggleRelation'.$record->ID, false,
				"togglerelation", array('RecordID' => $record->ID))
			->addExtraClass($linkExtraClass)
			->setAttribute('title', $title)
			->setAttribute('data-icon', $iconClass);

		return $field->Field();
	}

	public function isLinked($recordID) {
		$item = $this->relationList->byID($recordID);
		//Debug::dump($item);
		return $item && $item->exists() ? true : false;
	}
	
	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param mixed $arguments
	 * @param array $data - form data
	 * @return void
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName != 'togglerelation' || !$this->ownerModel->ID) return;


		$itemToHandle = $gridField->getList()->byID($arguments['RecordID']);

		$relationName = $this->relationName;
		$relation = $this->ownerModel->$relationName();

		if ($relation instanceof DataList) {
			// many-to- relation
			$existItem = $relation->byID($arguments['RecordID']);	
			if(!$existItem) {
				// add itemToHandle to list
				$this->relationList->add($itemToHandle);
			} else {
				$this->relationList->remove($itemToHandle);
			}
		} else if ($relation instanceof DataObject) {
			// one-to- relation
			$relType = $this->getRelationTypeForOneTo();
			$relObj = $itemToHandle;
			
			if ($relation->ID === $itemToHandle->ID) {
				// remove from relation
				if ($relType == 'has_one') {
					$relField = $this->relationName . 'ID';
					$this->ownerModel->$relField = 0;
					$this->ownerModel->write();
				} else if ($relType == 'belongs_to') {
					$remoteField = $this->ownerModel->getRemoteJoinField($this->relationName, 'belongs_to');
					$relObj->$remoteField = 0;
					$relObj->write();
				}
			} else {
				// addToRelation
				print_r('foofoo');
				$relField = $this->relationName . 'ID';

				$this->ownerModel = $this->setRelationFieldByObj($this->ownerModel, $relObj, $this->relationName, $relType);
				$this->ownerModel->write();
			}
			$this->updateRelationList();
		}
	}

	private function updateRelationList() {
		$relName = $this->relationName;
		$rel = $this->ownerModel->$relName();
		$this->relationList = DataList::create($rel->class)->where('ID=' . $rel->ID);
	}

	private function getRelationTypeForOneTo($obj) {
		$type = '';

		$relationKeys = array(
			'has_one'		=> $this->ownerModel->has_one($component = null),
			'belongs_to'	=> $this->ownerModel->belongs_to($component = null, $classOnly = true),
		);

		foreach ($relationKeys as $key => $relation) {
			if (is_array($relation) && !empty($relation)) {
				foreach ($relation as $k => $v) {
					if ($k == $this->relationName) {
						$type = $key;		
					}
				}
			}
		}
		
		return $type;
	}

	protected function setRelationFieldByObj($obj, $relObj, $relName, $relType) {
		if ($relType == 'has_one') {
			print_r($obj);
			$relField = $relName . 'ID';
			$obj->$relField = $relObj->ID;
		} else if ($relType == 'belongs_to') {
			$remoteField = $obj->getRemoteJoinField($relName, 'belongs_to');
			
			// get all objs with $relObj's ID and set $remoteField to 0
			if ($formerObjs = DataList::create($relObj->class)->where("$remoteField=$obj->ID")) {
				foreach ($formerObjs as $o) {
					$o->$remoteField = 0;
					$o->write();
				}
			}
			
			// now set the id to the passed $relObj
			$relObj->$remoteField = $obj->ID;
			$relObj->write();
		}
		return $obj;
	}

}