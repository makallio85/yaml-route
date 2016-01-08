<?php

$loader = require dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'vendor' . DS . 'autoload.php';
$loader->setPsr4("makallio85\\YamlRoute\\Test\\", dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "./vendor/makallio85/yaml-route/tests/YamlRoute");

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'bootstrap.php';

\Cake\Core\Configure::write(
    ['plugins' => [
        'PluginCars'    => dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'plugins' . DS . 'PluginCars',
        'PluginAnimals' => dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'plugins' . DS . 'PluginAnimals',
    ]]
);