<?php

namespace makallio85\YamlRoute\Test;

use makallio85\YamlRoute\Plugin;

/**
 * Class PluginTest
 *
 * @package makallio85\YamlRoute\Test
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \makallio85\YamlRoute\Exception\PluginException
     * @throws \makallio85\YamlRoute\Exception\PluginException
     */
    public function testDoubleLoad()
    {
        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
    }
}