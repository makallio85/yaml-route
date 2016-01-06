<?php

namespace makallio85\YamlRoute;

use makallio85\YamlRoute\Exception\ValidatorException;

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
     * @throws \makallio85\YamlRoute\Exception\ValidatorException
     */
    public static function run($data)
    {
        if (count($data['route']) === 0) {
            throw new ValidatorException('Invalid routing data in file \'' . $data['file'] . '\'!');
        }
        foreach ($data['route'] as $name => $route) {
            self::checkRoute($name, $route, true);
        }

        return true;
    }

    /**
     * Check route
     *
     * @param $name
     * @param $route
     * @param $root
     *
     * @throws \makallio85\YamlRoute\Exception\ValidatorException
     */
    private static function checkRoute($name, $route, $root)
    {
        if (!isset($route['path'])) {
            throw new ValidatorException("Route path missing for route '$name''!");
        }
        if (isset($route['config'])) {
            if (isset($route['config']['action']) && !isset($route['config']['controller'])) {
                throw new ValidatorException('Action \'' . $route['config']['action'] . "' is present, but controller is missing from route '$name'' config!");
            }
            if (isset($route['config']['routes'])) {
                foreach ($route['config']['routes'] as $name => $route) {
                    self::checkRoute($name, $route, false);
                }
            }
        }
        if (!$root && !isset($route['config'])) {
            throw new ValidatorException("Route '$name' is missing config key!");

        }
        if (!$root && isset($route['config'])) {
            $requiredKeys = ['controller'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $route['config'])) {
                    throw new ValidatorException("Key '$key' is missing from route '$name'' config!");
                }
            }
        }
    }
}