<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'bootstrap.php';

\Cake\Core\Configure::write(
    ['plugins' => [
        'PluginCars'    => dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'plugins' . DS . 'PluginCars',
        'PluginAnimals' => dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'plugins' . DS . 'PluginAnimals',
    ]]
);

makallio85\YamlRoute\Plugin::getInstance()->load('PluginCars', ['route' => true, 'bootstrap' => false]);
makallio85\YamlRoute\Plugin::getInstance()->load('PluginAnimals', ['route' => true, 'bootstrap' => false]);

makallio85\YamlRoute\Generator::getInstance()->run();