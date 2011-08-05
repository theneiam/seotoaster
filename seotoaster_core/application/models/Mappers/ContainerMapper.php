<?php

/**
 * Container mapper
 *
 * @author Seotoaster Dev Team
 */
class Application_Model_Mappers_ContainerMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Application_Model_DbTable_Container';

	protected $_model   = 'Application_Model_Models_Container';

	public function save($container) {
		if(!$container instanceof Application_Model_Models_Container) {
			throw new Exceptions_SeotoasterException('Given parameter should be and Application_Model_Models_Container instance');
		}
		$data = array(
			'name'            => $container->getName(),
			'content'         => $container->getContent(),
			'container_type'  => $container->getContainerType(),
			'page_id'         => $container->getPageId(),
			'published'       => $container->getPublished(),
			'publishing_date' => $container->getPublishingDate()
		);
		if(!$container->getId()) {
			return $this->getDbTable()->insert($data);
		}
		else {
			return $this->getDbTable()->update($data, array('id = ?' => $container->getId()));
		}
	}

	public function findByName($name, $pageId = 0, $type = Application_Model_Models_Container::TYPE_REGULARCONTENT) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('name = ?', $name);
		$where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('container_type = ?', $type);
		if($pageId) {
			$where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('page_id = ?', $pageId);
		}
		$row  = $this->getDbTable()->fetchAll($where)->current();
		if(null == $row) {
			return null;
		}
		return new Application_Model_Models_Container($row->toArray());
	}

	public function findByPageId($pageId) {
		$where = $this->getDbTable()->getAdapter()->quoteInto("page_id = ?", $pageId);
		return $this->fetchAll($where);
	}

	public function deleteByPageId($pageId) {

	}

	public function delete(Application_Model_Models_Container $container) {
		$where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $container->getId());
		$this->getDbTable()->delete($where);
		$container->notifyObservers();
	}

	/**
	 * Method finds container which contains query string inside itself
	 * @param string $findString String to be find
	 * @param boolean $attachPage Flag to attach page or not
	 * @return mixed Array of containers objects or null if no matches found.
	 */
	public function findByContent($findString, $attachPage = false) {
		$where = $this->getDbTable()->getAdapter()->quoteInto("content LIKE ?", '%'.$findString.'%');
		$row = $this->fetchAll($where);
		if (empty($row)){
			return null;
		}
		return $row;
	}
 }
