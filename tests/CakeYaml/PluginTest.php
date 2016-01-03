<?php

namespace CakeYaml\Test;
use CakeYaml\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        Plugin::getInstance()->load('PluginAnimals', ['bootstrap' => false, 'routes' => true]);
        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
    }
}