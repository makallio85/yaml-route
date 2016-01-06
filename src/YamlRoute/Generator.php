<?php

namespace makallio85\YamlRoute;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Cake\Routing\Router;
use makallio85\YamlRoute\Exception\GeneratorException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Generator
 *
 * @package makallio85\YamlRoute
 */
class Generator
{
    use ConversionTrait;
    use FileHandlerTrait;

    /**
     * Route configurations
     *
     * @var array
     */
    private $_routeConfigs = [];

    /**
     * Instance of Generator
     *
     * @var \makallio85\YamlRoute\Generator|null
     */
    private static $_instance = null;

    /**
     * @var
     */
    private $_dump;

    /**
     * @var bool
     */
    private $_debug = false;

    /**
     * @var bool
     */
    private $_executed = [
        '\Cake\Core\Plugin::routes()' => false,
    ];

    /**
     * Project route file path
     *
     * @var
     */
    private $_projectFilePath;

    /**
     * Get instance
     *
     * @return \makallio85\YamlRoute\Generator
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
    private function _addRouteConfig($config)
    {
        $this->_routeConfigs[] = $config;
    }

    /**
     * Set route configurations
     */
    private function _setRouteConfigs()
    {
        foreach (Plugin::getInstance()->getLoaded() as $item) {
            $this->_addRouteConfig($item);
        }
    }

    /**
     * Load project routes.yml file
     *
     * @throws \makallio85\YamlRoute\Exception\ValidatorException
     */
    private function _loadProjectConfig()
    {
        if ($this->_projectFilePath === null) {
            $this->setProjectFilePath(ROOT . DS . 'config' . DS . 'routes.yml');
        }
        $path = $this->_getProjectFilePath();
        if (!file_exists($path)) {
            throw new GeneratorException("Project route configuration file not found in path '$path'!");
        }
        $route = Yaml::parse(file_get_contents($path));
        $route = $this->_loadRouteConfigs($route);

        $data = ['name' => 'Project', 'route' => $route, 'file' => $path];
        Validator::run($data);

        $this->_addRouteConfig($data);
    }

    /**
     * Get project file path
     *
     * @return mixed
     */
    private function _getProjectFilePath()
    {
        return $this->_projectFilePath;
    }

    /**
     * Set project file path
     *
     * @param $path
     *
     * @return $this
     */
    public function setProjectFilePath($path)
    {
        $this->_projectFilePath = $path;

        return $this;
    }

    /**
     * Get route configurations
     *
     * @return array
     */
    private function _getRouteConfigs()
    {
        $res = [];
        foreach ($this->_routeConfigs as $key => $item) {
            if ($item['name'] === 'Project') {
                $res[] = $item;
                unset($this->_routeConfigs[$key]);
            }
        }
        $this->_routeConfigs = array_merge($res, $this->_routeConfigs);

        return $this->_routeConfigs;
    }

    /**
     * Generate routes
     */
    private function _generateRoutes()
    {
        $this->_loadProjectConfig();
        $configs = $this->_getRouteConfigs();
        foreach ($configs as $config) {
            if (isset($config['route'])) {
                foreach ($config['route'] as $name => $route) {
                    $this->_newRoute($name, $route);
                }
            }
        }
        $this->_pluginRoutes();
    }

