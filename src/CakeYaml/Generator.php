<?php

namespace CakeYaml;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Cake\Routing\Router;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Generator
 *
 * @package CakeYaml
 */
class Generator
{
    /**
     * Route configurations
     *
     * @var array
     */
    private static $_routeConfigs = [];

    /**
     * Instance of Generator
     *
     * @var null
     */
    private static $_instance = null;

    /**
     * Get instance
     *
     * @return \CakeYaml\Generator|null
     */
    private static function _getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Generator();
        }

        return self::$_instance;
    }

    /**
     * Add route config
     *
     * @param $config
     */
    private static function _addRouteConfig($config)
    {
        self::$_routeConfigs[] = $config;
    }

    /**
     * Set route configurations
     */
    private static function _setRouteConfigs()
    {
        foreach (Plugin::getLoaded() as $item) {
            self::_addRouteConfig($item);
        }
    }

    /**
     * Load project routes.yml file
     */
    private static function _loadProjectConfig()
    {
        $path = ROOT . DS . 'config' . DS . 'routes.yml';
        if (file_exists($path)) {
            $route = Yaml::parse(file_get_contents($path));
            self::_addRouteConfig(['name' => 'Project', 'route' => $route]);
        }
    }

    /**
     * Get route configurations
     *
     * @return array
     */
    private static function _getRouteConfigs()
    {
        return self::$_routeConfigs;
    }

    /**
     * Generate routes
     */
    private static function _generateRoutes()
    {
        self::_loadProjectConfig();
        $configs = self::_getRouteConfigs();

        foreach ($configs as $config) {
            if (isset($config['route'])) {
                foreach ($config['route'] as $name => $route) {
                    self::_newRoute($name, $route);
                }
            }
        }
        //debug(Router::routes()); die();
    }

    /**
     * New route
     *
     * @param $name
     * @param $route
     */
    private static function _newRoute($name, $route)
    {
        if (!is_array($route['config'])) {
            $route['config'] = self::_loadRouteConfig($route['config']);
        }
        if (isset($route['config']['plugin']) && Plugin::isLoaded($route['config']['plugin'])) {
            $method = 'plugin';
            $path = $route['config']['plugin'];
            $options = ['path' => $route['path']];
        } else {
            $method = 'scope';
            $path = '';
            $options = [];
        }

        // Set default route class
        if (isset($route['config']['default_route_class'])) {
            if (in_array($route['config']['default_route_class'], ['Route', 'InflectedRoute', 'DashedRoute'])) {
                Router::defaultRouteClass($route['config']['default_route_class']);
            }
        }
        Router::$method(
            $path, $options, function ($routes) use ($route, $name) {
            if (isset($route['config']['controller'])) {
                $opts = [];
                foreach ($route['config'] as $key => $item) {
                    if (!in_array($key, ['routes', 'extensions', 'plugin', 'default_route_class'])) {
                        $opts[$key] = $item;
                    }
                }
                /* @var \Cake\Routing\Router $routes */
                $routes->connect('/', $opts, ['_name' => $name]);
            }
            if (isset($route['config']['routes'])) {
                //debug($route); die();
                foreach ($route['config']['routes'] as $key => $x) {
                    $opts = [];
                    foreach ($x as $k => $item) {
                        if (!in_array($k, ['routes', 'extensions', 'plugin'])) {
                            $opts[$k] = $item;
                        }
                    }
                    /* @var \Cake\Routing\Router $routes */
                    $routes->connect('/' . $key, $opts, ['_name' => $key]);
                }
            }
            if (isset($route['config']['fallbacks'])) {
                $fallbacks = $route['config']['fallbacks'];
            } else {
                $fallbacks = 'DashedRoute';
            }
            /* @var \Cake\Routing\RouteBuilder $routes */
            $routes->fallbacks($fallbacks);
        }
        );
        CakePlugin::routes();
    }

    /**
     * Load route config
     *
     * @param $config
     *
     * @return array
     */
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

    /**
     * Generate routes based on yml files
     */
    public static function run()
    {
        $instance = self::_getInstance();
        $instance::_setRouteConfigs();
        $instance::_generateRoutes();
    }
}