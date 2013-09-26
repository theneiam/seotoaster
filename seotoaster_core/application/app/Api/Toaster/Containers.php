<?php
/**
 * Seotoaster content API
 *
 * @author Eugene I. Nezhuta <eugene@seotoaster.com>
 * Date: 9/26/13
 * Time: 3:51 PM
 */

class Api_Toaster_Containers extends Api_Service_Abstract {

    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array('allow' => array('get', 'post', 'put', 'delete')),
        Tools_Security_Acl::ROLE_ADMIN      => array('allow' => array('get', 'post', 'put', 'delete')),
        Tools_Security_Acl::ROLE_USER       => array('allow' => array('get', 'post', 'put', 'delete')),
        Tools_Security_Acl::ROLE_MEMBER     => array('allow' => array('get')),
        Tools_Security_Acl::ROLE_GUEST      => array('allow' => array('get'))
    );

    public function getAction() {
        // at first we will try to find content by id
        if(($containerId = intval(filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT)) ) == 0) {
            $containerId = filter_var($this->_request->getParam('name'), FILTER_SANITIZE_STRING);
        }

        // return only content for the containers
        $contentOnly = $this->_request->getParam('co', false);
        $mapper      = Application_Model_Mappers_ContainerMapper::getInstance();
        if(!$containerId) {
            // return all containers
        }

        $type      = $this->_request->getParam('type', Application_Model_Models_Container::TYPE_REGULARCONTENT);
        $pageId    = $this->_request->getParam('pid', null);
        $container = is_integer($containerId)  ? $mapper->find($containerId) : $mapper->findByName($containerId, $pageId, $type);

        if(!$container instanceof Application_Model_Models_Container) {
            $this->_error('404 Container not found', self::REST_STATUS_NOT_FOUND);
        }

        return ($contentOnly) ? $container->getContent() : $container;
    }

    public function postAction() {}
    public function putAction() {}
    public function deleteAction() {}
}