<?php

class Application_Model_Models_User extends Application_Model_Models_Abstract implements Zend_Acl_Role_Interface {
	
	private $_email     = '';

	private $_password  = '';

	private $_roleId    = '';

	private $_fullName  = '';

	private $_lasLogin  = '';

	private $_regDate   = '';

	public function getRoleId() {
		return ($this->_id) ? $this->_roleId : Tools_Security_Acl::ROLE_GUEST;
	}

	public function setRoleId($roleId) {
		$this->_roleId = $roleId;
		return $this;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function setEmail($email) {
		$this->_email = $email;
		return $this;
	}

	public function getPassword() {
		return $this->_password;
	}

	public function setPassword($password) {
		$this->_password = $password;
		return $this;
	}

	public function getFullName() {
		return $this->_fullName;
	}

	public function setFullName($fullName) {
		$this->_fullName = $fullName;
		return $this;
	}

	public function getLasLogin() {
		return $this->_lasLogin;
	}

	public function setLasLogin($lasLogin) {
		$this->_lasLogin = $_lasLogin;
		return $this;
	}

	public function getRegDate() {
		return $this->_regDate;
	}

	public function setRegDate($regDate) {
		$this->_regDate = $regDate;
		return $this;
	}
}
