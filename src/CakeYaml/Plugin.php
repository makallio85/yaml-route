<?php

namespace CakeYaml;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Symfony\Component\Yaml\Yaml;

class Plugin
{
    private static $_loaded   = [];
    private static $_instance = null;

    private static function _addLoaded($plugin)
    {
        self::$_loaded[] = $plugin;
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Plugin();
        }

        return self::$_instance;
    }

    public static function getLoaded()
    {
        return self::$_loaded;
    }

    public static function load($plugins, $options)
    {
        $routes = isset($options['routes']) ? true : false;

        $options['routes'] = false;
        CakePlugin::load($plugins, $options);

        if ($routes) {
            $pluginPaths = Configure::read('plugins');
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }
            foreach ($plugins as $plugin) {
                $route = Yaml::parse(file_get_contents($pluginPaths[$plugin] . DS . 'config' . DS . 'routes.yml'));
                self::_addLoaded(['name' => $plugin, 'route' => $route]);
            }
        }
    }
}