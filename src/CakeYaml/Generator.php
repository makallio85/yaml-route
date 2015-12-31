<?php

namespace CakeYaml;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Symfony\Component\Yaml\Yaml;

class Generator
{
    private static $_routeConfigs = [];
    private static $_instance     = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Generator();
        }

        return self::$_instance;
    }

    public static function run()
    {
        self::_setRouteConfigs();
        self::_generateRoutes();
    }

    private static function _setRouteConfigs()
    {
        self::$_routeConfigs = Plugin::getInstance()->getLoaded();
    }

    private static function _getRouteConfigs()
    {
        return self::$_routeConfigs;
    }

    private static function _generateRoutes()
    {
        $configs = self::_getRouteConfigs();

        foreach ($configs as $config) {
            foreach ($config['route'] as $name => $route) {
                self::_newRoute($name, $route);
            }
        }
        //debug(Router::routes()); die();
    }

    private static function _newRoute($name, $route)
    {
        if (!is_array($route['config'])) {
            $route['config'] = self::_loadRouteConfig($route['config']);
        }
        if (isset($route['config']['plugin']) && Plugin::exists($route['config']['plugin'])) {
            $method = 'plugin';
            $path = $route['config']['plugin'];
            $options = ['path' => $route['path']];
        } else {
            $method = 'scope';
            $path = '';
            $options = [];
        }
        Router::$method($path, $options, function ($routes) use ($route, $name) {
            if (isset($route['config']['controller'])) {
                $opts = [];
                foreach ($route['config'] as $key => $item) {
                    if (!in_array($key, ['routes', 'extensions', 'plugin'])) {
                        $opts[$key] = $item;
                    }
                }
                $routes->connect('/', $opts, ['_name' => $name]);
            }
            //debug($route); die();
            foreach ($route['config']['routes'] as $key => $x) {
                $opts = [];
                foreach ($x as $k => $item) {
                    if (!in_array($k, ['routes', 'extensions', 'plugin'])) {
                        $opts[$k] = $item;
                    }
                }
                $routes->connect('/' . $key, $opts, ['_name' => $key]);
            }
        });
    }

    private static function _loadRouteConfig($config)
    {
        if (strpos($config, '.') !== false) {
            list($plugin, $file) = explode('.', $config);
            $pluginPaths = Plugin::getLoaded();
            $path = $pluginPaths[$plugin] . DS . 'config' . DS . $file . '.yml';
        } else {
            $path = ROOT . DS . 'config' . DS . $config . '.yml';
        }

        return Yaml::parse(file_get_contents($path));
    }
}