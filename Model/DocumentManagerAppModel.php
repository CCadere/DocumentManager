<?php
class DocumentManagerAppModel extends AppModel {
	
	/**
	 * @brief returns the absolute path to the file of given URL (absolute or relative)
	 */
	public function urlToFile($url) {
		$url = explode('/' . Configure::read('DocumentManager.baseDir') . '/', $url);
		return APP . WEBROOT_DIR . DS . Configure::read('DocumentManager.baseDir') . DS . implode(DS, explode('/', $url[1]));
	}
	
	/**
	 * @brief returns the relative URL of the file described by given absolute path
	 */
	public function fileToUrl($path) {
		$path = explode(DS . Configure::read('DocumentManager.baseDir') . DS, $path);
		return '/' . Configure::read('DocumentManager.baseDir') . '/' . implode('/', explode(DS, $path[1]));
	}
	
	/**
	 * Deletes a file without generating warnings
	 * If file doesn't exist, deletion is considered successful
	 * @param string $path
	 * @return true on success, false otherwise
	 */
	function unlinkSafe($path) {
		return !file_exists($path) || @unlink($path);
	}
}
?>
