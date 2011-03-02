<?php

class Tools_Plugins_Tools {

	public static function fetchPluginsMenu($userRole = null) {
		$additionalMenu = array();
		$enabledPlugins = self::getEnabledPlugins();
		if(is_array($enabledPlugins) && !empty ($enabledPlugins)) {
			$miscData    = Zend_Registry::get('misc');
			$websiteData = Zend_Registry::get('website');
			$pluginDirPath  = $websiteData['path'] . $miscData['pluginsPath'];
			foreach ($enabledPlugins as $plugin) {
				if($plugin instanceof Application_Model_Models_Plugin) {
					$pluginConfigPath = $pluginDirPath . $plugin->getName() . '/config/config.ini';
					if(file_exists($pluginConfigPath)) {
						try {
							$configIni = new Zend_Config_Ini($pluginConfigPath);
							if(isset($configIni->cpanel)) {
								$additionalMenu[$plugin->getName()]['title'] = strtoupper((isset($configIni->cpanel->title)) ? $configIni->cpanel->title : $plugin->getName());
								if(isset($configIni->cpanel->items)) {
									$items = $configIni->cpanel->items->toArray();
									if(isset($configIni->$userRole) && isset($configIni->$userRole->items)) {
										$items = array_merge($items, $configIni->$userRole->items->toArray());
									}
									$additionalMenu[$plugin->getName()]['items'] = array_map(array('self', '_processPluginMenuItem'), $items);
								}
							}
						}
						catch (Zend_Config_Exception $zce) {
							//Zend_Debug::dump($zce->getMessage()); die(); //development
							continue; //production
						}
					}
				}
			}
		}
		return $additionalMenu;
	}

	private static function _processPluginMenuItem($item) {
		$websiteData = Zend_Registry::get('website');
		$replaceMap = array(
			'{url}' => $websiteData['url'],
			'\''    => '"'
		);
		foreach ($replaceMap as $key => $replace) {
			$item = str_replace($key, $replace, $item);
		}
		return $item;
	}

	public static function getWidgetmakerContent() {
		return self::_getData('getWidgetMakerContent');
	}

	private static function _getData($method) {
		$pluginsData = array();
		$pluginMapper   = new Application_Model_Mappers_PluginMapper();
		$enabledPlugins = $pluginMapper->findEnabled();
		if(is_array($enabledPlugins) && !empty ($enabledPlugins)) {
			foreach ($enabledPlugins as $plugin) {
				$pluginClassName = ucfirst($plugin->getName());
				$pluginPath = 'plugins/' . $plugin->getName() . '/' . $pluginClassName . '.php';
				if(file_exists($pluginPath)) {
					require_once $pluginPath;
					if(method_exists($pluginClassName, $method)) {
						$pluginsData[] = $pluginClassName::$method();
					}
				}
			}
		}
		return $pluginsData;
	}

	public static function fetchPluginsRoutes() {
		$routes         = array();
		$enabledPlugins = self::getEnabledPlugins();
		if(is_array($enabledPlugins) && !empty ($enabledPlugins)) {
			$routesPath  = APPLICATION_PATH . '/configs/' . SITE_NAME . 'routes.xml';
			if(file_exists($routesPath)) {
				$routes = new Zend_Config_Xml($routesPath);
				$routes = $routes->toArray();
			}
			$miscData       = Zend_Registry::get('misc');
			$websiteData    = Zend_Registry::get('website');
			$pluginDirPath  = $websiteData['path'] . $miscData['pluginsPath'];
			foreach ($enabledPlugins as $plugin) {
				if($plugin instanceof Application_Model_Models_Plugin) {
					$pluginConfigPath = $pluginDirPath . $plugin->getName() . '/config/config.ini';
					if(file_exists($pluginConfigPath)) {
						try {
							$configIni = new Zend_Config_Ini($pluginConfigPath);
							if(!isset($configIni->route)) {
								continue;
							}
							$pluginRoute = self::_formatPluginRoute($configIni->route->toArray(), $plugin->getName());
							if(!empty($routes)) {
								if(!array_key_exists($pluginRoute['name'], $routes['routes'])) {
									$routes['routes'][$pluginRoute['name']] = $pluginRoute['data'];
								}
							}
						}
						catch (Zend_Config_Exception $zce) {
							//Zend_Debug::dump($zce->getMessage()); die(); //development
							continue; //production
						}
					}
				}
			}
			$writer = new Zend_Config_Writer_Xml();
			$writer->setConfig(new Zend_Config($routes));
			try {
				$writer->write($routesPath);
			}
			catch (Zend_Config_Exception $zce) {
				//Zend_Debug::dump($zce->getMessage());
			}
		}
	}

	public static function getEnabledPlugins() {
		$pluginMapper   = new Application_Model_Mappers_PluginMapper();
		return $pluginMapper->findEnabled();
	}

	private static function _formatPluginRoute($routeData, $pluginName) {
		$name   = $routeData['name'];
		$method = $routeData['method'];
		unset ($routeData['name']);
		unset ($routeData['method']);
		$route = array(
			'name' => $name,
			'data' => $routeData
		);
		$route['data']['defaults'] = array(
			'controller' => 'backend_plugin',
			'action'     => 'fireaction',
			'name'       => $pluginName,
			'run'        => $method
		);
		return $route;
	}
}