    /**
     * New route
     *
     * @param $name
     * @param $route
     */
    private function _newRoute($name, $route)
    {
        $method = 'scope';
        $path = '/';
        $options = [];

        if (isset($route['config'])) {
            if (isset($route['config']['plugin']) && Plugin::getInstance()->isLoaded($route['config']['plugin'])) {
                $method = 'plugin';
                $path = $route['config']['plugin'];
                $options = ['path' => $route['path']];
            }
        }

        // Set default route class
        if (isset($route['config']['default_route_class'])) {
            if (in_array($route['config']['default_route_class'], ['Route', 'InflectedRoute', 'DashedRoute'])) {
                Router::defaultRouteClass($route['config']['default_route_class']);
                $this->_addToDump("\\Cake\\Routing\\Router::defaultRouteClass('" . $route['config']['default_route_class'] . "');\n");
            }
        }

        // Debugging
        if ($this->_debug) {
            $this->_addToDump("\\Cake\\Routing\\Router::$method('$path', " . $this->_arrToStr($options) . ", function (" . '$routes' . ") {");
        }

        Router::$method(
            $path, $options, function ($routes) use ($route, $name, $method) {
            $exclude = ['validate', 'routes', 'extensions', 'default_route_class', 'path'];

            if (isset($route['config'])) {
                if (isset($route['config']['extensions']) && is_array($route['config']['extensions'])) {
                    /* @var \Cake\Routing\Router $routes */
                    $routes->extensions($route['config']['extensions']);
                    if ($this->_debug) {
                        $this->_addToDump("\t" . '$routes->extensions(' . $this->_arrToStr($route['config']['extensions']) . ');');
                    }
                }
                if (isset($route['config']['controller'])) {
                    $route = $this->_createPassParams($route);

                    $opts = [];
                    foreach ($route['config'] as $key => $item) {
                        if (!in_array($key, $exclude)) {
                            if (is_array($item) && $key === 'pass') {
                                foreach ($item as $i => $y) {
                                    $opts[$i] = $y;
                                }
                            } else {
                                $opts[$key] = $item;
                            }
                        }
                    }

                    $thirdParam = $this->_buildThirdParam($name, $route);

                    $path = $method == 'scope' ? $route['path'] : '/';

                    /* @var \Cake\Routing\Router $routes */
                    $routes->connect($path, $opts, $thirdParam);

                    // Debugging
                    if ($this->_debug) {
                        $this->_addToDump("\t" . '$routes->connect(\'' . $path . '\', ' . $this->_arrToStr($opts) . ', ' . $this->_arrToStr($thirdParam) . ');');
                    }
                }
                if (isset($route['config']['routes'])) {
                    foreach ($route['config']['routes'] as $key => $x) {
                        if (isset($x['config'])) {

                            if (isset($x['config']['extensions']) && is_array($x['config']['extensions'])) {
                                /* @var \Cake\Routing\Router $routes */
                                $routes->extensions($x['config']['extensions']);
                                if ($this->_debug) {
                                    $this->_addToDump("\t" . '$routes->extensions(' . $this->_arrToStr($x['config']['extensions']) . ');');
                                }
                            }

                            $x = self::_createPassParams($x);
                            $opts = [];
                            foreach ($x['config'] as $k => $item) {
                                if (!in_array($k, $exclude)) {
                                    if (is_array($item) && $k === 'pass') {
                                        foreach ($item as $i => $y) {
                                            $opts[$i] = $y;
                                        }
                                    } else {
                                        $opts[$k] = $item;
                                    }
                                }
                            }

                            $thirdParam = $this->_buildThirdParam($key, $x);

                            /* @var \Cake\Routing\Router $routes */
                            $routes->connect('/' . $this->_varsToString($x['path']), $opts, $thirdParam);

                            // Debugging
                            if ($this->_debug) {
                                $this->_addToDump("\t" . '$routes->connect(\'' . $this->_varsToString($x['path']) . '\', ' . $this->_arrToStr($opts) . ', ' . $this->_arrToStr($thirdParam) . ');');
                            }
                        }
                    }
                }
            }
            $this->_createFallbacks($routes, $route);
        }
        );

        // Debugging
        if ($this->_debug) {
            $this->_addToDump('});' . "\n");
        }
    }

    /**
     * Create Fallbacks
     *
     * @param $routes
     * @param $route
     */
    private function _createFallbacks($routes, $route)
    {
        if (isset($route['config']) && isset($route['config']['fallbacks'])) {
            $fallbacks = $route['config']['fallbacks'];
        } else {
            $fallbacks = 'DashedRoute';
        }
        /* @var \Cake\Routing\RouteBuilder $routes */
        $routes->fallbacks($fallbacks);

        // Debugging
        if ($this->_debug) {
            $this->_addToDump("\t" . '$routes->fallbacks(\'' . $fallbacks . '\');');
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
    private function _buildThirdParam($name, $route)
    {
        $arr = ['_name' => $name, 'pass' => $route['config']['pass']];
        if (isset($route['config']['validate'])) {
            foreach ($route['config']['validate'] as $key => $item) {
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
    private function _createPassParams($route)
    {
        if (!isset($route['config']['pass'])) {
            $route['config']['pass'] = [];
        }
        preg_match_all('/\{([^}]+)\}/', $route['path'], $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $key => $item) {
                array_push($route['config']['pass'], $key);
            }
        }
        $arr = [];
        foreach ($route['config']['pass'] as $key => $item) {
            array_push($arr, $key);
        }
        $route['config']['pass'] = $arr;

        return $route;
    }

    /**
     * Run Plugin::routes()
     */
    private function _pluginRoutes()
    {
        if (!self::_isExecuted('\Cake\Core\Plugin::routes()')) {
            CakePlugin::routes();
            $this->_setExecuted('\Cake\Core\Plugin::routes()');

            // Debugging
            if ($this->_debug) {
                $this->_addToDump('\Cake\Core\Plugin::routes();' . "\n");
            }
        }
    }

    /**
     * Set method call as executed
     *
     * @param $name
     */
    private function _setExecuted($name)
    {
        $this->_executed[$name] = true;
    }

    /**
     * Is method call executed?
     *
     * @param $name
     *
     * @return mixed
     */
    private function _isExecuted($name)
    {
        return $this->_executed[$name];
    }

    /**
     * Add item to dump
     *
     * @param $string
     */
    private function _addToDump($string)
    {
        $this->_dump .= $string . "\n";
    }

    /**
     * Get dump
     *
     * @return mixed
     */
    public function getDump()
    {
        return trim($this->_dump);
    }

    /**
     * Generate routes based on yml files
     *
     * @param $debug
     *
     * @return $this
     */
    public function run($debug = false)
    {
        $this->_debug = $debug;
        $this->_setRouteConfigs();
        $this->_generateRoutes();

        return $this;
    }
}