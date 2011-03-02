<?php

class Application_Model_Mappers_PluginMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Application_Model_DbTable_Plugin';

	public function save($plugin) {
		if(!$plugin instanceof Application_Model_Models_Plugin) {
			throw new Exceptions_SeotoasterException('Given parameter should be and Application_Model_Models_Plugin instance');
		}
		$data = array(
			'name'   => $plugin->getName(),
			'status' => $plugin->getStatus(),
			'cache'  => intval($plugin->getCache()),
			'tag'    => $plugin->getTag()
		);
		if(!$plugin->getId()) {
			return $this->getDbTable()->insert($data);
		}
		else {
			return $this->getDbTable()->update($data, array('id = ?' => $plugin->getId()));
		}
	}

	public function findByName($name) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('name = ?', $name);
		$row   = $this->getDbTable()->fetchAll($where)->current();
		if(null == $row) {
			return null;
		}
		return new Application_Model_Models_Plugin($row->toArray());
	}

	public function findEnabled() {
		$entries = array();
		$where   = $this->getDbTable()->getAdapter()->quoteInto('status = ?', Application_Model_Models_Plugin::ENABLED);
		$rowSet  = $this->getDbTable()->fetchAll($where);
		if(null == $rowSet) {
			return null;
		}
		foreach ($rowSet as $row) {
			$entries[] = new Application_Model_Models_Plugin($row->toArray());
		}
		return $entries;
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();
		return new Application_Model_Models_Plugin($row->toArray());
	}

	 public function fetchAll() {
		$entries   = array();
		$resultSet = $this->getDbTable()->fetchAll();
		if(null === $resultSet) {
			return null;
		}
		foreach ($resultSet as $row) {
			$entries[] = new Application_Model_Models_Plugin($row->toArray());
		}
		return $entries;
	}

}
