<?php

class Widgets_Plugin_Plugin extends Widgets_Abstract {

	protected $_cacheable = false;

	protected function _load() {
		$pluginName   = strtolower(array_shift($this->_options));
		if(!$pluginName) {
			return $this->_translator->translate('Plugin name not specified.');
		}
		$pluginMapper = new Application_Model_Mappers_PluginMapper();
		$plugin       = $pluginMapper->findByName($pluginName);
		if($plugin !== null) {
			if($plugin->getStatus() != Application_Model_Models_Plugin::ENABLED) {
				return $this->_translator->translate('Plugin ') . $plugin->getName() . $this->_translator->translate(' is not installed.');
			}
			try {
				$toasterPlugin = Tools_Factory_PluginFactory::createPlugin($plugin->getName(), $this->_options, $this->_toasterOptions);
				return $toasterPlugin->run();
			}
			catch (Exceptions_SeotoasterPluginException $spe) {
				return $spe->getMessage();
			}
			catch (Exception $e) {
				return $e->getMessage();
			}
		}
		return $this->_translator->translate('Plugin is not installed.');
	}
}
