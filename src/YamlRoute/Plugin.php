<?php

namespace makallio85\YamlRoute;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Plugin
 *
 * All CakePHP plugins should be loaded via this class to ensure all plugin routes are initialized properly
 *
 * @package makallio85\CakeYaml
 */
class Plugin
{
    /**
     * Array of loaded plugins
     *
     * @var array
     */
    private $_loaded = [];

    /**
     * Instance pf Plugin class
     *
     * @var null
     */
    private static $_instance = null;

    /**
     * Add loaded plugin
     *
     * @param $plugin
     */
    private function _addLoaded($plugin)
    {
        $this->_loaded[] = $plugin;
    }

    /**
     * Get instance
     *
     * @return \makallio85\YamlRoute\Plugin|null
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Plugin();
        }

        return self::$_instance;
    }

    /**
     * Get loaded plugins
     *
     * @return array
     */
    public function getLoaded()
    {
        return $this->_loaded;
    }

    /**
     * @param $plugins
     * @param $options
     *
     * @throws \makallio85\YamlRoute\YamlRouteException
     */
    public function load($plugins, $options)
    {
        $routes = isset($options['routes']) && $options['routes'] === true ? true : false;
        $options['routes'] = false;

        CakePlugin::load($plugins, $options);

        if ($routes) {
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }

            foreach ($plugins as $plugin) {
                if ($this->isLoaded($plugin)) {
                    throw new YamlRouteException("Plugin $plugin is loaded already and should not be loaded twice.");
                }
                $path = Configure::read('App.paths.plugins')[0] . DS . $plugin . DS . 'config' . DS . 'routes.yml';
                if (!file_exists($path)) {
                    throw new YamlRouteException("Yaml route configuration file not found in path $path.");
                }
                $route = Yaml::parse(file_get_contents($path));
                $this->_addLoaded(['name' => $plugin, 'route' => $route]);
            }
        }
    }

    /**
     * Is plugin loaded
     *
     * @param $plugin
     *
     * @return bool
     */
    public function isLoaded($plugin)
    {
        foreach (self::getLoaded() as $loaded) {
            if ($plugin === $loaded['name']) {
                return true;
            }
        }

        return false;
    }
}