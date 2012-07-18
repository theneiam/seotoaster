<?php

class Application_Model_Mappers_PageMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable       = 'Application_Model_DbTable_Page';

	protected $_model         = 'Application_Model_Models_Page';

	protected $_optimized     = false;

	protected $_originalsOnly = false;

	protected $_optimizedFields = array(
		'h1',
		'header_title',
		'url',
		'nav_name',
		'meta_description',
		'meta_keywords',
		'targeted_key_phrase'
	);

	public function save($page) {
		if(!$page instanceof Application_Model_Models_Page) {
			throw new Exceptions_SeotoasterException('Given parameter should be and Application_Model_Models_Page instance');
		}
		$data = array(
			'template_id'         => $page->getTemplateId(),
			'parent_id'           => $page->getParentId(),
			'h1'                  => $page->getH1(),
			'header_title'        => $page->getHeaderTitle(),
			'url'                 => $page->getUrl(),
			'nav_name'            => $page->getNavName(),
			'meta_description'    => $page->getMetaDescription(),
			'meta_keywords'       => $page->getMetaKeywords(),
			'teaser_text'         => $page->getTeaserText(),
			'show_in_menu'        => $page->getShowInMenu(),
			'is_404page'          => $page->getIs404page(),
			'protected'           => $page->getProtected(),
			'mem_landing'         => $page->getMemLanding(),
			'signup_landing'      => $page->getSignupLanding(),
			'checkout'            => $page->getCheckout(),
			'err_login_landing'   => $page->getErrLoginLanding(),
			'order'               => $page->getOrder(),
			'silo_id'             => $page->getSiloId(),
			'targeted_key_phrase' => $page->getTargetedKey(),
			'system'              => intval($page->getSystem()),
			'draft'               => intval($page->getDraft()),
			'news'                => intval($page->getNews()),
			'publish_at'          => (!$page->getPublishAt()) ? null : date('Y-m-d', strtotime($page->getPublishAt()))
		);
		if(null === ($id = $page->getId())) {
			unset($data['id']);
			return $this->getDbTable()->insert($data);
		}
		else {
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
	}

    public function fetchAll($where = '', $order = array(), $fetchSysPages = false, $originalsOnly = false) {
		//exclude system pages from select
		$sysWhere  = $this->getDbTable()->getAdapter()->quoteInto("system = '?'", intval($fetchSysPages));
		$where    .= (($where) ? ' AND ' . $sysWhere : $sysWhere);
		$order[]   = 'order';
		$entries   = array();
		$resultSet = $this->getDbTable()->fetchAllPages($where, $order);

        if(null === $resultSet) {
			return null;
		}
	    $this->_originalsOnly = $originalsOnly;
        if(!$resultSet || empty($resultSet)) {
            return null;
        }
        foreach($resultSet as $row) {
            $entries[] = $this->_toModel($row, $originalsOnly);
        }
		return $entries;
	}


	public function fetchAllUrls() {
		$urls  = array();
		$urls = array_map(array($this, '_callbackfetchAllUrls'), $this->fetchAll());
		return $urls;
	}

	private function _callbackfetchAllUrls($page) {
		return $page->getUrl();
	}

	public function fetchAllStaticMenuPages() {
		$where = $this->getDbTable()->getAdapter()->quoteInto("show_in_menu = '?'", Application_Model_Models_Page::IN_STATICMENU);
		return $this->fetchAll($where);
	}

	public function fetchAllMainMenuPages() {
        return $this->getDbTable()->fetchAllMenu(Application_Model_Models_Page::IN_MAINMENU);
	}

	public function fetchAllDraftPages() {
		$where = $this->getDbTable()->getAdapter()->quoteInto("draft = ?", '1');
		return $this->fetchAll($where, array(), true);
	}

	public function fetchAllNomenuPages() {
		$where = sprintf("show_in_menu = '%s' AND parent_id = %d", Application_Model_Models_Page::IN_NOMENU, Application_Model_Models_Page::IDCATEGORY_DEFAULT);
		return $this->fetchAll($where);
	}

	public function findByUrl($pageUrl) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('url = ?', $pageUrl);
		$page  = $this->_findWhere($where);
		return ($page !== null) ? $page : $this->_findWhere($where, true);
	}

	public function findErrorLoginLanding() {
		return $this->_findWhere("err_login_landing = '1'");
	}

	public function findMemberLanding() {
		return $this->_findWhere("mem_landing = '1'");
	}

	public function findSignupLandign() {
		return $this->_findWhere("signup_landing = '1'");
	}

	public function findCheckout() {
		return $this->_findWhere("checkout = '1'");
	}

	public function findByNavName($navName) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('nav_name = ?', $navName);
		return $this->_findWhere($where);
	}

	public function findByParentId($parentId) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('parent_id = ?', $parentId);
		return $this->fetchAll($where);
	}

	public function selectCategoriesIdName() {
		$result     = array();
		$categories = $this->findByParentId(0);
        if(empty($categories)) {
            return array();
        }
		foreach ($categories as $key => $category) {
			$categoryName = ($category->getProtected()) ? ($category->getH1() . '*') : $category->getH1();
			$result[$category->getId()] = $categoryName;
		}
		return $result;
	}

	public function find404Page() {
		$where  = $this->getDbTable()->getAdapter()->quoteInto('is_404page = ?', '1');
		return $this->_findWhere($where, true);
	}

	public function delete(Application_Model_Models_Page $page) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $page->getId());
		$deleteResult = $this->getDbTable()->delete($where);
		$page->notifyObservers();
		return $deleteResult;
	}

	public function fetchIdUrlPairs() {
		$pairs = array();
		$pages = $this->fetchAll();
		if(empty($pages)) {
			return null;
		}
		foreach ($pages as $page) {
			$pairs[$page->getId()] = $page->getUrl();
		}
		asort($pairs);
		return $pairs;
	}

	protected function  _findWhere($where, $fetchSysPages = false) {
		$sysWhere = $this->getDbTable()->getAdapter()->quoteInto("system = '?'", intval($fetchSysPages));
		$where   .= (($where) ? ' AND ' . $sysWhere : $sysWhere);
		$row      = $this->getDbTable()->fetchAll($where)->current();
		if(null === $row) {
			//try to find row in the optimized table
			$optimizedDbTable = new Application_Model_DbTable_Optimized();
			try {
				$optimizedRowset  = $optimizedDbTable->fetchAll(str_replace(' AND ' . $sysWhere, '', $where));
			} catch(Exception $e) {
				return null;
			}
			if($optimizedRowset->current() === null) {
				return null;
			}
			$row = $optimizedRowset->current()->findParentRow('Application_Model_DbTable_Page');
		}
		$row         = $this->_optimizedRowWalk($row, (isset($optimizedRowset) ? $optimizedRowset : null));
		$rowTemplate = $row->findParentRow('Application_Model_DbTable_Template');
		$row         = $row->toArray();
		$row['content'] = ($rowTemplate !== null) ? $rowTemplate->content : '';
		unset($rowTemplate);
		return $this->_toModel($row);
	}

	protected function _optimizedRowWalk($row, $optimizedRowset = null) {
		if(!$optimizedRowset) {
			$optimizedRowset = $row->findDependentRowset('Application_Model_DbTable_Optimized')->current();
		}
		if($optimizedRowset === null) {
			return $row;
		}
		$this->_optimized = true;
		foreach($optimizedRowset as $propertyName => $propertyValue) {
			if($propertyValue && isset($row->$propertyName)) {
				$row->$propertyName = $propertyValue;
			}
		}
        return $row;
	}

	public function find($id, $originalsOnly = false) {
        if(!is_array($id)) {
            return $this->_findPage($id, $originalsOnly);
        }
        $pages = array();
        foreach($id as $pageId) {
            if(null !== ($page = $this->_findPage($pageId, $originalsOnly))) {
                $pages[] = $page;
            }
        }
        return $pages;
	}

    protected function _findPage($id, $originalsOnly) {
        $row = $this->getDbTable()->findPage(intval($id));
        if(null == $row) {
            return null;
        }
        return $this->_toModel($row, $originalsOnly);
    }

	protected function _toModel($row, $originalsOnly = false) {
		if($row instanceof Zend_Db_Table_Row) {
			$row = $row->toArray();
		}
		if(!$originalsOnly && $this->_isOptimized($row)) {
            $this->_optimized           = false;
			$row['optimized']           = true;
            $row['h1']                  = isset($row['optimizedH1']) ? $row['optimizedH1'] : $row['h1'];
            $row['url']                 = isset($row['optimizedUrl']) ? $row['optimizedUrl'] : $row['url'];
            $row['header_title']        = isset($row['optimizedHeaderTitle']) ? $row['optimizedHeaderTitle'] : $row['header_title'];
            $row['nav_name']            = isset($row['optimizedNavName']) ? $row['optimizedNavName'] : $row['nav_name'];
            $row['targeted_key_phrase'] = isset($row['optimizedTargetedKeyPhrase']) ? $row['optimizedTargetedKeyPhrase'] : $row['targeted_key_phrase'];
            $row['meta_description']    = isset($row['optimizedMetaDescription']) ? $row['optimizedMetaDescription'] : $row['meta_description'];
            $row['meta_keywords']       = isset($row['optimizedMetaKeywords']) ? $row['optimizedMetaKeywords'] : $row['meta_keywords'];
		}
		return new $this->_model($row);
	}

    private function _isOptimized($row) {
        if($row instanceof Zend_Db_Table_Row) {
            $row = $row->toArray();
        }
        $isOptimized = false;
        foreach($row as $key => $value) {
            if(false !== (strpos($key, 'optimized', 0))) {
                $isOptimized = $isOptimized || (boolean)$value;
            }
        }
        return $isOptimized;
    }
}

