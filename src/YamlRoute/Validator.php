<?php

namespace makallio85\YamlRoute;

/**
 * Class Validator
 *
 * @package makallio85\YamlRoute
 */
class Validator
{
    /**
     * Run validation
     *
     * @param $data
     *
     * @return bool
     * @throws \makallio85\YamlRoute\YamlRouteException
     */
    public static function run($data)
    {
        if (count($data['route']) === 0) {
            throw new YamlRouteException('Invalid routing data in file ' . $data['file'] . '!');
        }
        foreach ($data['route'] as $name => $route) {
            self::checkRoute($name, $route);
        }

        return true;
    }

    /**
     * Check route
     *
     * @param $name
     * @param $route
     *
     * @throws \makallio85\YamlRoute\YamlRouteException
     */
    private static function checkRoute($name, $route)
    {
        if (!isset($route['path'])) {
            throw new YamlRouteException("Route path missing for route $name!");
        }
        if (isset($route['config'])) {
            if (isset($route['config']['routes'])) {

                foreach ($route['config']['routes'] as $name => $route) {
                    self::checkRoute($name, $route);
                }
            }
        }
    }
}