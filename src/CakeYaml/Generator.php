<?php

namespace CakeYaml;

use Cake\Core\Configure;

class Generator
{
    private static $_plugins  = [];
    private static $_instance = null;

    private static function _setPlugins()
    {
        self::$_plugins = Plugin::getInstance()->getLoaded();
    }

    private static function _getPlugins()
    {
        return self::$_plugins;
    }

    private static function _generateRoutes()
    {
        $plugins = self::_getPlugins();
        foreach ($plugins as $plugin) {

        }
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Generator();
        }

        return self::$_instance;
    }

    public static function run()
    {
        self::_setPlugins();
        self::_generateRoutes();
    }
}