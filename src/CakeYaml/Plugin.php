<?php

namespace CakeYaml;

use Cake\Core\App;
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
     * Load plugin.
     *
     * @param $plugins
     * @param $options
     *
     * @throws CakeYamlException
     */
    public static function load($plugins, $options)
    {
        $routes = isset($options['routes']) && $options['routes'] === true ? true : false;

        $options['routes'] = false;
        CakePlugin::load($plugins, $options);

        if ($routes) {
            $pluginPaths = Configure::read('plugins');
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }

            foreach ($plugins as $plugin) {
                if (self::isLoaded($plugin)) {
                    throw new CakeYamlException("Plugin $plugin is loaded already and should not be loaded twice.");
                }
                $path = $pluginPaths[$plugin] . DS . 'config' . DS . 'routes.yml';
                if (!file_exists($path)) {
                    throw new CakeYamlException("Yaml route configuration file not found in path $path.");
                }
                $route = Yaml::parse(file_get_contents($path));
                self::_addLoaded(['name' => $plugin, 'route' => $route]);
            }
        }
    }

    /**
     * Load all plugins at once
     *
     * @param $options
     */
    public static function loadAll($options)
    {
        $plugins = [];
        foreach (App::path('Plugin') as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $p) {
                if ($p->isDir() && !$p->isDot()) {
                    $plugins[] = $p->getBasename();
                }
            }
        }
        if (Configure::check('plugins')) {
            $plugins = array_merge($plugins, array_keys(Configure::read('plugins')));
            $plugins = array_unique($plugins);
        }

        foreach ($plugins as $p) {
            $opts = isset($options[$p]) ? $options[$p] : null;
            if ($opts === null && isset($options[0])) {
                $opts = $options[0];
            }
            if (Plugin::isLoaded($p)) {
                continue;
            }
            self::load($p, (array)$opts);
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