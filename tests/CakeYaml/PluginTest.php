<?php

namespace CakeYaml\Test;
use CakeYaml\Plugin;
use CakeYaml\Generator;

/**
 * Class PluginTest
 *
 * @package CakeYaml\Test
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \CakeYaml\CakeYamlException
     */
    public function testLoad()
    {
        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
        Generator::getInstance()->run(true);
        $res = Generator::getDump();
    }
}