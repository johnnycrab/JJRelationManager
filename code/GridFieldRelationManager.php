<?php
/**
 * GridField component for editing attached models in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldRelationManager implements GridField_HTMLProvider, GridField_URLHandler {
	
	/*
	function augmentColumns($gridField, &$columns) {
		if(!in_array('ToggleRelation', $columns)) $columns[] = 'ToggleRelation';
	}

	function getColumnsHandled($gridField) {
		return array();
	}

	function getColumnContent($gridField, $record, $columnName) {
		return '';
	}*/

	/* // GridField_ColumnProvider */

	protected $cookieTitle = '';

	public function __construct($ownerClass, $relClass) {
		$this->cookieTitle = $ownerClass . '_' . $relClass;
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField) {
		global $project;

		//Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');
		//print_r(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');
		Requirements::javascript(JJ_RELATION_MANAGER_PATH . '/javascript/JJRelationManager.js');	
		
		$checked = Session::get('GridField_' . $this->cookieTitle . '_showRelated') ? 'checked="checked"' : '';
		$toggleSelectAllHTML = '<span>Show related <input id="toggleRelationBtn" autocomplete="off" type="checkbox" title="select all" name="toggleRelationBtn" ' . $checked  . ' data-url="'.$gridField->Link('showrelated').'" /></span>';
		
		$html = '<div id="relationManagerOptions">'.$toggleSelectAllHTML.'</div>';
		
		return array(
			'after' => $html
		);
	}

	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
		return array(
			'showrelated' => 'showRelated'
		);
	}
	
	/**
	 * Pass control over to the RequestHandler
	 * 
	 * @param GridField $gridField
	 * @param SS_HTTPRequest $request
	 * @return mixed 
	 */
	public function showRelated($gridField, $request) {
		Session::set('GridField_' . $this->cookieTitle . '_showRelated', $request->postVar('checked'));
		

		return '{done:1}';
	}


}