<?php

namespace YamlRoute\Test;

use \YamlRoute\Plugin;
use \YamlRoute\Generator;

/**
 * Class IntegrationTest
 *
 * @package YamlRoute\Test
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Basic integration test
     * @throws \YamlRoute\YamlRouteException
     */
    public function testIntegration()
    {
        $assert = '\Cake\Routing\Router::plugin(\'PluginCars\', [\'path\' => \'/cars\'], function ($routes) {' . "\n";
        $assert .= "\t" . '$routes->extensions([\'0\' => \'json\', \'1\' => \'xml\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/\', [\'plugin\' => \'PluginCars\', \'controller\' => \'Cars\', \'action\' => \'index\'], [\'_name\' => \'cars\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws\', [\'path\' => \'/bmws\', \'controller\' => \'Bmws\'], [\'_name\' => \'bmws_list\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws/:id\', [\'_method\' => \'GET\', \'path\' => \'/bmws/{id}\', \'controller\' => \'Bmws\', \'action\' => \'view\'], [\'_name\' => \'bmws_view\', \'pass\' => [\'0\' => \'id\'], \'id\' => \'[0-9]+\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws/add\', [\'_method\' => \'POST\', \'path\' => \'/bmws/add\', \'controller\' => \'Bmws\', \'action\' => \'add\'], [\'_name\' => \'bmws_add\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/ladas\', [\'path\' => \'/ladas\', \'controller\' => \'Ladas\'], [\'_name\' => \'ladas\']);' . "\n";
        $assert .= "\t" . '$routes->fallbacks(\'DashedRoute\');' . "\n";
        $assert .= '});' . "\n\n";
        $assert .= '\Cake\Core\Plugin::routes();';

        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
        Generator::getInstance()->run(true);

        $res = Generator::getInstance()->getDump();
        $this->assertEquals($assert, $res);
    }
}

