<?php

namespace makallio85\YamlRoute;

use Cake\Core\Configure;
use Cake\Core\Plugin as CakePlugin;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Plugin
 *
 * All CakePHP plugins should be loaded via this class to ensure all plugin routes are initialized properly
 *
 * @package makallio85\CakeYaml
 */
class Plugin
{
    use FileHandlerTrait;

    /**
     * Array of loaded plugins
     *
     * @var array
     */
    private $_loaded = [];

    /**
     * Instance of Plugin
     *
     * @var \makallio85\YamlRoute\Plugin|null
     */
    private static $_instance = null;

    /**
     * Add loaded plugin
     *
     * @param $name
     * @param $plugin
     */
    private function _addLoaded($name, $plugin)
    {
        $this->_loaded[$name] = $plugin;
    }

    /**
     * Get instance
     *
     * @return \makallio85\YamlRoute\Plugin|null
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
    public function getLoaded()
    {
        return $this->_loaded;
    }

    /**
     * @param $plugins
     * @param $options
     *
     * @throws \makallio85\YamlRoute\Exception\PluginException
     */
    public function load($plugins, $options)
    {
        $routes = isset($options['routes']) && $options['routes'] === true ? true : false;
        $options['routes'] = false;

        CakePlugin::load($plugins, $options);

        if ($routes) {
            if (!is_array($plugins)) {
                $plugins = [$plugins];
            }

            foreach ($plugins as $plugin) {
                if ($this->isLoaded($plugin)) {
                    throw new Exception\PluginException("Plugin $plugin is loaded already and should not be loaded twice.");
                }
                $path = Configure::read('plugins')[$plugin] . 'config' . DS . 'routes.yml';
                if (!file_exists($path)) {
                    throw new Exception\PluginException("Yaml route configuration file not found in path $path.");
                }
                $route = Yaml::parse(file_get_contents($path));
                $data = ['name' => $plugin, 'route' => $route, 'file' => $path];
                $this->_addLoaded($plugin, $data);
                $data['route'] = $this->_loadRouteConfigs($route);
                Validator::run($data);
                $this->_updateLoaded($plugin, $data);
            }
        }
    }

    private function _updateLoaded($name, $data)
    {
        $this->_loaded[$name] = $data;
    }

    /**
     * Is plugin loaded
     *
     * @param $plugin
     *
     * @return bool
     */
    public function isLoaded($plugin)
    {
        foreach (self::getLoaded() as $loaded) {
            if ($plugin === $loaded['name']) {
                return true;
            }
        }

        return false;
    }
}