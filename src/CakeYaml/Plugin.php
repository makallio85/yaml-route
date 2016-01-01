<?php

namespace CakeYaml;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Plugin
 *
 * All CakePHP plugins should be loaded via this class to ensure all plugin routes are initialized properly
 *
 * @package CakeYaml
 */
class Plugin
{
    /**
     * Array of loaded plugins
     *
     * @var array
     */
    private static $_loaded = [];

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
    private static function _addLoaded($plugin)
    {
        self::$_loaded[] = $plugin;
    }

    /**
     * Get instance
     *
     * @return \CakeYaml\Plugin|null
     */
    private static function _getInstance()
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
    public static function getLoaded()
    {
        $instance = self::_getInstance();

        return $instance::$_loaded;
    }

    /**
     * Load plugin.
     *
     * @param $plugins
     * @param $options
     *
     * @throws CakeYamlException
     */
    public static function load($plugins, $options)
    {
        $instance = self::_getInstance();
        $routes = isset($options['routes']) && $options['routes'] === true ? true : false;

        $options['routes'] = false;
        CakePlugin::load($plugins, $options);

        if ($routes) {
            $pluginPaths = Configure::read('plugins');
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }
            foreach ($plugins as $plugin) {
                if ($instance::isLoaded($plugin)) {
                    throw new CakeYamlException("Plugin $plugin is loaded already and should not be loaded twice.");
                }
                $path = $pluginPaths[$plugin] . DS . 'config' . DS . 'routes.yml';
                if (!file_exists($path)) {
                    throw new CakeYamlException("Yaml route configuration file not found in path $path.");
                }
                $route = Yaml::parse(file_get_contents($path));
                $instance::_addLoaded(['name' => $plugin, 'route' => $route]);
            }
        }
    }

    /**
     * Not implemented yet
     *
     * @param $options
     */
    public static function loadAll($options)
    {
        $instance = self::_getInstance();
    }

    /**
     * Is plugin loaded
     *
     * @param $plugin
     *
     * @return bool
     */
    public static function isLoaded($plugin)
    {
        $instance = self::_getInstance();
        foreach ($instance::getLoaded() as $loaded) {
            if ($plugin === $loaded['name']) {
                return true;
            }
        }

        return false;
    }
}