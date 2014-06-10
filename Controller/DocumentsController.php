<?php

App::uses('Folder', 'Utility');

/**
 * Documents Controller
 *
 * @property Document $Document
 */
class DocumentsController extends DocumentManagerAppController {

	/**
	 * Displays a mini file explorer, rooted at app/webroot/files
	 * Current subfolder path is a folder name indexed array, i.e. the function arguments
	 * E.g. for the folder "/files/funny/images/lolCats/", $this->passedArgs must be:
	 * array(
	 *     0 => 'funny',
	 *     1 => 'images',
	 *     2 => 'lolCats',
	 * )
	 */
	public function index() {
		$this->set('pathFolderNames', $this->passedArgs);
		$this->set($this->Document->readFolder($this->passedArgs));
	}

	/**
	 * Creates a subfolder in the current folder of the mini explorer
	 */
	public function create_subfolder() {
		if (substr($this->request->data('folderName'), 0, 1) == '.') {
			$this->Session->setFlash(__d("document_manager", "Le nom d'un dossier ne peut pas commencer par un point."));
		} else {
			if ($error = $this->Document->createSubFolder($this->passedArgs, $this->request->data('folderName'))) {
				$this->Session->setFlash($error, 'flash', array('class' => 'alert alert-danger'));
			}
		}
		$this->redirect(array_merge($this->passedArgs, array('action' => 'index')));
	}

	/**
	 * Uploads a file to the current folder of the mini explorer
	 */
	public function upload_file() {
		if (!function_exists('getallheaders')) {
			$headers = $this->getHeaders();
		} else {
			$headers = getallheaders();
		}
		$message = $this->Document->saveDocument($this->request->data, $this->passedArgs, $this->getUserId(), $headers);
		if (!empty($message)) {
			$this->Session->setFlash($message, 'flash', array('class' => 'alert alert-danger'));
		}
		$this->redirect(array_merge($this->passedArgs, array('action' => 'index')));
	}

	/**
	 * Renames a file from the current folder of the mini explorer
	 */
	public function rename_file() {
		// Check if former extension is present in new file name
		$fileNameParts = explode('.', $fileName = $this->request->data['file']);
		$newFileNameParts = explode('.', $newFileName = $this->request->data['newFile']);
		if (count($fileNameParts) > 1 && $fileNameParts[count($fileNameParts) - 1] != $newFileNameParts[count($newFileNameParts) - 1]) {
			// Extension omitted: add it
			$newFileName = $newFileName . '.' . $fileNameParts[count($fileNameParts) - 1];
		}

		$file = $this->Document->renameFile($pathFolderNames = $this->Document->getPathFolderNames($this->passedArgs), $fileName, $newFileName, $this->getUserId());
		if (!empty($file['error'])) { // Error
			// Send JSON-encoded error message
			$this->viewClass = 'Json';
			$this->set($file);
			$this->set('_serialize', array_keys($file));
		} else { // Success
			// Display file element
			$this->set(compact('pathFolderNames', 'file'));
		}
	}

	/**
	 * Deletes a file from the current folder of the mini explorer
	 */
	public function delete_file() {
		$this->viewClass = 'Json';

		$path = $this->Document->getFullPath($this->Document->getPathFolderNames($this->passedArgs), $this->passedArgs['file']);
		$document = $this->Document->findByUrl($this->Document->fileToUrl($path));
		if ($this->hasAdminRights() || $this->fileBelongsToUser($document['Document']['user_id'])) {
			$error = $this->Document->deleteFile($path) ?
				null : __d("document_manager", "Le fichier n'a pu être supprimé.");
		} else {
			$error = __d("document_manager", "Ce fichier ne vous appartient pas.");
		}

		$json = array('remove' => true, 'error' => $error);
		$this->set(compact('json'));
		$this->set('_serialize', array('json'));
	}

	/**
	 * Deletes a subfolder from the current folder of the mini explorer
	 */
	public function delete_folder() {
		$this->viewClass = 'Json';

		$path = $this->Document->getFullPath($this->Document->getPathFolderNames($this->passedArgs), $this->passedArgs['folder']);
		if ($this->hasAdminRights() || $this->folderBelongsToUser($path)) {
			$error = $this->Document->deleteFolder($path) ? null : __d("document_manager", "Le dossier n'a pu être supprimé.");
		} else {
			$error = __d("document_manager", "Ce dossier contient des fichiers qui ne vous appartiennent pas.");
		}

		$json = array('remove' => (empty($error) ? 'true' : 'false'), 'error' => $error);
		$this->set(compact('json'));
		$this->set('_serialize', array('json'));
	}

	/**
	 * Custom function in case apache is not deployed
	 * @return type
	 */
	public function getHeaders() { 
       $headers = ''; 
       foreach ($_SERVER as $name => $value) { 
           if (substr($name, 0, 5) == 'HTTP_') { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 

}