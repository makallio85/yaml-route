<?php

namespace YamlRoute;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Cake\Routing\Router;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Generator
 *
 * @package YamlRoute
 */
class Generator
{
    use ConversionTrait;

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
     * @var
     */
    private static $_dump;

    /**
     * @var bool
     */
    private static $_debug = false;

    /**
     * @var bool
     */
    private static $_executed = [
        '\Cake\Core\Plugin::routes()' => false,
    ];

    /**
     * Get instance
     *
     * @return \YamlRoute\Generator|null
     */
    public static function getInstance()
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
        self::_pluginRoutes();
    }

    /**
     * New route
     *
     * @param $name
     * @param $route
     */
    private static function _newRoute($name, $route)
    {
        $method = 'scope';
        $path = '/';
        $options = [];

        if (isset($route['config'])) {
            if (!is_array($route['config'])) {
                $route['config'] = self::_loadRouteConfig($route['config']);
            }
            if (isset($route['config']['plugin']) && Plugin::isLoaded($route['config']['plugin'])) {
                $method = 'plugin';
                $path = $route['config']['plugin'];
                $options = ['path' => $route['path']];
            }
        }

        // Set default route class
        if (isset($route['config']['default_route_class'])) {
            if (in_array($route['config']['default_route_class'], ['Route', 'InflectedRoute', 'DashedRoute'])) {
                Router::defaultRouteClass($route['config']['default_route_class']);
            }
        }

        // Debugging
        if (self::$_debug) {
            self::_addToDump("\\Cake\\Routing\\Router::$method('$path', " . self::_arrToStr($options) . ", function (" . '$routes' . ") {");
        }

        Router::$method(
            $path, $options, function ($routes) use ($route, $name) {
            $exclude = ['pass', 'validate', 'routes', 'extensions', 'default_route_class'];

            if (isset($route['config']) && isset($route['config']['controller'])) {
                if (isset($route['config']['extensions']) && is_array($route['config']['extensions'])) {
                    /* @var \Cake\Routing\Router $routes */
                    $routes->extensions($route['config']['extensions']);
                    if (self::$_debug) {
                        self::_addToDump("\t" . '$routes->extensions(' . self::_arrToStr($route['config']['extensions']) . ');');
                    }
                }
                $route = self::_createPassParams($route);

                $opts = [];
                foreach ($route['config'] as $key => $item) {
                    if (!in_array($key, $exclude)) {
                        $opts[$key] = $item;
                    }
                }

                $thirdParam = self::_buildThirdParam($name, $route);

                /* @var \Cake\Routing\Router $routes */
                $routes->connect('/', $opts, $thirdParam);

                // Debugging
                if (self::$_debug) {
                    self::_addToDump("\t" . '$routes->connect(\'/\', ' . self::_arrToStr($opts) . ', ' . self::_arrToStr($thirdParam) . ');');
                }
            }
            if (isset($route['config']) && isset($route['config']['routes'])) {
                foreach ($route['config']['routes'] as $key => $x) {
                    if (isset($x['extensions']) && is_array($x['extensions'])) {
                        /* @var \Cake\Routing\Router $routes */
                        $routes->extensions($x['extensions']);
                        if (self::$_debug) {
                            self::_addToDump("\t" . '$routes->extensions(' . self::_arrToStr($x['extensions']) . ');');
                        }
                    }
                    $x = self::_createPassParams($x);
                    $opts = [];
                    foreach ($x as $k => $item) {
                        if (!in_array($k, $exclude)) {
                            $opts[$k] = $item;
                        }
                    }

                    $thirdParam = self::_buildThirdParam($key, $x);

                    /* @var \Cake\Routing\Router $routes */
                    $routes->connect('/' . self::_varsToString($x['path']), $opts, $thirdParam);

                    // Debugging
                    if (self::$_debug) {
                        self::_addToDump("\t" . '$routes->connect(\'' . self::_varsToString($x['path']) . '\', ' . self::_arrToStr($opts) . ', ' . self::_arrToStr($thirdParam) . ');');
                    }
                }
            }
            if (isset($route['config']) && isset($route['config']['fallbacks'])) {
                $fallbacks = $route['config']['fallbacks'];
            } else {
                $fallbacks = 'DashedRoute';
            }
            /* @var \Cake\Routing\RouteBuilder $routes */
            $routes->fallbacks($fallbacks);

            // Debugging
            if (self::$_debug) {
                self::_addToDump("\t" . '$routes->fallbacks(\'' . $fallbacks . '\');');
            }
        }
        );

        // Debugging
        if (self::$_debug) {
            self::_addToDump('});' . "\n");
        }
    }

    /**
     * Build third parameter for $routes::connect() method
     *
     * @param $name
     * @param $route
     *
     * @return array
     */
    private static function _buildThirdParam($name, $route)
    {
        $arr = ['_name' => $name, 'pass' => $route['pass']];
        if (isset($route['validate'])) {
            foreach ($route['validate'] as $key => $item) {
                $arr[$key] = $item;
            }
        }
        foreach ($arr as $key => $value) {
            if (count($value) === 0) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    /**
     * Create pass param list
     *
     * @param $route
     *
     * @return mixed
     */
    private static function _createPassParams($route)
    {
        $route['pass'] = [];
        preg_match_all('/\{([^}]+)\}/', $route['path'], $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $item) {
                array_push($route['pass'], $item);
            }
        }

        return $route;
    }

    /**
     * Run Plugin::routes()
     */
    private static function _pluginRoutes()
    {
        if (!self::_isExecuted('\Cake\Core\Plugin::routes()')) {
            CakePlugin::routes();
            self::setExecuted('\Cake\Core\Plugin::routes()');

            // Debugging
            if (self::$_debug) {
                self::_addToDump('\Cake\Core\Plugin::routes();' . "\n");
            }
        }
    }

    /**
     * Set method call as executed
     *
     * @param $name
     */
    private static function setExecuted($name)
    {
        self::$_executed[$name] = true;
    }

    /**
     * Is method call executed?
     *
     * @param $name
     *
     * @return mixed
     */
    private static function _isExecuted($name)
    {
        return self::$_executed[$name];
    }

    /**
     * Add item to dump
     *
     * @param $string
     */
    private static function _addToDump($string)
    {
        self::$_dump .= $string . "\n";
    }

    /**
     * Get dump
     *
     * @return mixed
     */
    public static function getDump()
    {
        return trim(self::$_dump);
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
     *
     * @param $debug
     */
    public static function run($debug = false)
    {
        self::$_debug = $debug;
        self::_setRouteConfigs();
        self::_generateRoutes();
    }
}