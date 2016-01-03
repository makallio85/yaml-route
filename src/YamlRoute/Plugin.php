<?php

namespace YamlRoute;

use \Cake\Core\App;
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
     * @return \YamlRoute\Plugin|null
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
    public static function getLoaded()
    {
        return self::$_loaded;
    }

    /**
     * @param $plugins
     * @param $options
     *
     * @throws \YamlRoute\YamlRouteException
     */
    public static function load($plugins, $options)
    {
        $routes = isset($options['routes']) && $options['routes'] === true ? true : false;
        $options['routes'] = false;

        CakePlugin::load($plugins, $options);

        if ($routes) {
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }

            foreach ($plugins as $plugin) {
                if (self::isLoaded($plugin)) {
                    throw new YamlRouteException("Plugin $plugin is loaded already and should not be loaded twice.");
                }
                $path = Configure::read('App.paths.plugins')[0] . DS . $plugin . DS . 'config' . DS . 'routes.yml';
                if (!file_exists($path)) {
                    throw new YamlRouteException("Yaml route configuration file not found in path $path.");
                }
                $route = Yaml::parse(file_get_contents($path));
                self::_addLoaded(['name' => $plugin, 'route' => $route]);
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
    public static function isLoaded($plugin)
    {
        foreach (self::getLoaded() as $loaded) {
            if ($plugin === $loaded['name']) {
                return true;
            }
        }

        return false;
    }
}