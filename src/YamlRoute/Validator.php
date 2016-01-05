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

    private function checkRoute($name, $route)
    {
        if (!isset($route['path'])) {
            throw new YamlRouteException("Route path missing for route $name!");
        }
        if (isset($route['routes'])) {
            foreach ($route['routes'] as $name => $route) {
                self::checkRoute($name, $route);
            }
        }
    }
}