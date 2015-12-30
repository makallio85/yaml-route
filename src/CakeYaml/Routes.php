<?php

namespace CakeYaml;

use Cake\Core\Plugin;

class Routes
{
    private static $routePlugins = [];
    private static $_instance = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Routes();
        }

        return self::$_instance;
    }

    public static function create()
    {

    }

    public static function loadPlugin($plugin, $options = [])
    {
        if (isset($options['routes']) && $options['routes']) {
            if(!is_array($plugin)) {
                self::$routePlugins[] = $plugin;
            } else {
                self::$routePlugins = $plugin;
            }
        }
        $options['routes'] = false;
        Plugin::load($plugin, $options);
    }
}