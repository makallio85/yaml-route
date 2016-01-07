<?php

makallio85\YamlRoute\Plugin::getInstance()->load('PluginCars', ['route' => true, 'bootstrap' => false]);
makallio85\YamlRoute\Plugin::getInstance()->load('PluginAnimals', ['route' => true, 'bootstrap' => false]);

makallio85\YamlRoute\Generator::getInstance()->run();