<?php
class DocumentsComponent extends Object {

	function initialize($controller) {}

	function startup($controller) {}

	function beforeRender($controller) {
		$controller->helpers[] = 'DocumentManager.DocumentManager';
	}
	
	function beforeRedirect() {}

	function shutdown($controller) {}
}
?>
