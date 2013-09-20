<?php
/**
 * Base Helper file for Plugin DocumentManager
 * Modify getUserId() and isAdmin() to match your Users management method  
 */

App::uses('AppHelper', 'View/Helper');

class DocumentManagerHelper extends AppHelper {
	public $helpers = array('Html', 'Authake.Authake');

	public function hasAdminRights() {
		if($this->isAdmin()) {// User has admin rights
			return true;
		};
		return false;
	}
	
	/**
	 * Checks if the file belongs to a User
	 */
	public function fileBelongsToUser($user_id) {
		if($this->getUserId() == $user_id) {// file can be changed by current user
			return true;
		};
		return false;
	}
	
	/**
	 * Returns the logged user id, if not logged, return null 
	 */
	public function getUserId() {
		if(!Configure::read('DocumentManager.authentification')) {// If there is no authentification, user_id is null
			return null;
		}
		return $this->Authake->getUserId();
	}
	
	public function isAdmin() {
		if(!Configure::read('DocumentManager.authentification')) {// If there is no authentification, everyone has all the rights
			return true;
		}
		return $this->Authake->isMemberOf(1);
	}
}
