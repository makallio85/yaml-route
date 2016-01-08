<?php

namespace makallio85\YamlRoute\Test;

use makallio85\YamlRoute\Plugin;
use makallio85\YamlRoute\Generator;

/**
 * Class IntegrationTest
 *
 * @package makallio85\YamlRoute\Test
 */
class IntegrationTest extends YamlRouteTest
{
    /**
     * Basic integration test
     *
     * @throws \makallio85\YamlRoute\Exception\GeneratorException
     * @throws \makallio85\YamlRoute\Exception\ValidatorException
     * @throws \makallio85\YamlRoute\Exception\PluginException
     */
    public function testIntegration()
    {
        $assert = '\Cake\Routing\Router::defaultRouteClass(\'DashedRoute\');' . "\n\n";

        $assert .= '\Cake\Routing\Router::scope(\'/\', [], function ($routes) {' . "\n";
        $assert .= "\t" . '$routes->fallbacks(\'DashedRoute\');' . "\n";
        $assert .= '});' . "\n\n";

        $assert .= '\Cake\Routing\Router::plugin(\'PluginCars\', [\'path\' => \'/cars\'], function ($routes) {' . "\n";
        $assert .= "\t" . '$routes->extensions([\'0\' => \'json\', \'1\' => \'xml\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/\', [\'plugin\' => \'PluginCars\', \'controller\' => \'Cars\', \'action\' => \'index\'], [\'_name\' => \'cars\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws\', [\'controller\' => \'Bmws\', \'foo\' => \'bar\'], [\'_name\' => \'bmws_list\', \'pass\' => [\'0\' => \'foo\']]);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws/:id\', [\'_method\' => \'GET\', \'controller\' => \'Bmws\', \'action\' => \'view\'], [\'_name\' => \'bmws_view\', \'pass\' => [\'0\' => \'id\'], \'id\' => \'[0-9]+\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/bmws/add\', [\'_method\' => \'POST\', \'controller\' => \'Bmws\', \'action\' => \'add\'], [\'_name\' => \'bmws_add\']);' . "\n";
        $assert .= "\t" . '$routes->connect(\'/ladas\', [\'controller\' => \'Ladas\'], [\'_name\' => \'ladas\']);' . "\n";
        $assert .= "\t" . '$routes->fallbacks(\'DashedRoute\');' . "\n";
        $assert .= '});' . "\n\n";

        $assert .= '\Cake\Core\Plugin::routes();';

        Plugin::getInstance()->load('PluginCars', ['bootstrap' => false, 'routes' => true]);
        Generator::getInstance()->run(true);

        $res = Generator::getInstance()->getDump();
        $this->assertEquals($assert, $res);
    }
}

