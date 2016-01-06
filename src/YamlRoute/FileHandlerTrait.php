<?php

namespace makallio85\YamlRoute;

use Cake\Core\Configure;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileHandlerTrait
 */
trait FileHandlerTrait
{
    /**
     * Load route config
     *
     * @param $config
     *
     * @return array
     */
    private function _loadRouteConfig($config)
    {
        if (strpos($config, '.') !== false) {
            list($plugin, $file) = explode('.', $config);
            $pluginPaths = Plugin::getInstance()->getLoaded();
            $path = Configure::read('App.paths.plugins')[0] . $pluginPaths[$plugin]['name'] . DS . 'config' . DS . $file . '.yml';
        } else {
            $path = ROOT . DS . 'config' . DS . $config . '.yml';
        }

        return Yaml::parse(file_get_contents($path));
    }

    /**
     * Load route configs
     *
     * @param $routes
     *
     * @return array
     */
    private function _loadRouteConfigs(&$routes)
    {
        foreach ($routes as &$route) {
            if (!is_array($route['config'])) {
                $route['config'] = $this->_loadRouteConfig($route['config']);
            }
            if (isset($route['config']['routes'])) {
                $this->_loadRouteConfigs($route['config']['routes']);
            }
        }

        return $routes;
    }
}