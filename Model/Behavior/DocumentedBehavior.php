<?php

/**
 * Documented Behavior
 * 
 * Models behaving as Documented may reference Documents which are handled as virtual URL fields.
 * Settings, when attaching this behavior to a model, are the list of fields to be documented.
 * For each (virtual) documented field <name>, model table must contain a <name>_id column.
 * 
 * Example of use :
 * public $actsAs = array(
 *		'DocumentManager.Documented' => array(
 *			'datasheet',
 *	));
 * 
 * When creating or editing a model, a Document will be created or updated for each virtual URL field present in data.
 * If a virtual field is present but empty, the old Document (if any) will be deleted.
 * 
 * Virtual URL fields will be automatically present in find results unless the corresponding <field>_id field is absent.
 * 
 * Referenced Documents are automatically deleted when a Documented model is deleted.
 */
class DocumentedBehavior extends ModelBehavior {

	/**
	 * Loads given settings for given model
	 * 
	 * @param Model $Model
	 * @param type $settings 
	 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = $settings;
	}

	/**
	 * Parses Document ids and adds corresponding URLs in find results
	 * 
	 * @param Model $Model: model being found
	 * @param array $results: find results
	 * @param boolean $primary: false if model is found through association
	 * @return array: modified results 
	 */
	public function afterFind(Model $Model, $results, $primary) {
		$Document = ClassRegistry::init('DocumentManager.Document');
		foreach ($results as $index => $result) {
			// Check all fields listed in settings for given model
			foreach ($this->settings[$Model->alias] as $field) {
				// Check if field contains an id	
				if (isset($result[$Model->alias][$field . '_id']) && is_numeric($result[$Model->alias][$field . '_id'])) {
					// Find corresponding Document
					$document = $Document->find('first', array(
						'conditions' => array('id' => $result[$Model->alias][$field . '_id']),
						'fields' => array('id', 'url'),
							));
					if (empty($document)) {
						// Document not found
						$results[$index][$Model->alias][$field] = null;
					} else {
						// Store url in results
						$results[$index][$Model->alias][$field] = $document['Document']['url'];
					}
				}
			}
		}
		// Return modified results
		return $results;
	}

	/**
	 * Creates Documents for documented URLs in given model data, and adds newly created Document ids to data
	 *
	 * @param Model $Model: model being saved
	 */
	public function beforeSave(Model $Model) {
		$Document = ClassRegistry::init('DocumentManager.Document');
		// Determine real field list
		$fields = array();
		foreach ($this->settings[$Model->alias] as $field) {
			$fields[] = $field . '_id';
		}
		if ($Model->id) { // Model update
			$model = $Model->find('first', array(
				'conditions' => array('id' => $Model->id),
				'fields' => $fields,
					));
		} else {
			$model = array();
		}
		$created = array();
		$deleted = array();
		// Check all fields listed in settings for given model
		foreach ($this->settings[$Model->alias] as $field) {
			// Check if field contains anything
			if (!empty($Model->data[$Model->alias][$field])) {
				if (is_array($Model->data[$Model->alias][$field])) { // Field input is a file input
					if (!empty($Model->data[$Model->alias][$field]['name']) &&
							!empty($Model->data[$Model->alias][$field]['tmp_name'])) { // Field contains uploaded file data
						$name = $Model->data[$Model->alias][$field]['name'];
						if (!empty($model[$Model->alias][$field])) {
							// Extract old file name
							$oldName = explode('/files/', $model[$Model->alias][$field]);
							if ($name == $oldName[count($oldName) - 1]) { // Same file name: file update
								// Delete old file
								unlink($Document->getFullPath(array(), $name));
							}
						}
						// Check for name collision with other Documents
						if (file_exists($Document->getFullPath(array(), $name))) {
							$Model->validationErrors[$field][0] = __d("document_manager", "Un fichier portant ce nom existe déjà.");
							$error = true;
							break;
						}
						// Save uploaded file
						if (move_uploaded_file($Model->data[$Model->alias][$field]['tmp_name'], $Document->getFullPath(array(), $name))) {
							$Model->data[$Model->alias][$field] = $Document->getRelativePath(array(), $name);
						} else {
							$Model->validationErrors[$field][0] = __d("document_manager", "Le fichier n'a pu être sauvegardé.");
							$error = true;
							break;
						}
					} else {
						// No file was uploaded: do nothing
						continue;
					}
				}

				// Check for old Document
				if (!empty($model[$Model->alias][$field])) {
					if ($model[$Model->alias][$field] != $Model->data[$Model->alias][$field]) {
						// Different Document: delete old one
						$deleted[$field] = $model[$Model->alias][$field . '_id'];
					} else {
						// Same Document: mission complete
						continue;
					}
				}
				// Try to create a Document with given URL
				$Document->create();
				if ($Document->save(array(
							'url' => $Model->data[$Model->alias][$field],
							'user_id' => $Model->data[$Model->alias]['user_id'],
						))) { // Document successfully created
					// Remember newly created Document
					$created[$field] = $Document->getInsertID();
				} else {
					$Model->validationErrors[$field][0] = __d("document_manager", "Le document n'a pas pu être créé.");
					$error = true;
					break;
				}
			} else if (array_key_exists($field, $Model->data[$Model->alias]) && !empty($model[$Model->alias][$field])) {
				// There was an old Document, to be deleted since new URL is empty
				$deleted[$field] = $model[$Model->alias][$field . '_id'];
			}
		}

		if (!empty($error)) { // Error creating a Document: rollback
			// Delete other newly created Documents
			foreach ($created as $stillBorn) {
				$Document->delete($stillBorn);
			}
			// Deny model save
			return false;
		}

		// No error creating Documents: delete old ones and update model data
		foreach ($this->settings[$Model->alias] as $field) {
			if (!empty($deleted[$field])) {
				// Update Document id
				$Model->data[$Model->alias][$field . '_id'] = null;
				$Document->delete($deleted[$field]);
			}
			if (!empty($created[$field])) {
				// Update Document id
				$Model->data[$Model->alias][$field . '_id'] = $created[$field];
			}
		}
		// Operation successful: allow model save
		return true;
	}

	/**
	 * Deletes Documents referenced by this model
	 * @param Model $Model: model being deleted
	 * @param type $cascade: unused
	 * @return boolean: true so that deletion can proceed
	 */
	public function beforeDelete(Model $Model, $cascade = true) {
		$Document = ClassRegistry::init('DocumentManager.Document');
		// Determine real field list
		$fields = array();
		foreach ($this->settings[$Model->alias] as $field) {
			$fields[] = $field . '_id';
		}
		// Get model data
		$model = $Model->find('first', array(
			'conditions' => array(
				'id' => $Model->id,
			),
			'fields' => $fields,
				));
		// Delete all Documents referenced by this model
		foreach ($fields as $field) {
			if (isset($model[$Model->alias][$field])) { // There is a Document for this field
				$Document->delete($model[$Model->alias][$field]);
			}
		}
		return true;
	}

}

?>
